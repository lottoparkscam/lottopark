<?php

namespace Helpers;

use Container;
use Fuel\Core\Cache;
use Fuel\Core\CacheNotFoundException;
use Helpers_Time;
use Lotto_Helper;
use Models\Whitelabel;
use Throwable;

class WhitelabelHelper
{
    public const LOGIN_FIELD_EMAIL = 'email';
    public const LOGIN_FIELD_LOGIN = 'login';

    /**
     * This function uses cache in order to avoid calling query to check it
     * after every single enter to website
     *
     * @var string $cacheKey - example: 'lottopark.com_login_method'
     */
    public static function isLoginByUserLoginAllowed(): bool
    {
        $whitelabelUrl = Lotto_Helper::getWhitelabelDomainFromUrl();
        $cacheKey = "{$whitelabelUrl}_login_method";
        $whitelabel = Container::get('whitelabel');

        try {
            $isLoginByUserLoginAllowed = Cache::get($cacheKey);
        } catch (CacheNotFoundException $exception) {
            $isLoginByUserLoginAllowed = (bool) $whitelabel->useLoginsForUsers;
            Cache::set($cacheKey, $isLoginByUserLoginAllowed, Helpers_Time::HOUR_IN_SECONDS);
        } catch (Throwable $e) {
            $isLoginByUserLoginAllowed = (bool) $whitelabel->useLoginsForUsers;
        }

        return $isLoginByUserLoginAllowed;
    }

    public static function isLoginByEmail(): bool
    {
        return !self::isLoginByUserLoginAllowed();
    }

    public static function getLoginField(): string
    {
        return WhitelabelHelper::isLoginByUserLoginAllowed() ? self::LOGIN_FIELD_LOGIN : self::LOGIN_FIELD_EMAIL;
    }

    public static function getId(): int
    {
        $whitelabel = Container::get('whitelabel');
        return $whitelabel->id;
    }

    /**
     * DO NOT USE HERE AUTOLOADED CLASS BECAUSE WE USE THIS FUNCTION IN WORDPRESS_IN_FUEL.PHP
     * We use global here because this function is also used in wordpress_in_fuel.
     * There isn't autoloader, DB, config and other things, so we import casinoConfig by require_once
     */
    public static function getCasinoTitleMap(string $domain): array
    {
        global $casinoConfig;

        $titleMap = $casinoConfig['titleMap'] ?? [];
        return $titleMap[$domain] ?? [];
    }

    public static function convertTitle(string $title, string $domain): string
    {
        $titleMap = self::getCasinoTitleMap($domain);

        if (isset($titleMap[$title])) {
            return $titleMap[$title];
        }

        return $title;
    }

    public static function isActivationRequired(): bool
    {
        $whitelabel = Container::get('whitelabel');
        return $whitelabel->userActivationType === Whitelabel::ACTIVATION_TYPE_REQUIRED;
    }
}
