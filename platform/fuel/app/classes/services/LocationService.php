<?php

declare(strict_types=1);

namespace Services;

use Lotto_Security;

class LocationService
{
    public function getIp(): string
    {
        return Lotto_Security::get_Ip();
    }
}