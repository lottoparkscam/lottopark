<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;
use Models\Lottery;

final class GreekKenoNavButton extends AbstractNavigation
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com']; 
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'Greek Keno',
        'pl' => 'Greckie Keno',
        'az' => 'Greek Keno',
        'cs' => 'Greek Keno',
        'da' => 'Greek Keno',
        'de' => 'Greek Keno',
        'et' => 'Greek Keno',
        'es' => 'Greek Keno',
        'fr' => 'Greek Keno',
        'hr' => 'Greek Keno',
        'id' => 'Greek Keno',
        'it' => 'Greek Keno',
        'lv' => 'Greek Keno',
        'lt' => 'Greek Keno',
        'hu' => 'Greek Keno',
        'mk' => 'Greek Keno',
        'nl' => 'Greek Keno',
        'pt' => 'Greek Keno',
        'ro' => 'Grecia Keno',
        'sq' => 'Greek Keno',
        'sk' => 'Greek Keno',
        'sl' => 'Greek Keno',
        'sr' => 'Greek Keno',
        'sv' => 'Greek Keno',
        'fil' => 'Greek Keno',
        'vi' => 'Greek Keno',
        'tr' => 'Greek Keno',
        'uk' => 'Greek Keno',
        'el' => 'Greek Keno',
        'bg' => 'Greek Keno',
        'ru' => 'Greek Keno',
        'ge' => 'Greek Keno',
        'ar' => 'Greek Keno',
        'hi' => 'Greek Keno',
        'bn' => 'Greek Keno',
        'th' => 'Greek Keno',
        'ko' => 'Greek Keno',
        'zh' => 'Greek Keno',
	]; 
	protected const SLUG_FOR_LINK = 'play/' . Lottery::GREEK_KENO_SLUG; 

}
