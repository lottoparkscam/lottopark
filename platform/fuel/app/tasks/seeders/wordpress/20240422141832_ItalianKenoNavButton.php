<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;
use Models\Lottery;

final class ItalianKenoNavButton extends AbstractNavigation
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com']; 
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'Italian Keno',
        'pl' => 'Włoskie Keno',
        'az' => 'İtalyan Keno',
        'cs' => 'Italské Keno',
        'da' => 'Italiensk Keno',
        'de' => 'Italienisches Keno',
        'et' => 'Itaalia Keno',
        'es' => 'Keno italiano',
        'fr' => 'Keno italien',
        'hr' => 'Talijanski Keno',
        'id' => 'Keno Italia',
        'it' => 'Keno italiano',
        'lv' => 'Itālijas Keno',
        'lt' => 'Itališkas Keno',
        'hu' => 'Olasz Kenó',
        'mk' => 'Италијанско Кено',
        'nl' => 'Italiaanse Keno',
        'pt' => 'Keno Italiano',
        'ro' => 'Italia Keno',
        'sq' => 'Keno Italiane',
        'sk' => 'Talianske Keno',
        'sl' => 'Italijanski Keno',
        'sr' => 'Italijanski Keno',
        'sv' => 'Italienska Keno',
        'fil' => 'Italyanong Keno',
        'vi' => 'Keno tiếng Ý',
        'tr' => 'İtalyan Keno',
        'uk' => 'Італійське кено',
        'el' => 'Ιταλικό Κίνο',
        'bg' => 'Италианско Кено',
        'ru' => 'Итальянское кено',
        'ge' => 'იტალიური კენო',
        'ar' => 'يانصيب الكينو الإيطالي',
        'hi' => 'इतालवी केनो',
        'bn' => 'ইতালিয়ান কেনো',
        'th' => 'Keno อิตาลี',
        'ko' => '이탈리아 키노',
        'zh' => '意大利基诺',
		'fa' => 'کینو ایتالیایی',
		'fi' => 'Italialainen Keno',
		'ja' => 'イタリアキノ',
		'he' => 'Keno איטלקי',
	]; 
	protected const SLUG_FOR_LINK = 'play/' . Lottery::ITALIAN_KENO_SLUG; 

}
