<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;
use Models\Lottery;

final class LoteriaRomanaNavButton extends AbstractNavigation
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com']; 
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'Loto 6/49',
        'pl' => 'Loto 6/49',
        'az' => 'Loto 6/49',
        'cs' => 'Loto 6/49',
        'da' => 'Loto 6/49',
        'de' => 'Loto 6/49',
        'et' => 'Loto 6/49',
        'es' => 'Loto 6/49',
        'fr' => 'Loto 6/49',
        'hr' => 'Loto 6/49',
        'id' => 'Loto 6/49',
        'it' => 'Loto 6/49',
        'lv' => 'Loto 6/49',
        'lt' => 'Loto 6/49',
        'hu' => 'Loto 6/49',
        'mk' => 'Loto 6/49',
        'nl' => 'Loto 6/49',
        'pt' => 'Loto 6/49',
        'ro' => 'Loto 6/49',
        'sq' => 'Loto 6/49',
        'sk' => 'Loto 6/49',
        'sl' => 'Loto 6/49',
        'sr' => 'Loto 6/49',
        'sv' => 'Loto 6/49',
        'fil' => 'Loto 6/49',
        'vi' => 'Loto 6/49',
        'tr' => 'Loto 6/49',
        'uk' => 'Loto 6/49',
        'el' => 'Loto 6/49',
        'bg' => 'Loto 6/49',
        'ru' => 'Loto 6/49',
        'ge' => 'Loto 6/49',
        'ar' => 'Loto 6/49',
        'hi' => 'Loto 6/49',
        'bn' => 'Loto 6/49',
        'th' => 'Loto 6/49',
        'ko' => 'Loto 6/49',
        'zh' => 'Loto 6/49',
        'fa' => 'Loto 6/49',
        'fi' => 'Loto 6/49',
        'ja' => 'Loto 6/49',
        'he' => 'Loto 6/49',
	]; 
	protected const SLUG_FOR_LINK = 'play/' . Lottery::LOTO_6_49_SLUG;
}
