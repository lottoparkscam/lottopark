<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;

final class CasinoToHeaderNavButton extends AbstractNavigation
{
    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];
    protected const SLUG_FOR_LINK = 'casino';
    protected const MENU = 'primary';
    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'Casino',
    ];
}
