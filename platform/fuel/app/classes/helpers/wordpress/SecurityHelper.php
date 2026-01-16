<?php

namespace Helpers\Wordpress;

use Helpers\CountryHelper;
use Models\Whitelabel;

class SecurityHelper
{
    public static function shouldBlockSpainForV1(array $whitelabel): bool
    {
        $isNotV1Type = (int)$whitelabel['type'] !== Whitelabel::TYPE_V1;
        if ($isNotV1Type) {
            return false;
        }

        // Slots endpoint check own ip's whitelist
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $isNotSlotsEndpoint = !str_starts_with($path, '/api/slots/');

        return $isNotSlotsEndpoint && CountryHelper::iso() === 'ES';
    }
}