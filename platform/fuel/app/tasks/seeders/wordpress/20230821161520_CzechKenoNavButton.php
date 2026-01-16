<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;
use Models\Lottery;

final class CzechKenoNavButton extends AbstractNavigation
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com']; 
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'Czech Keno',
        'pl' => 'Czeskie Keno',
        'az' => 'Çex Keno',
        'cs' => 'České Keno',
        'da' => 'Tjekkisk Keno',
        'de' => 'Tschechisches Keno',
        'et' => 'Tšehhi Keno',
        'es' => 'Keno checo',
        'fr' => 'Keno tchèque',
        'hr' => 'Češki Keno',
        'id' => 'Keno Ceko',
        'it' => 'Keno ceco',
        'lv' => 'Čehijas Keno',
        'lt' => 'Čekiškas Keno',
        'hu' => 'Cseh Kenó',
        'mk' => 'Чешко Кено',
        'nl' => 'Tsjechische Keno',
        'pt' => 'Keno Tcheco',
        'ro' => 'Cehia Keno',
        'sq' => 'Keno Çeke',
        'sk' => 'České Keno',
        'sl' => 'Češki Keno',
        'sr' => 'Češki Keno',
        'sv' => 'Tjeckiska Keno',
        'fil' => 'Czech Keno',
        'vi' => 'Keno tiếng Séc',
        'tr' => 'Çek Keno',
        'uk' => 'Чеське кено',
        'el' => 'Τσέχικο Κίνο',
        'bg' => 'Чешко Кено',
        'ru' => 'Чешское кено',
        'ge' => 'ჩეხური კენო',
        'ar' => 'يانصيب الكينو التشيكي',
        'hi' => 'चेक केनो',
        'bn' => 'চেক কেনো',
        'th' => 'Keno เช็ก',
        'ko' => '체코 키노',
        'zh' => '捷克基诺',
	]; 
	protected const SLUG_FOR_LINK = 'play/' . Lottery::CZECH_KENO_SLUG; 

}
