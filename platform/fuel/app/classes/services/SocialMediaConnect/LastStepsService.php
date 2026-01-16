<?php

namespace Services\SocialMediaConnect;

use Container;
use DateTime;
use DI\DependencyException;
use DI\NotFoundException;
use EventLoginHelper;
use Exception;
use Exceptions\SocialMedia\FillRegisterFormException;
use Exceptions\SocialMedia\UserProfileWithEmptyEmailException;
use Exceptions\SocialMedia\UserIsCorrectlyConnectedException;
use Exceptions\SocialMedia\UserIsNotConnectedOrIsDeletedException;
use Exceptions\SocialMedia\SocialUserEmailEqualsEmailWhichHaveSocialConnectionException;
use Fuel\Core\Session;
use Helpers\ClassHelper;
use Helpers\FlashMessageHelper;
use Helpers\SocialMediaConnect\ConnectHelper;
use Helpers\SocialMediaConnect\LastStepsHelper;
use Helpers\SocialMediaConnect\ProfileHelper;
use Helpers\UserHelper;
use Helpers_Time;
use Hybridauth\Adapter\OAuth2;
use Hybridauth\Exception\AuthorizationDeniedException;
use Hybridauth\Exception\HttpClientFailureException;
use Hybridauth\Exception\NotImplementedException;
use Hybridauth\User\Profile;
use Models\Whitelabel;
use Models\WhitelabelSocialApi;
use Models\WhitelabelUser;
use Models\WhitelabelUserSocial;
use ReflectionException;
use Repositories\Orm\WhitelabelUserRepository;
use Repositories\WhitelabelSocialApiRepository;
use Repositories\WhitelabelUserSocialRepository;
use Services\Auth\AbstractAuthService;
use Services\Auth\WordpressLoginService;
use Services\CartService;
use Services\Logs\FileLoggerService;
use Services\RedirectService;
use Throwable;

class LastStepsService
{
    private ConnectService $connectService;
    private FileLoggerService $fileLoggerService;
    private OAuth2 $socialAdapter;
    private WhitelabelUserRepository $whitelabelUserRepository;
    private WhitelabelUserSocialRepository $whitelabelUserSocialRepository;
    private RedirectService $redirectService;
    private ConfirmMailerService $confirmedMailerService;
    private ActivationService $activationHashGeneratorService;
    private DateTime $dateTime;
    private WhitelabelSocialApiRepository $whitelabelSocialApiRepository;
    private WordpressLoginService $wordpressLoginService;
    private SessionService $sessionService;
    private FormService $formService;
    public CartService $cartService;

    public function __construct(
        ConnectService $connectService,
        FileLoggerService $fileLoggerService,
        OAuth2 $socialAdapter,
        WhitelabelUserRepository $whitelabelUserRepository,
        WhitelabelUserSocialRepository $whitelabelUserSocialRepository,
        RedirectService $redirectService,
        ConfirmMailerService $confirmedMailerService,
        ActivationService $activationHashGeneratorService,
        DateTime $dateTime,
        WhitelabelSocialApiRepository $whitelabelSocialApiRepository,
        WordpressLoginService $wordpressLoginService,
        SessionService $sessionService,
        FormService $formService,
        CartService $cartService
    )
    {
        $this->activationHashGeneratorService = $activationHashGeneratorService;
        $this->confirmedMailerService = $confirmedMailerService;
        $this->redirectService = $redirectService;
        $this->socialAdapter = $socialAdapter;
        $this->connectService = $connectService;
        $this->fileLoggerService = $fileLoggerService;
        $this->whitelabelUserRepository = $whitelabelUserRepository;
        $this->whitelabelUserSocialRepository = $whitelabelUserSocialRepository;
        $this->dateTime = $dateTime;
        $this->whitelabelSocialApiRepository = $whitelabelSocialApiRepository;
        $this->wordpressLoginService = $wordpressLoginService;
        $this->sessionService = $sessionService;
        $this->formService = $formService;
        $this->cartService = $cartService;
    }

    /**
     * Before use this method check if whitelabel use social log in.
     * @throws ReflectionException
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Exception
     */
    public function startSocialMediaAccountIntegration(): void
    {
        $this->socialAdapter->setStorage($this->sessionService);
        $socialAdapter = $this->socialAdapter;
        $socialType = ClassHelper::getClassNameWithoutNamespace($socialAdapter);
        /** Authentication do redirect to facebook when user try first time login */
        $userProfile = null;
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        try {
            $socialAdapter->authenticate();
            $userProfile = $socialAdapter->getUserProfile();
            $socialAdapter->disconnect();
        } catch (NotImplementedException) {
            ConnectHelper::setSecurityError();
            $socialAdapter->disconnect();
            /** Redirect after social error from last-steps because it can't show register form when social connected is not correct */
            $this->redirectService->redirectToSignUpPage();
            /** Add return because it can`t mock exit in PHPUnit */
            return;
        } catch (AuthorizationDeniedException) {
            FlashMessageHelper::set(FlashMessageHelper::TYPE_ERROR, MessageHelper::getTranslatedAuthenticationError());
            $socialAdapter->disconnect();
            /** Redirect after social error from last-steps because can't shows register form when social connected is not correct */
            $this->redirectService->redirectToSignUpPage();
            /** Add return because it can`t mock exit in PHPUnit */
            return;
        } catch (HttpClientFailureException) {
            $this->fileLoggerService->warning('Social login/register configuration could be incorrect or user has internet issues');
            $this->socialAdapter->disconnect();
            FlashMessageHelper::set(FlashMessageHelper::TYPE_ERROR, MessageHelper::getTranslatedAuthenticationError());
            /** We do redirect after social error from last-steps because we can't shows register form when social connected is not correct. */
            $this->redirectService->redirectToSignUpPage();
            /** We must add this return because we cant mock exit in PHPUnit */
            return;
        } catch (Throwable $exception) {
            $this->sendErrorAndRemoveSocialAccessToken($exception, $userProfile);
            /** Add return because it can`t mock exit in PHPUnit */
            return;
        }

        $whitelabelSocialApi = $this->whitelabelSocialApiRepository->findWhitelabelSocialSettingsBySocialType($socialType);
        try {
            $this->connectService->connect($userProfile, $whitelabelSocialApi->id);
        } catch (UserIsCorrectlyConnectedException) {
            $whitelabelUserSocial = $this->whitelabelUserSocialRepository->findEnabledByUserSocialIdAndWhitelabelSocialAppId($userProfile->identifier, $whitelabelSocialApi->id);
            $whitelabelUser = $whitelabelUserSocial->whitelabelUser;

            if ($whitelabelUserSocial->isUserNotConfirmedByEmail()) {
                $this->sendActivationEmailAndDoRedirectToSignUpPage($whitelabelUserSocial, $whitelabelUser, $socialType);
                /** Add return because it can`t mock exit in PHPUnit */
                return;
            }

            $isAccountActivationRequired = $whitelabel->isActivationForUsersRequired() && $whitelabelUser->isUserNotActivated();
            if ($isAccountActivationRequired) {
                $this->activationRequiredSetFlashMessageAndDoRedirectToLoginPage($whitelabelUser->id);
                /** Add return because it can`t mock exit in PHPUnit */
                return;
            }

            $whitelabelUserIsNotActive = !$whitelabelUser->isActive;
            if($whitelabelUserIsNotActive) {
                $this->setNotActiveMessageAndDoRedirectToSignUpPage();
                /** Add return because it can`t mock exit in PHPUnit */
                return;
            }

            $this->loginUserIfSocialUserIdIsCorrect($whitelabelUserSocial, $whitelabelUser, $userProfile);
        } catch (FillRegisterFormException|UserProfileWithEmptyEmailException) {

            /**
             * These values are set so that the user does not have to re-enter them on the last steps page after error.
             */
            $this->formService->setRegisterFormValuesAndDeleteRegisterValuesFromSessionAfterUse();
            $this->fillRegisterForm($userProfile, $whitelabelSocialApi);
            /**
             * It will be skipped to show user the registration form from page-last-steps.
             * wordpress/wp-content/themes/base/page-last-steps.php (In this file we run this service)
             */
            return;
        } catch (SocialUserEmailEqualsEmailWhichHaveSocialConnectionException) {
            $userProfile->email = null;
            $this->fillRegisterForm($userProfile, $whitelabelSocialApi);
            /**
             * It will be skipped to show user the registration form from page-last-steps.
             * wordpress/wp-content/themes/base/page-last-steps.php (In this file we run this service)
             */
            return;
        } catch (UserIsNotConnectedOrIsDeletedException) {
            /** @var WhitelabelUser $whitelabelUser */
            $whitelabelUser = $this->whitelabelUserRepository->findUserByEmailAndWhitelabelId($userProfile->email, $whitelabel->id);
            $hash = $this->activationHashGeneratorService->generateActivationHash($whitelabelUser->salt);

            $credentials = [
                    'whitelabelUserId' => $whitelabelUser->id,
                    'whitelabelSocialApiId' => $whitelabelSocialApi->id,
                    'socialUserId' => $userProfile->identifier,
                    'isConfirmed' => false,
                    'activationHash' => $hash,
                    'lastHashSentAt' => $this->dateTime->format(Helpers_Time::ACTIVATION_HASH_SEND_DATE_CARBON_FORMAT),
                ];

            try {
                /** @var WhitelabelUserSocial|null $whitelabelUserSocial */
                $whitelabelUserSocial = $this->whitelabelUserSocialRepository->insert($credentials);
            } catch (Throwable $exception) {
                ConnectHelper::setSecurityError();
                $this->fileLoggerService->error('Whitelabel user social is not added during connecting to existing account. Message: ' . $exception->getMessage());
                /** Redirect after social error from last-steps because it can't show register form when social connected is not correct */
                $this->redirectService->redirectToSignUpPage();
            }
            if (!empty($whitelabelUserSocial)) {
                $this->sendSocialLoginActivationMail($hash, $whitelabelUserSocial, $whitelabelUser,  $socialType);
                FlashMessageHelper::set(FlashMessageHelper::TYPE_ERROR, MessageHelper::getTranslatedActivationConfirmEmail());
                /** Redirect after social error from last-steps because it can't show register form when social connected is not correct */
                $this->redirectService->redirectToLoginPage();
            } else {
                $this->fileLoggerService->error('Whitelabel_user_social is not added while first register for whitelabel_user.id: ' . $whitelabelUser->id);
                ConnectHelper::setSecurityError();
                /** Redirect after social error from last-steps because it can't show register form when social connected is not correct */
                $this->redirectService->redirectToSignUpPage();
            }
        } catch (Throwable $exception) {
            $this->sendErrorAndRemoveSocialAccessToken($exception, $userProfile);
        }
    }

    private function activationRequiredSetFlashMessageAndDoRedirectToLoginPage(int $whitelabelUserid): void
    {
        $resendLink = $this->wordpressLoginService->getResendLink($whitelabelUserid);
        FlashMessageHelper::set(FlashMessageHelper::TYPE_ERROR, sprintf(_(AbstractAuthService::MESSAGES['activationLink']), $resendLink));
        $this->redirectService->redirectToLoginPage();
    }

    private function sendErrorAndRemoveSocialAccessToken(Throwable $exception, ?Profile $profile): void
    {
        ConnectHelper::setSecurityError();
        $shouldSendError = LastStepsHelper::isSocialAccessTokenNotExpired($exception) &&
            $this->isNotIncorrectCodeException($exception);
        if ($shouldSendError) {
            $socialUserId = empty($profile) ? ' user profile not exists' : $profile->identifier;
            $message = 'Social User Id: ' . $socialUserId  . 'Message: ' . $exception->getMessage();
            $this->fileLoggerService->error($message);
        }
        $this->socialAdapter->disconnect();
        /** Redirect after social error from last-steps because it can't show register form when social connected is not correct */
        $this->redirectService->redirectToSignUpPage();
    }

    private function isNotIncorrectCodeException(Throwable $exception): bool
    {
        return !str_contains($exception->getMessage(), 'Unable to exchange code for API access token.');
    }

    private function sendSocialLoginActivationMail(
        string $hash,
        WhitelabelUserSocial $whitelabelUserSocial,
        WhitelabelUser $whitelabelUser,
        string $socialType
    ): void {
        $this->activationHashGeneratorService->setNewActivationHashPerSocialUser($whitelabelUserSocial->id, $hash);
        $activationLink = $this->activationHashGeneratorService->generateActivationLink($whitelabelUser->token, $hash, $socialType);
        $this->confirmedMailerService->sendConfirmationEmail($whitelabelUser, $socialType, $activationLink);
    }

    private function loginUserIfSocialUserIdIsCorrect(
        WhitelabelUserSocial $whitelabelUserSocial,
        WhitelabelUser $whitelabelUser,
        Profile $userProfile
    ): void {
        $isSocialUserIdCorrect = $whitelabelUserSocial->socialUserId === $userProfile->identifier;
        if ($isSocialUserIdCorrect) {
            UserHelper::setUserSession($whitelabelUser->id, $whitelabelUser->token, $whitelabelUser->hash, $whitelabelUser->email, true);

            $whitelabel = Container::get('whitelabel');
            $loginData = [
                'event' => 'login',
                'user_id' => $whitelabel['prefix'] . 'U' . $whitelabelUser->token,
            ];
            EventLoginHelper::addScripts($loginData);

            ConnectHelper::setSuccessLoginMessage();

            $order = Session::get("order");
            if (!empty($order)) {
                $this->cartService->createOrUpdateCart($whitelabelUser->id, $order);
            } else {
                $cart = $this->cartService->getCart($whitelabelUser->id);
                Session::set("order", $cart);
            }

            $this->redirectService->redirectToHomePage();
        }
    }
    
    private function setNotActiveMessageAndDoRedirectToSignUpPage(): void
    {
        FlashMessageHelper::set(FlashMessageHelper::TYPE_ERROR, AbstractAuthService::MESSAGES['notActiveAccount']);
        /** Redirect after social error from last-steps because it can't show register form when social connected is not correct */
        $this->redirectService->redirectToSignUpPage();
    } 
    
    private function sendActivationEmailAndDoRedirectToSignUpPage(
        WhitelabelUserSocial $whitelabelUserSocial,
        WhitelabelUser $whitelabelUser,
        string $socialType
    ): void {
        $shouldSendActivationMail = !empty($whitelabelUserSocial->lastHashSentAt)
            && Helpers_Time::isDayPassed($whitelabelUserSocial->lastHashSentAt);
        if ($shouldSendActivationMail) {
            $hash = $this->activationHashGeneratorService->generateActivationHash($whitelabelUser->salt);
            $this->sendSocialLoginActivationMail($hash, $whitelabelUserSocial, $whitelabelUser, $socialType);
            FlashMessageHelper::set(FlashMessageHelper::TYPE_ERROR, MessageHelper::getTranslatedActivationConfirmEmail());
        } else {
            FlashMessageHelper::set(FlashMessageHelper::TYPE_ERROR, MessageHelper::getTranslatedActivationConfirmEmailBeforeDayPassed());
        }
        
        /** Redirect after social error from last-steps because it can't show register form when social connected is not correct */
        $this->redirectService->redirectToSignUpPage();
    }

    private function fillRegisterForm(Profile $userProfile, WhitelabelSocialApi $whitelabelSocialApi): void
    {
        ProfileHelper::setSocialProfileToSession($userProfile);
        /**
         * We add to session SocialConnect,SocialName,userSocialId for add while register connection to social.
         * platform/fuel/app/classes/forms/wordpress/user/register.php on Line: 639
         * FLow: signup -> user press social connect button -> redirect to page last steps -> user press registration button
         * -> redirect to signup -> checking form. If form is correct user will be logged.
         */
        Session::set(ConnectHelper::SOCIAL_CONNECT_KEY, true);
        Session::set('whitelabelSocialApiId', $whitelabelSocialApi->id);
        Session::set('socialUserId', $userProfile->identifier);
        /**
         * We make redirect after form on last steps contain errors.
         * We should set this on session because we used register form on last steps.
         * FLow: signup -> user press social connect button -> reidirect to page last steps -> user make mistake in form
         * -> user press registration button -> redirect to signup -> checking form ->
         * an error was found in the form-> redirect to the current social media last steps page
         */
        Session::set('socialType', $whitelabelSocialApi->socialType->type);
        /**
         * It will be skipped to show user the registration form from page-last-steps.
         * wordpress/wp-content/themes/base/page-last-steps.php (In this file we run this service)
         */
    }
}
