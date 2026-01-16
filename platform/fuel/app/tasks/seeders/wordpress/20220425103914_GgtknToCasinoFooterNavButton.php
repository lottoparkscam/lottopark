<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;

final class GgtknToCasinoFooterNavButton extends AbstractNavigation
{
    protected const IS_NOT_WORDPRESS_PAGE = true;
    protected const DIRECT_LINK = 'https://ggtkn.com/';

    protected const WP_DOMAIN_NAME_WITHOUT_PORT = [
        'lottopark.com',
        'lottohoy.com',
        'lottomat.com',
        'redfoxlotto.com',
        'lotteryking.net',
        'lovcasino.com',
    ];

    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'GG Token',
    ];

    protected const MENU = 'casino-footer';
}
