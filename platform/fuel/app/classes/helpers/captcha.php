<?php

namespace Helpers;

use Fuel\Core\Config;
use Lotto_Security;

final class CaptchaHelper
{
    /** @var string */
    const RECAPTCHA_API_URL = 'https://www.google.com/recaptcha/api.js';

    /** @var string */
    const HCAPTCHA_API_URL = 'https://hcaptcha.com/1/api.js';

    /** @var string */
    const RECAPTCHA_VERIFICATION_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /** @var string */
    const HCAPTCHA_VERIFICATION_URL = 'https://hcaptcha.com/siteverify';

    /** @var array */
    const HCAPTCHA_COUNTRIES = ['CN'];

    
    public static function getCaptcha() : string
    {
        $userIP = Lotto_Security::get_IP();
        $useHcaptcha = CountryHelper::isIPFromCountries($userIP, self::HCAPTCHA_COUNTRIES);

        if ($useHcaptcha) {
            return self::getHcaptcha();
        }
        
        return self::getRecaptcha();
    }

    public static function getRecaptcha() : string
    {
        Config::load("recaptcha", true);

        return
        '<div class="form-group recaptcha">
            <div class="g-recaptcha" 
                data-sitekey="' . htmlspecialchars(Config::get("recaptcha.keys.site_key")) . '"></div>
        </div>';
    }

    public static function getHcaptcha() : string
    {
        return
        '<div class="form-group captcha">
            <div class="h-captcha" data-sitekey="' . htmlspecialchars(Config::get("hcaptcha.keys.site_key")) . '"></div>
        </div>';
    }

    public static function getCaptchaApiUrl() : string
    {
        $userIP = Lotto_Security::get_IP();
        $useHcaptcha = CountryHelper::isIPFromCountries($userIP, self::HCAPTCHA_COUNTRIES);

        if ($useHcaptcha) {
            return self::HCAPTCHA_API_URL;
        }

        return self::RECAPTCHA_API_URL;
    }

    public static function loadCaptchaConfig(): void
    {
        $userIP = Lotto_Security::get_IP();
        $useHcaptcha = CountryHelper::isIPFromCountries($userIP, self::HCAPTCHA_COUNTRIES);

        if ($useHcaptcha) {
            Config::load("hcaptcha", true);
        } else {
            Config::load("recaptcha", true);
        }
    }

    /** Auto detects captcha and hCaptcha (version for China) */
    public static function checkCaptcha(): bool
    {
        return Lotto_Security::check_captcha();
    }
}
