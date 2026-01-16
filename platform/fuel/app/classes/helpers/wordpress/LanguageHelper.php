<?php

namespace Helpers\Wordpress;

use Abstracts\Controllers\Internal\AbstractPublicController;
use Container;
use Fuel\Core\Cache;
use Fuel\Core\CacheNotFoundException;
use Fuel\Core\Config;
use Fuel\Core\Input;
use Helper_Route;
use Helpers\UrlHelper;
use Lotto_Settings;
use Models\Whitelabel;
use Repositories\WhitelabelLanguageRepository;
use ResourceBundle;
use Services\Logs\FileLoggerService;
use LanguageHelper as BasicLanguageHelper;

/** Use these functions only in Wordpress */
class LanguageHelper
{
    public const DEFAULT_LANGUAGE_CODE = 'en_GB';
    public const DEFAULT_LANGUAGE_SHORTCODE = 'en';

    public static function getCurrentWhitelabelLanguage(): ?array
    {
        global $currentWhitelabelLanguageCache;
        if (!empty($currentWhitelabelLanguageCache)) {
            return $currentWhitelabelLanguageCache;
        }

        global $sitepress;
        if (!$sitepress) {
            $languageFromUrl = BasicLanguageHelper::getLanguageShortcodeFromUrl();
        } else {
            $currentUrlWithoutParams = UrlHelper::getCurrentUrlWithoutParams();
            $languageFromUrl = $sitepress->get_language_from_url($currentUrlWithoutParams);
        }

        if (defined(AbstractPublicController::API_LANGUAGE_SHORTCODE)) {
            $languageFromUrl = API_LANGUAGE_SHORTCODE;
        }

        if (defined('WP_CLI')) {
            return null;
        }

        $whitelabelLanguageRepository = Container::get(WhitelabelLanguageRepository::class);
        $whitelabelLanguages = $whitelabelLanguageRepository->getAll();
        if (empty($whitelabelLanguages)) {
            $logger = Container::get(FileLoggerService::class);
            /** @var Whitelabel $whitelabel */
            $domain = Container::get('domain') ?? '';
            // There is no need to look for records in whtielabel_langauge table for whitelotto.com domain
            // We exclude warning for domains like admin.whitelotto.com, empire, manager etc.
            $isNotWhitelotto = !str_contains($domain, 'whitelotto');
            if ($isNotWhitelotto) {
                $logger->warning("There are not any records in whitelabel_languages table for whitelabel domain: $domain");
            }
            return null;
        }

        $defaultWhitelabelLanguageShortcode = substr($whitelabelLanguages[0]['code'], 0, 2);

        $whitelabelLanguagesPerShortCode = [];
        foreach ($whitelabelLanguages as $whitelabelLanguage) {
            $languageCode = $whitelabelLanguage['code'] ?? self::DEFAULT_LANGUAGE_CODE;
            $shortcode = BasicLanguageHelper::getLanguageCodeFromLocale($languageCode);
            $whitelabelLanguagesPerShortCode[$shortcode] = $whitelabelLanguage;
        }

        $isLanguageFromUrlInWhitelabelLanguages = in_array(
            $languageFromUrl,
            array_keys($whitelabelLanguagesPerShortCode)
        );
        if ($isLanguageFromUrlInWhitelabelLanguages) {
            $currentWhitelabelLanguage = $whitelabelLanguagesPerShortCode[$languageFromUrl];
        } else {
            $currentWhitelabelLanguage = $whitelabelLanguagesPerShortCode[$defaultWhitelabelLanguageShortcode];
        }

        $uri =  UrlHelper::shorten_uri(Input::server('REQUEST_URI'));
        switch ($uri) {
            case Helper_Route::ORDER_CONFIRM:
                $currentWhitelabelLanguage = $whitelabelLanguagesPerShortCode[$defaultWhitelabelLanguageShortcode];
                break;
            default:
                break;
        }

        $currentWhitelabelLanguageCache = $currentWhitelabelLanguage;

        return $currentWhitelabelLanguage;
    }

    public static function getDefaultWhitelabelLanguageShortcode(): string
    {
        global $defaultWhitelabelLanguageShortcodeCache;
        if (!empty($defaultWhitelabelLanguageShortcodeCache)) {
            return $defaultWhitelabelLanguageShortcodeCache;
        }

        $whitelabelLanguageRepository = Container::get(WhitelabelLanguageRepository::class);
        $whitelabelLanguages = $whitelabelLanguageRepository->getAll();
        if (empty($whitelabelLanguages)) {
            return self::DEFAULT_LANGUAGE_SHORTCODE;
        }

        $defaultWhitelabelLanguageShortcode = substr($whitelabelLanguages[0]['code'], 0, 2);
        $defaultWhitelabelLanguageShortcodeCache = $defaultWhitelabelLanguageShortcode;
        return $defaultWhitelabelLanguageShortcode;
    }

    public static function getCurrentLanguageShortcode(): string
    {
        $currentWhitelabelLanguage = self::getCurrentWhitelabelLanguage();
        $languageCode = $currentWhitelabelLanguage['code'] ?? self::DEFAULT_LANGUAGE_CODE;
        return BasicLanguageHelper::getLanguageCodeFromLocale($languageCode);
    }

    public static function getShortcodeLanguage(string $languageCode): string
    {
        return BasicLanguageHelper::getLanguageCodeFromLocale($languageCode);
    }

    /**
     * @param string $displayLocale decides in which language we should receive language name.
     * If this parameter is not provided it will detect automatically.
     */
    public static function getLanguageNameByLocale(string $locale, string $displayLocale = ''): string
    {
        if (empty($displayLocale)) {
            $displayLocale = self::getCurrentLanguageShortcode();
        }

        $cacheKey = 'languages.names.' . $locale . '_' . $displayLocale;
        try {
            return Cache::get($cacheKey);
        } catch (CacheNotFoundException $e) {
            $validLocales = ResourceBundle::getLocales('');
            $languageCode = explode('_', $locale)[0];

            $isNotValidLocale = !array_search($languageCode, $validLocales);
            if ($isNotValidLocale) {
                Cache::set($cacheKey, $locale);
                return $locale;
            }

            $regexp = preg_quote($languageCode, '~');
            $languageCountriesCount = preg_grep('~' . $regexp . '~', $validLocales);
            $isLanguageUsedInOneCountry = count($languageCountriesCount) <= 2; // all locales can be passed like pl instead pl_PL
            if ($isLanguageUsedInOneCountry) {
                $languageName = ucfirst(locale_get_display_language($locale, $displayLocale));
                Cache::set($cacheKey, $languageName);
                return $languageName;
            }

            if ($languageCode === 'en') {
                $locale = 'en';
            }

            $languageName = ucfirst(locale_get_display_name($locale, $displayLocale));
            if (empty($languageName)) {
                Cache::set($cacheKey, $locale);
                return $locale;
            }

            Cache::set($cacheKey, $languageName);
            return $languageName;
        }
    }

    /** Use only in wordpress */
    public static function configureLocale(): void
    {
        $fileLoggerService = Container::get(FileLoggerService::class);
        $currentWhitelabelLanguage = LanguageHelper::getCurrentWhitelabelLanguage();

        // wp cli (at least whitelabel installation) might have empty wlanguage and ICL const. NOTE: ICL const is incompatible with wlangueage e.g. 'en' is not valid language
        $currentLanguageCode = $currentWhitelabelLanguage['code'] ?? LanguageHelper::DEFAULT_LANGUAGE_CODE;
        $locale = $currentLanguageCode;
        $fullLocale = $locale . ".utf8";
        if ($locale == 'sr_RS') {
            $fullLocale = $locale . "@latin";
            $locale = 'sr_Latn_RS';
        }

        Lotto_Settings::getInstance()->set('locale_default', $fullLocale);
        putenv('LC_ALL=' . $fullLocale);
        $newCurrentLocale = setlocale(LC_ALL, $fullLocale);
        if ($newCurrentLocale === false) {
            $fileLoggerService->error("unable to set locale $fullLocale");
        }

        // Register domain translations for gettext
        bindtextdomain('lotto-platform', WP_PLUGIN_DIR . '/lotto-platform/languages/gettext');
        textdomain('lotto-platform');

        // workaround for PHP bug https://bugs.php.net/bug.php?id=18556
        if (in_array($locale, array("tr_TR", "az_AZ"))) {
            putenv('LC_CTYPE=en_US.utf8');
            setlocale(LC_CTYPE, 'en_US.utf8');
        }

        // Some locales are using "," in floats and this is very bad when you are parsing variables
        // from float to string and again to float
        // Try to force using "." dots in floats
        setlocale(LC_NUMERIC, 'en_US.utf8');

        $languageShortcode = substr(
            $currentLanguageCode,
            0,
            strpos($currentLanguageCode, '_')
        );
        $isLanguageShortcodeNotCorrect = $languageShortcode == false;
        if ($isLanguageShortcodeNotCorrect) {
            $fileLoggerService->error('Unable to find the first occurrence of a substring in a string.');
        }

        Config::set('language', $languageShortcode);
    }
}
