<?php

namespace Helpers;

use Config;
use Container;
use LanguageHelper;
use Lotto_Helper;
use Fuel\Core\Input;
use Helper_Route;
use Services\Logs\FileLoggerService;

final class UrlHelper
{
    /**
     * Wrapper for urlencode over primitive array.
     *
     * @param array $parameters
     * @return void
     */
    public static function urlencode_array(array &$parameters): void
    {
        foreach ($parameters as &$parameter) {
            $parameter = urlencode($parameter);
        }
    }

    /**
     * @param $uri
     *
     * @return string
     */
    public static function shorten_uri($uri)
    {
        return substr($uri, 0, 15);
    }

    /**
     * This is basically copy of wordpress esc_url function but
     * with some modifications to let it work outside of wordpress
     *
     * @param string $url
     *
     * @return string|string[]
     */
    public static function esc_url(string $url)
    {
        if ('' === $url) {
            return $url;
        }

        $url = str_replace(' ', '%20', ltrim($url));
        $url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\[\]\\x80-\\xff]|i', '', $url);

        if ('' === $url) {
            return $url;
        }

        if (0 !== stripos($url, 'mailto:')) {
            $strip = ['%0d', '%0a', '%0D', '%0A'];

            $count = 1;
            while ($count) {
                $url = str_replace($strip, '', $url, $count);
            }
        }

        $url = str_replace(';//', '://', $url);

        /*
         * If the URL doesn't appear to contain a scheme, we presume
         * it needs http:// prepended (unless it's a relative link
         * starting with /, # or ?, or a PHP file).
         */
        if (
            strpos($url, ':') === false && !in_array($url[0], ['/', '#', '?'], true) &&
            !preg_match('/^[a-z0-9-]+?\.php/i', $url)
        ) {
            $url = 'http://' . $url;
        }

        //        $url = wp_kses_normalize_entities($url);
        $url = str_replace('&', '&amp;', $url);

        // Change back the allowed entities in our list of allowed entities.
        $url = preg_replace_callback(
            '/&amp;#(0*[0-9]{1,7});/',
            function ($matches) {
                if (empty($matches[1])) {
                    return '';
                }

                $i = $matches[1];
                if (valid_unicode($i)) {
                    $i = str_pad(ltrim($i, '0'), 3, '0', STR_PAD_LEFT);
                    $i = "&#$i;";
                } else {
                    $i = "&amp;#$i;";
                }

                return $i;
            },
            $url
        );
        $url = preg_replace_callback(
            '/&amp;#[Xx](0*[0-9A-Fa-f]{1,6});/',
            function ($matches) {
                if (empty($matches[1])) {
                    return '';
                }

                $hexchars = $matches[1];
                return (!valid_unicode(hexdec($hexchars))) ? "&amp;#x$hexchars;" : '&#x' . ltrim($hexchars, '0') . ';';
            },
            $url
        );
        $url = str_replace('&amp;', '&#038;', $url);
        $url = str_replace("'", '&#039;', $url);

        if ((false !== strpos($url, '[')) || (false !== strpos($url, ']'))) {
            $to_unset = [];
            $url      = strval($url);

            if ('//' === substr($url, 0, 2)) {
                $to_unset[] = 'scheme';
                $url        = 'placeholder:' . $url;
            } elseif ('/' === substr($url, 0, 1)) {
                $to_unset[] = 'scheme';
                $to_unset[] = 'host';
                $url        = 'placeholder://placeholder' . $url;
            }

            $parsed = parse_url($url);

            if (false !== $parsed) {
                // Remove the placeholder values.
                foreach ($to_unset as $key) {
                    unset($parsed[$key]);
                }
            }

            $front  = '';

            if (isset($parsed['scheme'])) {
                $front .= $parsed['scheme'] . '://';
            } elseif ('/' === $url[0]) {
                $front .= '//';
            }

            if (isset($parsed['user'])) {
                $front .= $parsed['user'];
            }

            if (isset($parsed['pass'])) {
                $front .= ':' . $parsed['pass'];
            }

            if (isset($parsed['user']) || isset($parsed['pass'])) {
                $front .= '@';
            }

            if (isset($parsed['host'])) {
                $front .= $parsed['host'];
            }

            if (isset($parsed['port'])) {
                $front .= ':' . $parsed['port'];
            }

            $end_dirty = str_replace($front, '', $url);
            $end_clean = str_replace(['[', ']'], ['%5B', '%5D'], $end_dirty);
            $url       = str_replace($end_dirty, $end_clean, $url);
        }

        self::add_ending_slash($url);

        return $url;
    }

    public static function add_ending_slash(string &$url): void
    {
        $data = parse_url($url);

        $last_char = substr($url, -1);
        $path = $data['path'] ?? '';

        $has_slash = $last_char === '/';
        $has_extension = strpos($path, '.') !== false;
        $has_query_params = isset($data['query']);

        if ($has_slash || $has_extension) {
            return;
        }

        if ($has_query_params) {
            $chunks = explode('?', $url);
            [$before, $after] = $chunks;
            self::add_ending_slash($before);
            $url = $before . '?' . $after;
            return;
        }

        $url .= '/';
    }

    /**
     * Fluent wrapper for add_ending_slash.
     */
    public static function addEndingSlash(string $url): string
    {
        self::add_ending_slash($url);
        return $url;
    }

    /**
     * This function returns the whole url with params
     * example: https://lottopark.loc/casino-play/?game_uuid=00346d451fe046d2302e76409cbf6e5bc386a173
     */
    public static function getCurrentUrlWithParams(bool $addErrorIfEmpty = true): string
    {
        $url = rtrim(Input::server('HTTP_HOST'), "/") . Input::server('REQUEST_URI');
        $url = self::changeAbsoluteUrlToCasinoUrl($url);

        if ($addErrorIfEmpty && empty($url)) {
            $fileLoggerService = Container::get(FileLoggerService::class);
            $fileLoggerService->error('Cannot find current url with params. Received empty $currentUrl.');
        }

        if (!empty($url) && !str_starts_with($url, 'https')) {
            return 'https://' . $url;
        }

        return $url ?? '';
    }

    /** Use only in wordpress */
    public static function redirectToHomepage(bool $withExit = true): void
    {
        header('Location: ' . self::changeAbsoluteUrlToCasinoUrl(lotto_platform_home_url()));
        if ($withExit) {
            exit;
        }
    }

    /**
     * Note: use where necessary; sometimes we have to check it before wp init
     * If possible use is_front_page()
     */
    public static function isHomepage(string $languageCode = ''): bool
    {
        $language = function_exists('getLanguage') ? getLanguage() : $languageCode;
        $homeUrl = self::getHomeUrlWithoutLanguage();
        $currentUrl = self::getCurrentUrlWithParams();
        $currentUrl = self::changeAbsoluteUrlToCasinoUrl($currentUrl);

        $currentSiteWithParams = str_replace($homeUrl, '', $currentUrl);
        $currentSiteWithoutLanguage = str_replace('/', '', $currentSiteWithParams);
        $finalUrlPath = str_replace($language, '', $currentSiteWithoutLanguage);

        $hasParams = str_contains($currentSiteWithoutLanguage, '?');

        if ($hasParams) {
            $finalUrlPath = StringHelper::removeLastChunkBySeparator($finalUrlPath, '?');
        }

        if (empty($finalUrlPath)) {
            return true;
        }

        return false;
    }

    /** Use only in wordpress */
    public static function redirectToLoginPage(bool $withExit = true): void
    {
        header('Location: ' . lotto_platform_get_permalink_by_slug('login'));
        if ($withExit) {
            exit;
        }
    }

    /** Use only in wordpress */
    public static function redirectToSignUpPage(bool $withExit = true): void
    {
        header('Location: ' . lotto_platform_get_permalink_by_slug('signup'));
        if ($withExit) {
            exit;
        }
    }

    /**
     * IMPORTANT: url must have protocol in it. use only for e.g. https://lovcasino.com/ not lovcasino.com (http_host)
     */
    public static function removeCasinoPrefixFromAbsoluteUrl(string $url): string
    {
        /** in format e.g. casino|instant|games */
        $casinoPrefixes = self::getCasinoPrefixesAsRegex();
        return preg_replace("/\/($casinoPrefixes)\./", '/', $url);
    }

    /** If is casino this function change e.g. https://lottopark.com/test to https://casino.lottopark.com/test */
    public static function changeAbsoluteUrlToCasinoUrl(string $url, bool $forceCasino = false): string
    {
        $isNotCasino = true;
        if (defined('IS_CASINO')) {
            $isNotCasino = !IS_CASINO;
        }

        if ($isNotCasino && !$forceCasino) {
            return $url;
        }

        $whitelabelDomain = Lotto_Helper::getWhitelabelDomainFromUrl();
        $url = str_replace('www.', '', $url); // NOTE: due to cloudflare we have www.whitalabel with casino.whitelabel (without www)

        $doNotStartFromWhitelabelDomain = !str_contains($url, "https://$whitelabelDomain") &&
            !str_contains($url, "http://$whitelabelDomain");
        if ($doNotStartFromWhitelabelDomain) {
            return $url;
        }

        if (str_contains($url, "https://$whitelabelDomain")) {
            $httpCount = 8;
        }

        if (str_contains($url, "http://$whitelabelDomain")) {
            $httpCount = 7;
        }

        $whitelabelDomainCount = strlen($whitelabelDomain);
        $prefixCount = $httpCount + $whitelabelDomainCount;
        $relativeUrl = substr($url, $prefixCount);
        $casinoPrefix = self::getCasinoPrefixForWhitelabel($whitelabelDomain);
        return "https://{$casinoPrefix}.{$whitelabelDomain}{$relativeUrl}";
    }

    /**
     * DO NOT USE HERE AUTOLOADED CLASS BECAUSE WE USE THIS FUNCTION IN WORDPRESS_IN_FUEL.PHP
     * We need to use raw $_SERVER['HTTP_HOST'] here because we use this function in wordpress_in_fuel.php
     */
    public static function isCasino(): bool
    {
        $casinoPrefixes = self::getCasinoPrefixesAsRegex();
        return preg_match("/^($casinoPrefixes)./", $_SERVER['HTTP_HOST'] ?? '');
    }

    public static function parseUrlQueryStringAsArray(string $queryString): array
    {
        $parsedParameters = [];
        parse_str($queryString, $parsedParameters);
        return $parsedParameters;
    }

    public static function addWwwPrefixIfNeeded(string $url): string
    {
        $casinoPrefixForLottohoy = self::getCasinoPrefixForWhitelabel('lottohoy.com');
        $urlDoesNotContainWwwPrefix = !str_contains($url, 'www.lottohoy.com') &&
            !str_contains($url, "$casinoPrefixForLottohoy.lottohoy.com");
        if ($urlDoesNotContainWwwPrefix) {
            $url = str_replace('lottohoy.com', 'www.lottohoy.com', $url);
        }

        return $url;
    }

    /** Without trailing slash */
    public static function getHomeUrlWithoutLanguage(string $path = '', string $domain = ''): string
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        if (empty($domain)) {
            $domain = Input::server('HTTP_HOST');
        }

        if (empty($domain)) {
            $whitelabel = Container::get('whitelabel');
            $domain = $whitelabel->domain ?? '';
        }

        if (empty($domain)) {
            $fileLoggerService->error(
                "Home url will be incorrect because domain does not exist. Path: $path"
            );
            return '';
        }

        $url = 'https://' . $domain;
        $url = self::changeAbsoluteUrlToCasinoUrl($url . $path);
        $url = self::addWwwPrefixIfNeeded($url);

        $prefixesToRemove = [
            'api.',
            'aff.',
            'manager.',
            'empire.',
        ];


        return str_replace($prefixesToRemove, '', $url);
    }

    public static function getCasinoHomeUrl(): string
    {
        return self::changeAbsoluteUrlToCasinoUrl(self::getHomeUrlWithoutLanguage(), true);
    }

    public static function changeUrlsToCasino(string $body): string
    {
        /**
         * For default language $languageUri variable should return empty URI '/'
         */
        $languageUri = LanguageHelper::getLanguageUri();

        $whitelabelDomain = Lotto_Helper::getWhitelabelDomainFromUrl();
        $casinoPrefix = self::getCasinoPrefixForWhitelabel($whitelabelDomain);

        $newCasinoUrl = "\"https://{$casinoPrefix}.{$whitelabelDomain}$languageUri";
        $newCasinoUrl = rtrim($newCasinoUrl, '/');
        $newCasinoUrl = "$newCasinoUrl/\"";

        if (!function_exists('lotto_platform_get_post_id_by_slug')) {
            $currentCasinoPrefix = UrlHelper::getCurrentCasinoPrefix();
            return preg_replace("/\"https:\/\/(www.)?$whitelabelDomain.*$currentCasinoPrefix.*\"/", $newCasinoUrl, $body);
        }

        $translatedCasinoPageId = RouteHelper::getCasinoHomePageId(domain: $whitelabelDomain) ?? null;

        $isWhitelabelWithoutCasino = $translatedCasinoPageId === null;
        if ($isWhitelabelWithoutCasino) {
            return $body;
        }

        $translatedCasinoPage = get_post($translatedCasinoPageId);
        $translatedCasinoPageSlug = $translatedCasinoPage->post_name;

        return preg_replace("/\"https:\/\/(www.)?$whitelabelDomain.*$translatedCasinoPageSlug.*\"/", $newCasinoUrl, $body);
    }

    /**
     * DO NOT USE HERE AUTOLOADED CLASS BECAUSE WE USE THIS FUNCTION IN WORDPRESS_IN_FUEL.PHP
     * This function is used in wordpress_in_fuel.php, so you cannot use Input::server() here
     * For links like 'www.lottohoy.com' it returns www prefix because it is correct subdomain
     */
    public static function getCurrentSubdomain(): ?string
    {
        $parts = explode('.', $_SERVER['HTTP_HOST'] ?? '');
        $isSubdomain = count($parts) > 2;
        return $isSubdomain ? $parts[0] : null;
    }

    /**
     * DO NOT USE HERE AUTOLOADED CLASS BECAUSE WE USE THIS FUNCTION IN WORDPRESS_IN_FUEL.PHP
     * We use global here because this function is also used in wordpress_in_fuel.
     * There isn't autoloader, DB, config and other things, so we import casinoConfig by require_once
     */
    public static function getCasinoPrefixes(): array
    {
        global $casinoConfig;

        $prefixesMap = array_merge($casinoConfig['prefixesMap'] ?? [], ['casino']);
        return array_values(array_unique($prefixesMap));
    }

    /**
     * DO NOT USE HERE AUTOLOADED CLASS BECAUSE WE USE THIS FUNCTION IN WORDPRESS_IN_FUEL.PHP
     */
    public static function getCasinoPrefixesAsRegex(): string
    {
        return implode('|', self::getCasinoPrefixes());
    }

    /**
     * DO NOT USE HERE AUTOLOADED CLASS BECAUSE WE USE THIS FUNCTION IN WORDPRESS_IN_FUEL.PHP
     */
    public static function getCurrentCasinoPrefix(): ?string
    {
        $currentSubdomain = self::getCurrentSubdomain();
        $isValidCasinoPrefix = in_array($currentSubdomain, self::getCasinoPrefixes());
        return $isValidCasinoPrefix ? $currentSubdomain : 'casino';
    }

    /**
     * DO NOT USE HERE AUTOLOADED CLASS BECAUSE WE USE THIS FUNCTION IN WORDPRESS_IN_FUEL.PHP
     * We use global here because this function is also used in wordpress_in_fuel.
     * There isn't autoloader, DB, config and other things, so we import casinoConfig by require_once
     */
    public static function getCasinoPrefixForWhitelabel(string $domain): string
    {
        global $casinoConfig;

        $prefixesMap = $casinoConfig['prefixesMap'] ?? [];
        return $prefixesMap[$domain] ?? 'casino';
    }

    public static function getCurrentUrlWithoutParams(): string
    {
        $httpsSuffix = !empty(Input::server('HTTPS')) ? 's' : '';
        $httpHost = Input::server('HTTP_HOST');
        $requestUri = Input::server('REQUEST_URI');
        return "http$httpsSuffix://$httpHost$requestUri";
    }

    public static function getSignUpUrlInDefaultLanguage(): string
    {
       return UrlHelper::getHomeUrlWithoutLanguage() . '/auth/signup';
    }

    public static function checkAndChangeWhitelabelDomainInUrl(string $domain, string $url): string
    {
        $parsedUrl = parse_url($url);

        if (!empty($parsedUrl['host']) && $parsedUrl['host'] !== $domain) {
            $url = str_replace($parsedUrl['host'], $domain, $url);
        }

        return $url;
    }
}
