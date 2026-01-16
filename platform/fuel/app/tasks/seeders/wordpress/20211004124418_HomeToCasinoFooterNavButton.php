<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;

final class HomeToCasinoFooterNavButton extends AbstractNavigation
{
    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];
    protected const SLUG_FOR_LINK = '/';
    protected const MENU = 'casino-footer';

    // en is enough, we have auto seed other languages
    // title translation is detected automatically
    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'Home'
    ];
}
