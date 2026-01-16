<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;
use Models\Lottery;

final class FinnishKenoNavButton extends AbstractNavigation
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com']; 
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'Finnish Keno',
        'pl' => 'Fińskie Keno',
        'az' => 'Fin Keno',
        'cs' => 'Finské Keno',
        'da' => 'Finsk Keno',
        'de' => 'Finnisches Keno',
        'et' => 'Soome Keno',
        'es' => 'Keno finlandés',
        'fr' => 'Keno finlandais',
        'hr' => 'Finski Keno',
        'id' => 'Keno Finlandia',
        'it' => 'Keno finlandese',
        'lv' => 'Somijas Keno',
        'lt' => 'Suomiškas Keno',
        'hu' => 'Finn Kenó',
        'mk' => 'Финско Кено',
        'nl' => 'Finse Keno',
        'pt' => 'Keno Finlandês',
        'ro' => 'Finlanda Keno',
        'sq' => 'Keno Finlandeze',
        'sk' => 'Fínske Keno',
        'sl' => 'Finski Keno',
        'sr' => 'Finski Keno',
        'sv' => 'Finska Keno',
        'fil' => 'Finnish na Keno',
        'vi' => 'Keno tiếng Phần Lan',
        'tr' => 'Fin Keno',
        'uk' => 'Фінське кено',
        'el' => 'Φινλανδικό Κίνο',
        'bg' => 'Финландско Кено',
        'ru' => 'Финское кено',
        'ge' => 'ფინური კენო',
        'ar' => 'يانصيب الكينو الفنلندي',
        'hi' => 'फिनिश केनो',
        'bn' => 'ফিনিশ কেনো',
        'th' => 'Keno ฟินแลนด์',
        'ko' => '핀란드 키노',
        'zh' => '芬兰基诺',
	]; 
	protected const SLUG_FOR_LINK = 'play/' . Lottery::FINNISH_KENO_SLUG; 

}
