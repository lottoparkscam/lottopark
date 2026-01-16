<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;
use Models\Lottery;

final class LatvianKenoNavButton extends AbstractNavigation
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com']; 
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'Latvian Keno',
        'pl' => 'Łotewskie Keno',
        'az' => 'Latış Keno',
        'cs' => 'Lotyšské Keno',
        'da' => 'Lettisk Keno',
        'de' => 'Lettisches Keno',
        'et' => 'Läti Keno',
        'es' => 'Keno letón',
        'fr' => 'Keno letton',
        'hr' => 'Latvijski Keno',
        'id' => 'Keno Latvia',
        'it' => 'Keno lettone',
        'lv' => 'Latvijas Keno',
        'lt' => 'Latviškas Keno',
        'hu' => 'Latviai Kenó',
        'mk' => 'Латвиско Кено',
        'nl' => 'Letse Keno',
        'pt' => 'Keno Letão',
        'ro' => 'Letonia Keno',
        'sq' => 'Keno Letoneze',
        'sk' => 'Lotyšské Keno',
        'sl' => 'Latvijski Keno',
        'sr' => 'Latvijski Keno',
        'sv' => 'Lettiska Keno',
        'fil' => 'Latvian na Keno',
        'vi' => 'Keno tiếng Latvia',
        'tr' => 'Letonya Keno',
        'uk' => 'Латвійське кено',
        'el' => 'Λετονικό Κίνο',
        'bg' => 'Латвийско Кено',
        'ru' => 'Латвийское кено',
        'ge' => 'ლატვიური კენო',
        'ar' => 'يانصيب الكينو بلاتفيا',
        'hi' => 'लातवियाई केनो',
        'bn' => 'লাতভিয়ান কেনো',
        'th' => 'Keno ลัตเวีย',
        'ko' => '라트비아 키노',
        'zh' => '拉脱维亚基诺',
	]; 
	protected const SLUG_FOR_LINK = 'play/' . Lottery::LATVIAN_KENO_SLUG; 

}
