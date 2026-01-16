<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;
use Models\Lottery;

final class SlovakKenoNavButton extends AbstractNavigation
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com']; 
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'Slovak Keno',
        'pl' => 'Słowackie Keno',
        'az' => 'Slovak Keno',
        'cs' => 'Slovenské Keno',
        'da' => 'Slovakisk Keno',
        'de' => 'Slowakisches Keno',
        'et' => 'Slovakkia Keno',
        'es' => 'Keno eslovaco',
        'fr' => 'Keno slovaque',
        'hr' => 'Slovački Keno',
        'id' => 'Keno Slovakia',
        'it' => 'Keno slovacco',
        'lv' => 'Slovākijas Keno',
        'lt' => 'Slovakiškas Keno',
        'hu' => 'Szlovák Kenó',
        'mk' => 'Словачко Кено',
        'nl' => 'Slowaakse Keno',
        'pt' => 'Keno Eslovaco',
        'ro' => 'Slovacia Keno',
        'sq' => 'Keno Sllovake',
        'sk' => 'Slovenské Keno',
        'sl' => 'Slovaški Keno',
        'sr' => 'Slovački Keno',
        'sv' => 'Slovakisk Keno',
        'fil' => 'Slovak Keno',
        'vi' => 'Keno tiếng Slovak',
        'tr' => 'Slovak Keno',
        'uk' => 'Словацьке кено',
        'el' => 'Σλοβάκικο Κίνο',
        'bg' => 'Словашко Кено',
        'ru' => 'Словакское кено',
        'ge' => 'სლოვაკური კენო',
        'ar' => 'يانصيب الكينو السلوفاكي',
        'hi' => 'स्लोवाक केनो',
        'bn' => 'স্লোভাক কেনো',
        'th' => 'Keno สโลวาเกีย',
        'ko' => '슬로바키아 키노',
        'zh' => '斯洛伐克基诺',
	]; 
	protected const SLUG_FOR_LINK = 'play/' . Lottery::SLOVAK_KENO_SLUG; 

}
