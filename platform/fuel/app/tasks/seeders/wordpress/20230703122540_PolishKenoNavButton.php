<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;
use Models\Lottery;

final class PolishKenoNavButton extends AbstractNavigation
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com']; 
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'Polish Keno',
        'pl' => 'Polish Keno',
        'az' => 'Polish Keno',
        'cs' => 'Polish Keno',
        'da' => 'Polish Keno',
        'de' => 'Polish Keno',
        'et' => 'Polish Keno',
        'es' => 'Polish Keno',
        'fr' => 'Polish Keno',
        'hr' => 'Polish Keno',
        'id' => 'Polish Keno',
        'it' => 'Polish Keno',
        'lv' => 'Polish Keno',
        'lt' => 'Polish Keno',
        'hu' => 'Polish Keno',
        'mk' => 'Polish Keno',
        'nl' => 'Polish Keno',
        'pt' => 'Polish Keno',
        'ro' => 'Polonia Keno',
        'sq' => 'Polish Keno',
        'sk' => 'Polish Keno',
        'sl' => 'Polish Keno',
        'sr' => 'Polish Keno',
        'sv' => 'Polish Keno',
        'fil' => 'Polish Keno',
        'vi' => 'Polish Keno',
        'tr' => 'Polish Keno',
        'uk' => 'Polish Keno',
        'el' => 'Polish Keno',
        'bg' => 'Polish Keno',
        'ru' => 'Polish Keno',
        'ge' => 'Polish Keno',
        'ar' => 'Polish Keno',
        'hi' => 'Polish Keno',
        'bn' => 'Polish Keno',
        'th' => 'Polish Keno',
        'ko' => 'Polish Keno',
        'zh' => 'Polish Keno',
	]; 
	protected const SLUG_FOR_LINK = 'play/' . Lottery::POLISH_KENO_SLUG; 

}
