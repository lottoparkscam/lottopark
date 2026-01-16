<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;

final class TermsAndConditionsToCasinoFooterNavButton extends AbstractNavigation
{
    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];
    protected const SLUG_FOR_LINK = 'general-terms-and-conditions';
    protected const MENU = 'casino-footer';
    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'Terms & Conditions',
        'pl' => 'Warunki użytkowania',
        'az' => 'Şərtlər və Qaydalar',
        'cs' => 'Podmínky použití',
        'da' => 'Betingelser for brug',
        'de' => 'Terms & Conditions',
        'et' => 'Kasutustingimused',
        'es' => 'Condiciones de uso',
        'fr' => 'Conditions d’utilisations',
        'hr' => 'Uvjeti korištenja',
        'id' => 'Syarat Penggunaan',
        'it' => 'Termini di utilizzo',
        'lv' => 'Lietošanas noteikumi',
        'lt' => 'Naudojimosi taisyklės',
        'hu' => 'Felhasználási Feltételek',
        'mk' => 'Услови за користење',
        'nl' => 'Gebruiksvoorwaarden',
        'pt' => 'Termos de Utilização',
        'ro' => 'Termeni de utilizare',
        'sq' => 'Termat e përdorimit',
        'sk' => 'Podmienky používania',
        'sl' => 'Pogoji uporabe',
        'sr' => 'Uslovi korišćenja',
        'sv' => 'Användarvillkor',
        'fil' => 'Tuntunin ng Paggamit',
        'vi' => 'Điều khoản sử dụng',
        'tr' => 'Kullanım Şartları',
        'uk' => 'Умови користування',
        'el' => 'Όροι Χρήσης',
        'bg' => 'Условия за ползване',
        'ru' => 'Условия пользования',
        'ge' => 'მოხმარების პირობები',
        'ar' => 'تعليمات الاستخدام',
        'hi' => 'उपयोग की शर्तें',
        'bn' => 'ব্যবহারের শর্তাবলী',
        'th' => 'Terms & Conditions',
        'ko' => 'Terms & Conditions',
        'zh' => '使用条款',
        'ka' => 'Terms & Conditions',
        'fa' => 'Terms & Conditions',
        'ja' => 'Terms & Conditions',

    ];
}
