<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;
use Models\Lottery;

final class EuroDreamsNavButton extends AbstractNavigation
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com']; 
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'EuroDreams',
		'pl' => 'EuroDreams',
        'az' => 'EuroDreams',
        'cs' => 'EuroDreams',
        'da' => 'EuroDreams',
        'de' => 'EuroDreams',
        'et' => 'EuroDreams',
        'es' => 'EuroDreams',
        'fr' => 'EuroDreams',
        'hr' => 'EuroDreams',
        'id' => 'EuroDreams',
        'it' => 'EuroDreams',
        'lv' => 'EuroDreams',
        'lt' => 'EuroDreams',
        'hu' => 'EuroDreams',
        'mk' => 'EuroDreams',
        'nl' => 'EuroDreams',
        'pt' => 'EuroDreams',
        'ro' => 'EuroDreams',
        'sq' => 'EuroDreams',
        'sk' => 'EuroDreams',
        'sl' => 'EuroDreams',
        'sr' => 'EuroDreams',
        'sv' => 'EuroDreams',
        'fil' => 'EuroDreams',
        'vi' => 'EuroDreams',
        'tr' => 'EuroDreams',
        'uk' => 'EuroDreams',
        'el' => 'EuroDreams',
        'bg' => 'EuroDreams',
        'ru' => 'EuroDreams',
        'ge' => 'EuroDreams',
        'ar' => 'EuroDreams',
        'hi' => 'EuroDreams',
        'bn' => 'EuroDreams',
        'th' => 'EuroDreams',
        'ko' => 'EuroDreams',
        'zh' => 'EuroDreams',
	]; 
	protected const SLUG_FOR_LINK = 'play/' . Lottery::EURODREAMS_SLUG; 

}
