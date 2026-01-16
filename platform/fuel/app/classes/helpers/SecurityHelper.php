<?php

namespace Helpers;

use Fuel\Core\Config;
use Fuel\Core\Security;

final class SecurityHelper
{
    public static function getCsrfInput(): string
    {
        $csrfKey = Config::get('security.csrf_token_key');
        $csrfValue = Security::fetch_token();

        return <<<CSRF
            <input type="hidden" name="$csrfKey" value="$csrfValue">
        CSRF;
    }
}