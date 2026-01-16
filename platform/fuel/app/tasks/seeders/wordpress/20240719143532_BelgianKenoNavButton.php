<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;
use Models\Lottery;

final class BelgianKenoNavButton extends AbstractNavigation
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com']; 
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'Belgian Keno',
        'pl' => 'Belgijskie Keno',
        'az' => 'Belçika Keno',
        'cs' => 'Belgické Keno',
        'da' => 'Belgisk Keno',
        'de' => 'Belgisches Keno',
        'et' => 'Belgia Keno',
        'es' => 'Keno belga',
        'fr' => 'Keno belge',
        'hr' => 'Belgijski Keno',
        'id' => 'Keno Belgia',
        'it' => 'Keno belga',
        'lv' => 'Beļģijas Keno',
        'lt' => 'Belgiškas Keno',
        'hu' => 'Belga Kenó',
        'mk' => 'Белгиско Кено',
        'nl' => 'Belgische Keno',
        'pt' => 'Keno Belga',
        'ro' => 'Belgia Keno',
        'sq' => 'Keno Belge',
        'sk' => 'Belgické Keno',
        'sl' => 'Belgijski Keno',
        'sr' => 'Belgijski Keno',
        'sv' => 'Belgisk Keno',
        'fil' => 'Belgian na Keno',
        'vi' => 'Keno tiếng Bỉ',
        'tr' => 'Belçika Keno',
        'uk' => 'Бельгійське кено',
        'el' => 'Βελγικό Κίνο',
        'bg' => 'Белгийско Кено',
        'ru' => 'Бельгийское кено',
        'ge' => 'ბელგიური კენო',
        'ar' => 'يانصيب الكينو البلجيكي',
        'hi' => 'बेल्जियम केनो',
        'bn' => 'বেলজিয়ান কেনো',
        'th' => 'Keno เบลเยียม',
        'ko' => '벨기에 키노',
        'zh' => '比利时基诺',
        'fa' => 'کینو بلژیکی',
        'fi' => 'Belgialainen Keno',
        'ja' => 'ベルギーキノ',
        'he' => 'Keno בלגי',
	]; 
	protected const SLUG_FOR_LINK = 'play/' . Lottery::BELGIAN_KENO_SLUG; 
}
