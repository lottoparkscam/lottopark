<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;
use Models\Lottery;

final class UkrainianKenoNavButton extends AbstractNavigation
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com']; 
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'Ukrainian Keno',
        'pl' => 'Ukraińskie Keno',
        'az' => 'Ukrayna Keno',
        'cs' => 'Ukrajinské Keno',
        'da' => 'Ukrainsk Keno',
        'de' => 'Ukrainisches Keno',
        'et' => 'Ukraina Keno',
        'es' => 'Keno ucraniano',
        'fr' => 'Keno ukrainien',
        'hr' => 'Ukrajinski Keno',
        'id' => 'Keno Ukraina',
        'it' => 'Keno ucraino',
        'lv' => 'Ukrainas Keno',
        'lt' => 'Ukrainietiškas Keno',
        'hu' => 'Ukrán Kenó',
        'mk' => 'Украинско Кено',
        'nl' => 'Oekraïens Keno',
        'pt' => 'Keno Ucraniano',
        'ro' => 'Ucraina Keno',
        'sq' => 'Keno Ukrainas',
        'sk' => 'Ukrajinské Keno',
        'sl' => 'Ukrajinski Keno',
        'sr' => 'Ukrajinski Keno',
        'sv' => 'Ukrainsk Keno',
        'fil' => 'Ukrainian na Keno',
        'vi' => 'Keno tiếng Ukraina',
        'tr' => 'Ukrayna Keno',
        'uk' => 'Українське Кено',
        'el' => 'Ουκρανικό Κίνο',
        'bg' => 'Украинско Кено',
        'ru' => 'Украинское Кено',
        'ge' => 'უკრაინული კენო',
        'ar' => 'يانصيب الكينو الأوكراني',
        'hi' => 'यूक्रेनी केनो',
        'bn' => 'ইউক্রেনীয় কেনো',
        'th' => 'Keno ยูเครน',
        'ko' => '우크라이나 키노',
        'zh' => '乌克兰基诺',
        'fa' => 'کینو اوکراینی',
        'fi' => 'Ukrainalainen Keno',
        'ja' => 'ウクライナキノ',
        'he' => 'Keno אוקראיני',
	]; 
	protected const SLUG_FOR_LINK = 'play/' . Lottery::UKRAINIAN_KENO_SLUG; 

}
