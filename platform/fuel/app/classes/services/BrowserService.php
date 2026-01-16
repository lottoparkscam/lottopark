<?php

declare(strict_types=1);

namespace Services;

use Helpers_General;

class BrowserService
{
    public function getBrowser(): string
    {
        return Helpers_General::get_browser();
    }

    public function getOs(): string
    {
        return Helpers_General::get_os();
    }
}