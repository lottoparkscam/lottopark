<?php

namespace Helpers;

use Container;
use Exception;
use Fuel\Core\Response;
use Fuel\Core\Session;
use Models\WhitelabelUser;
use Repositories\Orm\WhitelabelUserRepository;

class UserHelper
{
    public const SHOULD_LOGOUT_AFTER_BROWSER_CLOSE_KEY = 'shouldLogoutAfterBrowserClose';
    public const REQUIRED_USER_SESSION_FIELDS = [
        'user.email',
        'user.hash',
        'user.id',
        'user.token',
        'is_user'
    ];

    /** Use this method only in places where DI is not supported */
    public static function getUser(): ?WhitelabelUser
    {
        if (self::isUserSessionIncorrect()) {
            return null;
        }
        /** @var WhitelabelUserRepository $whitelabelUserRepository */
        $whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);

        return $whitelabelUserRepository->getUserFromSession();
    }

    public static function getUserId(): ?int
    {
        return Session::get('user.id') ?? null;
    }

    public static function getUserToken(): ?string
    {
        return Session::get('user.token') ?? null;
    }

    public static function getUserModel(array $selectFields = []): ?WhitelabelUser
    {
        $token = self::getUserToken();
        $whitelabelId = Container::get('whitelabel')->id;

        if (empty($token)) {
            return null;
        }

        /** @var WhitelabelUserRepository $whitelabelUserRepository */
        $whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);

        return $whitelabelUserRepository->findByTokenWithSpecifiedSelect($token, $whitelabelId, $selectFields);
    }

    /** @param string $email even if whitelabel has auth by login, email is required */
    public static function setUserSession(int $userId, string $token, string $hash, string $email, bool $rememberUser = false): void
    {
        Session::set(UserHelper::SHOULD_LOGOUT_AFTER_BROWSER_CLOSE_KEY, !$rememberUser);

        Session::set('user.email', $email);
        Session::set('user.hash', $hash);
        Session::set('user.id', $userId);
        Session::set('user.token', $token);
        Session::set('is_user', true);

        Session::rotate();
    }

    /** @throws Exception when $field isn't set in user's session */
    public static function updateUserSession(string $field, mixed $value): void
    {
        if (!empty(Session::get($field))) {
            Session::set($field, $value);
            return;
        }

        throw new Exception('Cannot find provided user session.');
    }

    public static function logOutUser(): void
    {
        $messages = Session::get_flash('message');
        Session::destroy();
        if (!empty($messages)) {
            FlashMessageHelper::addMany($messages);
        }
        Session::rotate();

        if (function_exists('lotto_platform_home_url')) {
            Response::redirect(lotto_platform_home_url('/'));
        }

        // Redirect for places without wordpress
        Response::redirect(UrlHelper::getHomeUrlWithoutLanguage());
    }

    public static function isUserSessionIncorrect(): bool
    {
        if (empty(Session::get('user'))) {
            return true;
        }

        foreach (self::REQUIRED_USER_SESSION_FIELDS as $field) {
            if (is_null(Session::get($field))) {
                self::logOutUser();
                return true;
            }
        }

        return false;
    }

    /** 
     * This function should be called in bootstrap lvl
     * Fuel disallows to set config during code execution and overrides setting with default config
     */
    public static function setShouldLogoutAfterBrowserClose(): void
    {
        $shouldLogoutAfterBrowserClose = Session::get(self::SHOULD_LOGOUT_AFTER_BROWSER_CLOSE_KEY, true);
        Session::instance()->set_config('expire_on_close', $shouldLogoutAfterBrowserClose);
    }

    public static function isUserLogged(): bool
    {
        return Session::get('is_user', false);
    }

    public static function isUserNotLogged(): bool
    {
        return !self::isUserLogged();
    }
}
