<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;
use Models\Lottery;

final class SlovakKeno10NavButton extends AbstractNavigation
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com']; 
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'Slovak Keno 10',
        'pl' => 'Słowackie Keno 10',
        'az' => 'Slovak Keno 10',
        'cs' => 'Slovenské Keno 10',
        'da' => 'Slovakisk Keno 10',
        'de' => 'Slowakisches Keno 10',
        'et' => 'Slovakkia Keno 10',
        'es' => 'Keno 10 eslovaco',
        'fr' => 'Keno 10 slovaque',
        'hr' => 'Slovački Keno 10',
        'id' => 'Keno 10 Slovakia',
        'it' => 'Keno 10 slovacco',
        'lv' => 'Slovākijas Keno 10',
        'lt' => 'Slovakiškas Keno 10',
        'hu' => 'Szlovák Kenó 10',
        'mk' => 'Словачко Кено 10',
        'nl' => 'Slowaakse Keno 10',
        'pt' => 'Keno 10 Eslovaco',
        'ro' => 'Slovacia Keno 10',
        'sq' => 'Keno 10 Sllovake',
        'sk' => 'Slovenské Keno 10',
        'sl' => 'Slovaški Keno 10',
        'sr' => 'Slovački Keno 10',
        'sv' => 'Slovakisk Keno 10',
        'fil' => 'Slovak na Keno 10',
        'vi' => 'Keno 10 tiếng Slovak',
        'tr' => 'Slovak Keno 10',
        'uk' => 'Словацьке кено 10',
        'el' => 'Σλοβάκικο Κίνο 10',
        'bg' => 'Словашко Кено 10',
        'ru' => 'Словакское кено 10',
        'ge' => ' სლოვაკური კენო 10',
        'ar' => 'يانصيب الكينو السلوفاكي 10',
        'hi' => 'स्लोवाक केनो 10',
        'bn' => 'স্লোভাক কেনো 10',
        'th' => 'Keno 10 สโลวาเกีย',
        'ko' => '슬로바키아 키노 10',
        'zh' => '斯洛伐克基诺 10',
		'fa' => 'کینو اسلواکی 10',
        'fi' => 'Slovakialainen Keno 10',
        'ja' => 'スロバキアキノ 10',
        'he' => 'Keno 10 סלובקי',
	]; 
	protected const SLUG_FOR_LINK = 'play/' . Lottery::SLOVAK_KENO_10_SLUG; 
}
