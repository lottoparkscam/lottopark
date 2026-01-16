<?php

namespace Tests\Unit\Classes\Lotto;

use Fuel\Core\Config;
use Fuel\Core\Fuel;
use Lotto_Security;
use Test_Unit;

class SecurityTest extends Test_Unit
{
    /** @test */
    public function hCaptchaIsDisableOnDeveloperEnv(): void
    {
        Fuel::$env = 'development';
        Lotto_Security::check_hcaptcha();
        $this->assertNull(Config::get('hcaptcha'));
    }

    /** @test */
    public function hCaptchaIsProdEnv(): void
    {
        Fuel::$env = 'production';
        Lotto_Security::check_hcaptcha();
        $this->assertNotNull(Config::get('hcaptcha'));
    }

    /** @test */
    public function reCaptchaIsDisableOnDeveloperEnv(): void
    {
        Fuel::$env = 'development';
        Lotto_Security::check_recaptcha();
        $this->assertNull(Config::get('recaptcha'));
    }

    /** @test */
    public function reCaptchaIsProdEnv(): void
    {
        $_SERVER['REMOTE_ADDR'] = 123;
        Fuel::$env = 'production';
        Lotto_Security::check_recaptcha();
        $this->assertNotNull(Config::get('recaptcha'));
    }
}
