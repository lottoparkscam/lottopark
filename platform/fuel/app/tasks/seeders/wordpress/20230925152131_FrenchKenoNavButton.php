<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;
use Models\Lottery;

final class FrenchKenoNavButton extends AbstractNavigation
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com']; 
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'French Keno',
        'pl' => 'Francuskie Keno',
        'az' => 'Fransız Keno',
        'cs' => 'Francouzské Keno',
        'da' => 'Fransk Keno',
        'de' => 'Französisches Keno',
        'et' => 'Prantsuse Keno',
        'es' => 'Keno francés',
        'fr' => 'Keno français',
        'hr' => 'Francuski Keno',
        'id' => 'Keno Perancis',
        'it' => 'Keno francese',
        'lv' => 'Francijas Keno',
        'lt' => 'Prancūziškas Keno',
        'hu' => 'Francia Kenó',
        'mk' => 'Француско Кено',
        'nl' => 'Franse Keno',
        'pt' => 'Keno Francês',
        'ro' => 'Franta Keno',
        'sq' => 'Keno Franceze',
        'sk' => 'Francúzske Keno',
        'sl' => 'Francoski Keno',
        'sr' => 'Francuski Keno',
        'sv' => 'Franska Keno',
        'fil' => 'Pranses na Keno',
        'vi' => 'Keno tiếng Pháp',
        'tr' => 'Fransız Keno',
        'uk' => 'Французьке кено',
        'el' => 'Γαλλικό Κίνο',
        'bg' => 'Френско Кено',
        'ru' => 'Французкое кено',
        'ge' => 'ფრანგული კენო',
        'ar' => 'يانصيب الكينو الفرنسي',
        'hi' => 'फ्रेंच केनो',
        'bn' => 'ফরাসি কেনো',
        'th' => 'Keno ฝรั่งเศส',
        'ko' => '프랑스 키노',
        'zh' => '法国基诺',
	]; 
	protected const SLUG_FOR_LINK = 'play/' . Lottery::FRENCH_KENO_SLUG; 

}
