<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;
use Models\Lottery;

final class GermanKenoNavButton extends AbstractNavigation
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com']; 
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'German Keno',
        'pl' => 'Niemieckie Keno',
        'az' => 'Alman Keno',
        'cs' => 'Německé Keno',
        'da' => 'Tysk Keno',
        'de' => 'Deutsches Keno',
        'et' => 'Saksa Keno',
        'es' => 'Keno alemán',
        'fr' => 'Keno allemand',
        'hr' => 'Njemački Keno',
        'id' => 'Keno Jerman',
        'it' => 'Keno tedesco',
        'lv' => 'Vācijas Keno',
        'lt' => 'Vokiškas Keno',
        'hu' => 'Német Kenó',
        'mk' => 'Германско Кено',
        'nl' => 'Duitse Keno',
        'pt' => 'Keno Alemão',
        'ro' => 'Germania Keno',
        'sq' => 'Keno Gjermane',
        'sk' => 'Nemecké Keno',
        'sl' => 'Nemški Keno',
        'sr' => 'Nemački Keno',
        'sv' => 'Tyska Keno',
        'fil' => 'Alemang Keno',
        'vi' => 'Keno tiếng Đức',
        'tr' => 'Alman Keno',
        'uk' => 'Німецьке кено',
        'el' => 'Γερμανικό Κίνο',
        'bg' => 'Немско Кено',
        'ru' => 'Немецкое кено',
        'ge' => ' გერმანული კენო',
        'ar' => 'يانصيب الكينو الألماني',
        'hi' => 'जर्मन केनो',
        'bn' => 'জার্মান কেনো',
        'th' => 'Keno เยอรมัน',
        'ko' => '독일 키노',
        'zh' => '德国基诺',
		'fa' => 'کینو آلمانی',
        'fi' => 'Saksalainen Keno',
        'ja' => 'ドイツキノ',
        'he' => 'Keno גרמני',
	]; 
	protected const SLUG_FOR_LINK = 'play/' . Lottery::GERMAN_KENO_SLUG; 
}
