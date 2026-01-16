<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;

final class GgtknToFooterNavButton extends AbstractNavigation
{
    protected const IS_NOT_WORDPRESS_PAGE = true;
    protected const DIRECT_LINK = 'https://ggtkn.com/';

    protected const WP_DOMAIN_NAME_WITHOUT_PORT = [
        'lottopark.com',
        'lottomat.com',
        'lottohoy.com',
        'lotteo.com',
        'redfoxlotto.com',
        'lottolive24.com',
        'lottolooting.com',
        'doublejack.online',
        'lotto.monster',
        'megajackpot.win',
        'lotteryking.net',
    ];

    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'GG Token',
    ];

    protected const MENU = 'footer';
}
