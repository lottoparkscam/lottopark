<?php

namespace Services\Auth;

use Carbon\Carbon;
use Container;
use Fuel\Core\Input;
use Fuel\Core\Session;
use Fuel\Core\Security;
use Models\Whitelabel;
use Repositories\WhitelabelRepository;
use Lotto_Security;
use Repositories\Orm\WhitelabelUserRepository;
use Helpers_General;
use Exception;
use Lotto_Helper;
use Fuel\Core\Event;
use Helpers_Time;
use Helpers\UserHelper;
use Models\WhitelabelUser;
use Throwable;
use Helpers\FlashMessageHelper;
use Helpers\RedirectHelper;

abstract class AbstractAuthService
{
    public const MESSAGES = [
        'securityError' => 'Security error! Please try again.',
        'wrongCaptcha' => 'Incorrect captcha! Please try again.',
        'loginAttemptsReached' => 'Too many login attempts! Please try again later.',
        'wrongLoginCredentials' => 'Wrong login credentials.',
        'successLogin' => 'You have been successfully logged in!',
        'notActiveAccount' => 'Your account is not active. Please contact us to activate your account.',
        'activationLink' =>
            "Your account is not active. Please follow the activation link provided in the e-mail.
             If you don't see the e-mail, make sure to check your spam folder.
              You can also <a href='%s'>try to resend </a> or <a href='/contact'>contact us</a> for manual activation."

    ];

    protected Whitelabel $whitelabel;
    protected WhitelabelRepository $whitelabelRepository;
    protected WhitelabelUserRepository $whitelabelUserRepository;

    public function __construct(WhitelabelRepository $whitelabelRepository, WhitelabelUserRepository $whitelabelUserRepository)
    {
        $this->whitelabelRepository = $whitelabelRepository;
        $this->whitelabelUserRepository = $whitelabelUserRepository;

        $this->whitelabel = Container::get('whitelabel');
    }

    protected function checkSecurity(bool $checkCaptcha): bool
    {
        if (empty(Input::post())) {
            return false;
        }

        if ($this->isHoneypotIncorrect()) {
            FlashMessageHelper::set(FlashMessageHelper::TYPE_ERROR, self::MESSAGES['securityError']);
            return false;
        }

        if (!Security::check_token()) {
            FlashMessageHelper::set(FlashMessageHelper::TYPE_ERROR, self::MESSAGES['securityError']);
            return false;
        }

        if (!Lotto_Security::check_captcha() && $checkCaptcha) {
            FlashMessageHelper::set(FlashMessageHelper::TYPE_ERROR, self::MESSAGES['wrongCaptcha']);
            return false;
        }

        if (!Lotto_Security::check_IP()) {
            FlashMessageHelper::set(FlashMessageHelper::TYPE_ERROR, self::MESSAGES['loginAttemptsReached']);
            return false;
        }

        return true;
    }

    protected function isHoneypotIncorrect(): bool
    {
        return Input::post('currency_a') != null || Input::post('currency_b') != null || Input::post('currency_c') != null;
    }

    public function getResendLink(int $userId): string
    {
        $user = $this->whitelabelUserRepository->findOneById($userId);
        $now = Carbon::now('UTC');
        $resendActivationHash = Lotto_Security::generate_time_hash($user->salt, $now);
        try {
            $user->resend_hash = $resendActivationHash;
            $user->save();
        } catch (Throwable $e) {
            RedirectHelper::redirect(
                RedirectHelper::HOMEPAGE_SLUG,
                FlashMessageHelper::TYPE_ERROR,
                'Something went wrong, we are working on it.',
                true,
                'Cannot update user resend_hash' . $e->getMessage()
            );
        }

        return 'https://' . $this->whitelabel->domain . '/resend/' . $user->token . '/' . $resendActivationHash;
    }

    /** @throws Exception when cannot find user */
    protected function updateUserInfo(int $userId): void
    {
        $authorizedUser = $this->whitelabelUserRepository->findOneById($userId);

        //This situation should not be happened
        if (is_null($authorizedUser)) {
            throw new Exception('Received null authorized user. User with id: ' . $userId);
        }

        $now = Carbon::now();
        $lastActiveTimestamp = new Carbon($authorizedUser->last_active, 'UTC');
        $shouldUpdateGeoInfo = Helpers_Time::isDateBeforeDate($lastActiveTimestamp, Carbon::now()->subHour());
        if ($shouldUpdateGeoInfo) {
            $country = $authorizedUser->last_country ?? null;
            $ipHasChanged = $authorizedUser->last_ip !== Lotto_Security::get_IP();
            if ($ipHasChanged) {
                $geoIp = Lotto_Helper::get_geo_IP_record(Lotto_Security::get_IP());
                if ($geoIp) {
                    /** @phpstan-ignore-next-line */
                    $country = $geoIp->country->isoCode;
                }
            }

            $userSet = [
                'last_active' => $now->format(Helpers_Time::DATETIME_FORMAT),
                'last_ip' => Lotto_Security::get_IP(),
                'last_country' => $country,
                'system_type' => Helpers_General::get_os(),
                'browser_type' => Helpers_General::get_browser()
            ];

            $authorizedUser->set($userSet);
            $authorizedUser->save();

            Event::trigger('user_update', [
                'whitelabel_id' => $authorizedUser->whitelabel_id,
                'user_id' => $authorizedUser->id,
                'plugin_data' => $userSet,
            ]);
        }
    }

    protected function setUserSession(WhitelabelUser $user, bool $rememberUser = false): void
    {
        UserHelper::setUserSession(
            $user->id,
            $user->token,
            $user->hash,
            $user->email,
            $rememberUser
        );
    }
}
