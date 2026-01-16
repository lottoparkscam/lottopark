<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;

final class CasinoToFooterNavButton extends AbstractNavigation
{
    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];
    protected const SLUG_FOR_LINK = 'casino';
    protected const MENU = 'footer';
    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'Casino',
    ];
}
