<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;
use Models\Lottery;

final class HungarianKenoNavButton extends AbstractNavigation
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com']; 
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'Hungarian Keno',
        'pl' => 'Węgierskie Keno',
        'az' => 'Macar Keno',
        'cs' => 'Maďarské Keno',
        'da' => 'Ungarsk Keno',
        'de' => 'Ungarisches Keno',
        'et' => 'Ungari Keno',
        'es' => 'Keno húngaro',
        'fr' => 'Kéno hongrois',
        'hr' => 'Ungarski Keno',
        'id' => 'Keno Hungaria',
        'it' => 'Keno ungarais',
        'lv' => 'Ungāru Keno',
        'lt' => 'Ungariškas Keno',
        'hu' => 'Ungari Keno',
        'mk' => 'Унгарско Кено',
        'nl' => 'Hongaars Keno',
        'pt' => 'Keno Húngaro',
        'ro' => 'Ungaria Keno',
        'sq' => 'Keno Hungarez',
        'sk' => 'Maďarské Keno',
        'sl' => 'Madžarski Keno',
        'sr' => 'Mađarski Keno',
        'sv' => 'Ungerska Keno',
        'fil' => 'Hungarian na Keno',
        'vi' => 'Keno tiếng Hungary',
        'tr' => 'Macar Keno',
        'uk' => 'Угорське кено',
        'el' => 'Ουγγρικό Κίνο',
        'bg' => 'Унгарско Кено',
        'ru' => 'Венгерское кено',
        'ge' => 'უნგრელი კენო',
        'ar' => 'يانصيب الكينو المجرية',
        'hi' => 'हंगेरियन केनो',
        'bn' => 'হাঙ্গেরিয়ান কেনো',
        'th' => 'Keno ฮังการี',
        'ko' => '헝가리 케노',
        'zh' => '匈牙利基诺',
	]; 
	protected const SLUG_FOR_LINK = 'play/' . Lottery::HUNGARIAN_KENO_SLUG; 

}
