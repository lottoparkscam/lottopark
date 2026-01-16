<?php

namespace Services\Auth;

use Carbon\Carbon;
use Helpers\UserHelper;
use Repositories\WhitelabelRepository;
use Repositories\Orm\WhitelabelUserRepository;
use Helpers_General;
use DateInterval;
use Lotto_Security;
use Forms\Wordpress\Forms_Wordpress_Email;
use Helpers\FlashMessageHelper;
use Helpers\RedirectHelper;
use Helpers_Time;
use Services\MailerService;
use Models\WhitelabelUser;

class UserActivationService extends AbstractAuthService
{
    private MailerService $mailerService;

    public function __construct(
        WhitelabelRepository $whitelabelRepository,
        WhitelabelUserRepository $whitelabelUserRepository,
        MailerService $mailerService
    ) {
        parent::__construct($whitelabelRepository, $whitelabelUserRepository);
        $this->mailerService = $mailerService;

        $user = UserHelper::getUser();
        if (!empty($user)) {
            $isUserWithoutActivation = $this->whitelabel->userActivationType === Helpers_General::ACTIVATION_TYPE_REQUIRED && !$user->isConfirmed;
            RedirectHelper::redirectIf($isUserWithoutActivation, RedirectHelper::HOMEPAGE_SLUG);
        }
    }

    public function resendActivationEmail(): void
    {
        $whitelabelUserToken = htmlentities(get_query_var('id'));

        $whitelabelUser = $this->whitelabelUserRepository->findByTokenAndWhitelabelId(
            $whitelabelUserToken,
            $this->whitelabel->id
        );

        RedirectHelper::redirectIf(
            is_null($whitelabelUser),
            RedirectHelper::HOMEPAGE_SLUG,
            FlashMessageHelper::TYPE_ERROR,
            'Incorrect resend link. Please contact us for manual activation.',
            true,
            "Cannot find user with provided user token: $whitelabelUserToken for whitelabel with id {$this->whitelabel->id}"
        );

        RedirectHelper::redirectIf(
            $whitelabelUser->isConfirmed,
            RedirectHelper::HOMEPAGE_SLUG,
            FlashMessageHelper::TYPE_SUCCESS,
            'Your account has been activated before. Please login to access your account.',
            true
        );

        RedirectHelper::redirectIf(
            $whitelabelUser->isDeleted,
            RedirectHelper::HOMEPAGE_SLUG,
            FlashMessageHelper::TYPE_SUCCESS,
            'Your account has been deleted. You cannot activate it.',
            true
        );

        RedirectHelper::redirectIf(
            $this->isResendEmailLimitReached($whitelabelUser->resendLast),
            RedirectHelper::HOMEPAGE_SLUG,
            FlashMessageHelper::TYPE_ERROR,
            'You can resend the activation link once per 24 hours. Please try again later or contact us.',
            true
        );

        $now = Carbon::now('UTC');
        $timeHash = Lotto_Security::generate_time_hash($whitelabelUser->salt, $now);
        $whitelabelUser->set([
            'activation_hash' => $timeHash,
            'resend_last' => $now->format(Helpers_Time::DATETIME_FORMAT),
            'activation_valid' => $now->add(new DateInterval('P1D'))->format(Helpers_Time::DATETIME_FORMAT)
        ]);
        $whitelabelUser->save();

        $link = $this->getActivationLink($whitelabelUser);
        $this->sendEmail($whitelabelUser->email, $link, ICL_LANGUAGE_CODE);
    }

    public function getActivationLink(WhitelabelUser $user): string
    {
        return 'https://' . $this->whitelabel->domain . '/activation/'  . $user->token . '/' . $user->activation_hash;
    }

    private function getEmailTemplate(string $resendLink, ?string $language): array
    {
        $wordpressEmail = new Forms_Wordpress_Email($this->whitelabel->to_array());
        $wordpressEmail = $wordpressEmail->get_email('register', $language, ['link' => $resendLink]);

        $wordpressEmail['title'] = str_replace('Account Activation', _('Account Activation'), $wordpressEmail['title']);

        return $wordpressEmail;
    }

    private function sendEmail(string $userEmail, string $resendLink, ?string $language = null): void
    {
        $emailTemplate = $this->getEmailTemplate($resendLink, $language);

        $isSuccess = $this->mailerService->sendPublic(
            $userEmail,
            $emailTemplate['title'],
            $emailTemplate['body_html'],
            $this->whitelabel->name,
            true
        );

        RedirectHelper::redirectIf(
            $isSuccess,
            RedirectHelper::HOMEPAGE_SLUG,
            FlashMessageHelper::TYPE_SUCCESS,
            'The activation e-mail has been resent to you.',
            true
        );

        RedirectHelper::redirectIf(
            !$isSuccess,
            RedirectHelper::HOMEPAGE_SLUG,
            FlashMessageHelper::TYPE_ERROR,
            'We have encountered an error while trying to send the message. Please contact us to activate your account.',
            true
        ); 
    }

    public function isResendEmailLimitReached(?Carbon $lastResendDate): bool
    {
        if (empty($lastResendDate)) {
            return false;
        }

        return Helpers_Time::isDayNotPassed($lastResendDate->format(Helpers_Time::DATETIME_FORMAT));
    }

    public function isResendEmailLimitNotReached(?Carbon $lastResendDate): bool
    {
        return !$this->isResendEmailLimitReached($lastResendDate);
    }
}
