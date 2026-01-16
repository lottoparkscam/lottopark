<?php

namespace Tests;

use Fuel\Core\Config;
use Fuel\Core\Security;
use Helpers\SecurityHelper;
use Test_Unit;

class SecurityHelperTest extends Test_Unit
{
    /** @test */
    public function getCsrfInput_ShouldHaveValidData(): void
    {
        $csrfKey = Config::get('security.csrf_token_key');
        $csrfValue = Security::fetch_token();

        $expected = <<<CSRF
            <input type="hidden" name="$csrfKey" value="$csrfValue">
        CSRF;

        $this->assertNotEmpty($csrfKey);
        $this->assertNotEmpty($csrfValue);
        $this->assertSame($expected, SecurityHelper::getCsrfInput());
    }
}
