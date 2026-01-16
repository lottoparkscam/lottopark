<?php

namespace Services\SocialMediaConnect;

use Container;
use Exceptions\SocialMedia\ConfirmLoginException;
use Exceptions\SocialMedia\IncorrectTypeException;
use Exceptions\SocialMedia\InvalidActivationHash;
use Exceptions\SocialMedia\InvalidUserTokenException;
use Exceptions\SocialMedia\UserConfirmSocialLoginBeforeException;
use Exceptions\SocialMedia\WhitelabelUserSocialConnectionNotExists;
use Fuel\Core\Input;
use Helpers\FlashMessageHelper;
use Helpers\SocialMediaConnect\LastStepsHelper;
use Helpers\UserHelper;
use Helpers_General;
use Helpers_Time;
use Lotto_Security;
use Models\Whitelabel;
use Models\WhitelabelUser;
use Models\WhitelabelUserSocial;
use Repositories\Orm\WhitelabelUserRepository;
use Repositories\WhitelabelSocialApiRepository;
use Repositories\WhitelabelUserSocialRepository;
use Services\Auth\AbstractAuthService;
use Services\Auth\WordpressLoginService;
use Services\RedirectService;

class ActivationService
{
    private WhitelabelUserSocialRepository $whitelabelUserSocialRepository;
    private WhitelabelUserRepository $whitelabelUserRepository;
    private RedirectService $redirectService;
    private WhitelabelSocialApiRepository $whitelabelSocialApiRepository;
    private WordpressLoginService $wordpressLoginService;

    public function __construct(
        WhitelabelUserSocialRepository $whitelabelUserSocialRepository,
        WhitelabelUserRepository $whitelabelUserRepository,
        RedirectService $redirectService,
        WhitelabelSocialApiRepository $whitelabelSocialApiRepository,
        WordpressLoginService $wordpressLoginService,
    )
    {
        $this->redirectService = $redirectService;
        $this->whitelabelUserSocialRepository = $whitelabelUserSocialRepository;
        $this->whitelabelUserRepository = $whitelabelUserRepository;
        $this->whitelabelSocialApiRepository = $whitelabelSocialApiRepository;
        $this->wordpressLoginService = $wordpressLoginService;
    }

    public function generateActivationHash(string $userSalt): string
    {
        return Lotto_Security::generate_time_hash($userSalt, Helpers_Time::getCarbonDateNowInUtc());
    }

    public function setNewActivationHashPerSocialUser(int $whitelabelUserSocialId, string $hash): void
    {
        /** @var WhitelabelUserSocial|null $whitelabelUserSocial */
        $whitelabelUserSocial = $this->whitelabelUserSocialRepository->findOneById($whitelabelUserSocialId);
        if (empty($whitelabelUserSocial)) {
            return;
        }

        if (!empty($whitelabelUserSocial->lastHashSentAt) && Helpers_Time::isDayPassed($whitelabelUserSocial->lastHashSentAt)) {
            $this->whitelabelUserSocialRepository->updateHash($whitelabelUserSocialId, $hash, Helpers_Time::getCarbonDateNowInUtc());
        }
    }

    public function generateActivationLink(string $userToken, string $hash, string $socialType): string
    {
        $activationLink = lotto_platform_get_permalink_by_slug('activation');
        return $activationLink . $userToken . '/' . $hash . '?socialName=' . $socialType;
    }

    public function isSocialActivation(): bool
    {
        return !empty(Input::get(LastStepsHelper::SOCIAL_NAME_PARAMETER));
    }

    public function startSocialAccountActivation(): void
    {
        try {
            $socialType = LastStepsHelper::getSocialType();
            $this->activateSocialLogin($socialType);
        } catch (ConfirmLoginException) {
            /** @var Whitelabel $whitelabel */
            $whitelabel = Container::get('whitelabel');
            /** @var WhitelabelUser $whitelabelUser */
            $whitelabelUser = $this->whitelabelUserRepository->findByTokenAndWhitelabelId($this->getUserToken(), $whitelabel->id);

            $whitelabelSocialApi = $this->whitelabelSocialApiRepository->findWhitelabelSocialSettingsBySocialType($socialType);
            $this->whitelabelUserSocialRepository->confirmSocialLogin($whitelabelUser->id, $whitelabelSocialApi->id);
            $this->whitelabelUserSocialRepository->removeUnusedHashAndHashSentDate($whitelabelUser->id, $whitelabelSocialApi->id);

            $isAccountActivationRequired = $whitelabel->isActivationForUsersRequired() && $whitelabelUser->isUserNotActivated();
            if ($isAccountActivationRequired) {
                $resendLink = $this->wordpressLoginService->getResendLink($whitelabelUser->id);
                FlashMessageHelper::set(FlashMessageHelper::TYPE_ERROR, sprintf(_(AbstractAuthService::MESSAGES['activationLink']), $resendLink));
                $this->redirectService->redirectToLoginPage();
                /** Add return because it can`t mock exit in PHPUnit */
                return;
            }

            $whitelabelUserIsNotActive = !$whitelabelUser->isActive;
            if($whitelabelUserIsNotActive) {
                FlashMessageHelper::set(FlashMessageHelper::TYPE_ERROR, AbstractAuthService::MESSAGES['notActiveAccount']);
                /** Redirect after social error from last-steps because it can't show register form when social connected is not correct */
                $this->redirectService->redirectToSignUpPage();
                /** Add return because it can`t mock exit in PHPUnit */
                return;
            }

                UserHelper::setUserSession(
                    $whitelabelUser->id,
                    $whitelabelUser->token,
                    $whitelabelUser->hash,
                    $whitelabelUser->email,
                    true
                );

            FlashMessageHelper::set(FlashMessageHelper::TYPE_SUCCESS, MessageHelper::getTranslatedSucceedConfirmedMail(), true);
            $this->redirectService->redirectToHomePage();
            /** We must add this return because we cant mock exit in PHPUnit */
            return;
        } catch (InvalidActivationHash|InvalidUserTokenException|IncorrectTypeException|WhitelabelUserSocialConnectionNotExists) {
            FlashMessageHelper::set(FlashMessageHelper::TYPE_ERROR, MessageHelper::getTranslatedActivationIncorrectLink(), true);
            $this->redirectService->redirectToHomePage();
        } catch (UserConfirmSocialLoginBeforeException) {
            $message = MessageHelper::getTranslatedAccountHasBeenActivatedBeforeLoggedInUser();
            $isUserNotLogged = UserHelper::isUserNotLogged();
            if ($isUserNotLogged) {
                $message = MessageHelper::getTranslatedAccountHasBeenActivatedBefore();
            }
            FlashMessageHelper::set(FlashMessageHelper::TYPE_SUCCESS, $message, true);
            $this->redirectService->redirectToHomePage();
        }
    }

    /**
     * @throws ConfirmLoginException
     * @throws InvalidUserTokenException
     * @throws UserConfirmSocialLoginBeforeException
     * @throws InvalidActivationHash
     * @throws WhitelabelUserSocialConnectionNotExists
     */
    public function activateSocialLogin(string $socialType): void
    {
        $token = $this->getUserToken();
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        /** @var WhitelabelUser $whitelabelUser */
        $whitelabelUser = $this->whitelabelUserRepository->findByTokenAndWhitelabelId($token, $whitelabel->id);
        $this->throwSocialUserTokenInvalidException($whitelabelUser);
        $whitelabelSocialApi = $this->whitelabelSocialApiRepository->findWhitelabelSocialSettingsBySocialType($socialType);
        $whitelabelUserSocial = $this->whitelabelUserSocialRepository->findEnabledByWhitelabelUserIdAndWhitelabelSocialAppId($whitelabelUser->id, $whitelabelSocialApi->id);
        $this->throwWhitelabelUserSocialConnectionNotExistsException($whitelabelUserSocial);
        $this->throwUserConfirmSocialLoginBeforeException($whitelabelUserSocial);
        $this->throwInvalidActivationHash($whitelabelUserSocial);
        $this->throwConfirmSocialLoginException($whitelabelUserSocial);
    }

    public function getUserToken(): string
    {
        return $this->getRequestUriParameter(1);
    }

    public function getUserActivationHash(): string
    {
        return $this->getRequestUriParameter(2);
    }

    /**
     * Social activation link example: https://lottopark.loc/activation/751330564/89d7eba78807b532cf7594f6324d87067eb524defc7f0223392800eddd6694e9?socialName=Facebook
     * Parameter 1 (751330564) is user token.
     * Parameter 2 (89d7eba78807b532cf7594f6324d87067eb524defc7f0223392800eddd6694e9) is activation hash from whitelabel_user_social.activation_hash
     */
    private function getRequestUriParameter(int $parameterId): string
    {
        $urlToParse = Input::server('REQUEST_URI');
        $requestPathWithoutParameters = parse_url($urlToParse)['path'];
        return explode('/', trim($requestPathWithoutParameters, '/'))[$parameterId];
    }


    /**
     * @throws WhitelabelUserSocialConnectionNotExists
     */
    private function throwWhitelabelUserSocialConnectionNotExistsException(?WhitelabelUserSocial $whitelabelUserSocial): void
    {
        if (empty($whitelabelUserSocial)) {
            throw new WhitelabelUserSocialConnectionNotExists();
        }
    }

    /**
     * @throws ConfirmLoginException
     */
    private function throwConfirmSocialLoginException(WhitelabelUserSocial $whitelabelUserSocial): void
    {
        if ($whitelabelUserSocial->isUserNotConfirmedByEmail()) {
            throw new ConfirmLoginException();
        }
    }

    /**
     * @throws InvalidUserTokenException
     */
    private function throwSocialUserTokenInvalidException(?WhitelabelUser $whitelabelUser): void
    {
        if (empty($whitelabelUser)) {
            throw new InvalidUserTokenException();
        }
    }

    /**
     * @throws UserConfirmSocialLoginBeforeException
     */
    private function throwUserConfirmSocialLoginBeforeException(WhitelabelUserSocial $whitelabelUserSocial): void
    {
        if ($whitelabelUserSocial->isConfirmedByEmail()) {
            throw new UserConfirmSocialLoginBeforeException();
        }
    }

    /**
     * @throws InvalidActivationHash
     */
    private function throwInvalidActivationHash(WhitelabelUserSocial $whitelabelUserSocial): void
    {
        if ($whitelabelUserSocial->activationHash !== $this->getUserActivationHash()) {
            throw new InvalidActivationHash();
        }
    }
}
