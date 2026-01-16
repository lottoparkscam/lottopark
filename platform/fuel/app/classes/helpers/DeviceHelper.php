<?php

namespace Helpers;

use Container;
use Detection\MobileDetect;

class DeviceHelper
{
    private static function deviceDetails(): MobileDetect
    {
        /** @var MobileDetect $deviceDetails */
        $deviceDetails = Container::get(MobileDetect::class);
        return $deviceDetails;
    }

    public static function isMobile(): bool
    {
        return self::deviceDetails()->isMobile();
    }
}
