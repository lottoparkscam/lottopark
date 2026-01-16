<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractMenu;

final class CasinoFooterMenu extends AbstractMenu
{
    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];
    protected const POSITION = 'footer';
    protected const MENU_SLUG = 'casino';
    protected const LANGUAGES = [
        'en',
    ];
}
