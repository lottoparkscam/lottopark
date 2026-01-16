<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;
use Helper_Route;

final class PrivacyPolicyToCasinoFooterNavButton extends AbstractNavigation
{
    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];
    protected const SLUG_FOR_LINK = Helper_Route::CASINO_PRIVACY_POLICY;
    protected const MENU = 'casino-footer';
    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'Privacy Policy',
        'pl' => 'Polityka prywatności',
        'az' => 'Gizlilik Siyasəti',
        'cs' => 'Zásady ochrany osobních údajů',
        'da' => 'Privatlivspolitik',
        'de' => 'Privacy Policy',
        'et' => 'Privaatsuspoliitika',
        'es' => 'Política de privacidad',
        'fr' => 'Politique de confidentialité',
        'hr' => 'Polica privatnosti',
        'id' => 'Kebijakan Pribadi',
        'it' => 'Informativa sulla Privacy',
        'lv' => 'Privātuma politika',
        'lt' => 'Privatumo politika',
        'hu' => 'Adatvédelmi irányelvek',
        'mk' => 'Политика за приватност',
        'nl' => 'Privacybeleid',
        'pt' => 'Política de Privacidade',
        'ro' => 'Politica de confidențialitate',
        'sq' => 'Politika e Privatësisë',
        'sk' => 'Zásady ochrany osobných údajov',
        'sl' => 'Politika zasebnosti',
        'sr' => 'Politika privatnosti',
        'sv' => 'Sekretesspolicy',
        'fil' => 'Patakaran sa Pagkapribado',
        'vi' => 'Chính sách riêng tư',
        'tr' => 'Gizlilik Politikası',
        'uk' => 'Політика конфіденційності',
        'el' => 'Πολιτική Απορρήτου',
        'bg' => 'Декларация за поверителност',
        'ru' => 'Политика конфиденциальности',
        'ge' => 'კონფიდენციალურობა',
        'ar' => 'سياسة الخصوصية',
        'hi' => 'गोपनीयता नीति',
        'bn' => 'গোপনীয়তা নীতি',
        'th' => 'Policy Privacy',
        'ko' => 'Policy Privacy',
        'zh' => '隐私政策',
        'ka' => 'Policy Privacy',
        'fa' => 'Policy Privacy',
        'ja' => 'Policy Privacy',
    ];
}
