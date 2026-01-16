<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;

final class LotteriesToCasinoFooterNavButton extends AbstractNavigation
{
    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];
    protected const SLUG_FOR_LINK = '/';
    protected const MENU = 'casino-footer';
    protected const FORCE_ENGLISH_NAME = true;
    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'Lotteries',
        'pl' => 'Lotteries',
        'az' => 'Lotteries',
        'cs' => 'Lotteries',
        'da' => 'Lotteries',
        'de' => 'Lotteries',
        'et' => 'Lotteries',
        'es' => 'Lotteries',
        'fr' => 'Lotteries',
        'hr' => 'Lotteries',
        'id' => 'Lotteries',
        'it' => 'Lotteries',
        'lv' => 'Lotteries',
        'lt' => 'Lotteries',
        'hu' => 'Lotteries',
        'mk' => 'Lotteries',
        'nl' => 'Lotteries',
        'pt' => 'Lotteries',
        'ro' => 'Lotteries',
        'sq' => 'Lotteries',
        'sk' => 'Lotteries',
        'sl' => 'Lotteries',
        'sr' => 'Lotteries',
        'sv' => 'Lotteries',
        'fil' => 'Lotteries',
        'vi' => 'Lotteries',
        'tr' => 'Lotteries',
        'uk' => 'Lotteries',
        'el' => 'Lotteries',
        'bg' => 'Lotteries',
        'ru' => 'Lotteries',
        'ge' => 'Lotteries',
        'ar' => 'Lotteries',
        'hi' => 'Lotteries',
        'bn' => 'Lotteries',
        'th' => 'Lotteries',
        'ko' => 'Lotteries',
        'zh' => 'Lotteries'
    ];
}
