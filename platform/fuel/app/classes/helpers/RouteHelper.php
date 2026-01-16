<?php

namespace Helpers;

use Helper_Route;

final class RouteHelper
{
    /**
     * @param string $slug
     * @param string|null $domain optional - if set, available casino mappings will be used
     * @return string
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public static function getPermalinkBySlug(string $slug, ?string $domain = null): string
    {
        if ($domain !== null) {
            $slug = self::getValidCasinoSlugForDomain($slug, $domain);
        }

        return lotto_platform_get_permalink_by_slug($slug);
    }

    public static function getPostIdBySlug(string $slug, string $type = 'page', ?string $languageCode = null, ?string $domain = null): ?int
    {
        if ($domain !== null) {
            $slug = self::getValidCasinoSlugForDomain($slug, $domain);
        }

        return lotto_platform_get_post_id_by_slug($slug, $type, $languageCode);
    }

    public static function getCasinoHomePageId($languageCode = null, ?string $domain = null): ?int
    {
        return RouteHelper::getPostIdBySlug(Helper_Route::CASINO_HOMEPAGE, 'page', $languageCode, $domain);
    }

    public static function getCasinoPlayLink(?string $domain = null): string
    {
        return RouteHelper::getPermalinkBySlug(Helper_Route::CASINO_PLAY, $domain);
    }

    public static function getCasinoLobbyLink(?string $domain = null): string
    {
        return RouteHelper::getPermalinkBySlug(Helper_Route::CASINO_LOBBY, $domain);
    }

    public static function getValidCasinoSlugForDomain(string $slug, string $domain): string
    {
        $map = self::getCasinoSlugMap($domain);

        return $map[$slug] ?? $slug;
    }

    /**
     * DO NOT USE HERE AUTOLOADED CLASS BECAUSE WE USE THIS FUNCTION IN WORDPRESS_IN_FUEL.PHP
     * We use global here because this function is also used in wordpress_in_fuel.
     * There isn't autoloader, DB, config and other things, so we import casinoConfig by require_once
     */
    public static function getCasinoSlugMap(string $domain): array
    {
        global $casinoConfig;

        $slugMap = $casinoConfig['slugMap'] ?? [];
        return $slugMap[$domain] ?? [];
    }
}