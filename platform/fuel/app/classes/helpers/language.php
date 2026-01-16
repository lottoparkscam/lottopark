<?php

use Fuel\Core\Input;

class LanguageHelper
{
    public const DEFAULT_LANGUAGE = 'en';

    public const LOCALES = [
        'ar_SA', 'az_AZ', 'bg_BG', 'bn_BD', 'cs_CZ', 'da_DK', 'de_DE', 'el_GR', 'en_GB', 'es_ES',
        'et_EE', 'fa_IR', 'tl_PH', 'fr_FR', 'hi_IN', 'hr_HR', 'hu_HU', 'id_ID', 'it_IT', 'ja_JP',
        'ka_GE', 'ko_KR', 'lt_LT', 'lv_LV', 'mk_MK', 'nl_NL', 'pl_PL', 'pt_PT', 'ro_RO', 'ru_RU',
        'sk_SK', 'sl_SI', 'sq_AL', 'sr_RS', 'sv_SE', 'th_TH', 'tr_TR', 'uk_UA', 'vi_VN', 'zh_CN',
        'pt_BR', 'fi_FI', 'he_IL',
    ];

    public const LANGUAGES = [
        'ar', 'az', 'bg', 'bn', 'cs', 'da', 'de', 'el', 'en', 'es',
        'et', 'fa', 'tl', 'fr', 'hi', 'hr', 'hu', 'id', 'it', 'ja',
        'ka', 'ko', 'lt', 'lv', 'mk', 'nl', 'pl', 'pt-pt', 'ro', 'ru',
        'sk', 'sl', 'sq', 'sr', 'sv', 'th', 'tr', 'uk', 'vi', 'zh',
        'pt', 'fi', 'he',
    ];

    /** This function is available without wordpress */
    public static function getLanguageShortcodeFromUrl(): string
    {
        $uri = Input::server('REQUEST_URI');
        if (empty($uri)) {
            return self::DEFAULT_LANGUAGE;
        }

        $uriArray = explode('/', $uri);
        $language = !empty($uriArray[1]) ? $uriArray[1] : self::DEFAULT_LANGUAGE;

        if ($language === self::DEFAULT_LANGUAGE) {
            return $language;
        }

        return self::isLanguageSupported($language) ? $language : self::DEFAULT_LANGUAGE;
    }

    public static function getLanguageUri(): string
    {
        $language = self::getLanguageShortcodeFromUrl();

        if ($language === self::DEFAULT_LANGUAGE) {
            return '/';
        }

        return '/' . $language;
    }

    public static function isLanguageSupported(string $language): bool
    {
        return in_array(strtolower($language), self::LANGUAGES);
    }

    public static function getLanguageCodeFromLocale(string $locale): string
    {
        $shortcode = self::DEFAULT_LANGUAGE;
        if (substr($locale, 2, 1) === '_') {
            $shortcode = substr($locale, 0, 2);
        }

        // E.g. 'fil'
        if (substr($locale, 3, 1) === '_') {
            $shortcode = substr($locale, 0, 3);
        }

        /**
         * pt_PT = pt-pt
         * pt_BR = pt
         */
        $isPortuguese = str_starts_with($locale, 'pt_PT');
        if ($isPortuguese) {
            $shortcode = 'pt-pt';
        }

        return $shortcode;
    }

    public static function getOnlyCodeAndLocale(string $fullLanguageDefinition): string
    {
        $dotPosition = strpos($fullLanguageDefinition, '.') ?: strlen($fullLanguageDefinition);
        $atPosition = strpos($fullLanguageDefinition, '@') ?: strlen($fullLanguageDefinition);
        $languageDefinitionBeforeDot = substr($fullLanguageDefinition, 0, $dotPosition);
        return substr($languageDefinitionBeforeDot, 0, $atPosition);
    }
}
