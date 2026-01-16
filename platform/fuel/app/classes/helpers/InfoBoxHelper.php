<?php

namespace Helpers;

use Container;
use Fuel\Core\CacheNotFoundException;
use Helpers_Time;
use Lotto_Helper;
use Lotto_View;
use Modules\View\ViewHelper;
use Repositories\LotteryRepository;
use Services\CacheService;
use Throwable;
use Services\Logs\FileLoggerService;

class InfoBoxHelper
{
    public static function generateHtml(string $title = ''): string
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $infoBoxesHtml = '';

        try {
            $whitelabel = Container::get('whitelabel');
        } catch (Throwable $exception) {
            return '';
        }

        if (IS_CASINO) {
            return '';
        }

        $whitelabelName = $whitelabel->name;
        /** @var CacheService $cacheService */
        $cacheService = Container::get(CacheService::class);
        $currentCountryCode = ICL_LANGUAGE_CODE ?? 'en';
        $englishMenuCacheKey = 'primaryMenuen';
        $englishMenuSlug = 'primary-menu';
        $menuId = 0;
        $primaryMenuItemsCacheKey = 'primaryMenuItems' . $currentCountryCode;
        try {
            $primaryMenuItems = $cacheService->getCacheForWhitelabelByDomain($primaryMenuItemsCacheKey);
        } catch (CacheNotFoundException $exception) {
            $cacheKey = 'primaryMenu' . $currentCountryCode;
            try {
                $menu = $cacheService->getCacheForWhitelabelByDomain($cacheKey);
                $menuId = $menu->term_id;
            } catch (CacheNotFoundException $exception) {
                try {
                    $englishMenu = $cacheService->getCacheForWhitelabelByDomain($englishMenuCacheKey);
                } catch (CacheNotFoundException $exception) {
                    // we always get english menu and then searching for translation in specific language
                    // whitelabels can have different menu slugs depended on language
                    $englishMenu = wp_get_nav_menu_object($englishMenuSlug);
                    // Check for alternative english menu slug - primary-menu-en
                    if (!$englishMenu) {
                        $englishMenuSlug .= '-en';
                        $englishMenu = wp_get_nav_menu_object($englishMenuSlug);
                    }
                    if (!$englishMenu) {
                        $fileLoggerService->error(
                            "$whitelabelName deleted english menu Detailed message: " . $exception->getMessage()
                        );
                        return '';
                    }
                    $menuId = $englishMenu->term_id;
                    $cacheService->setCacheForWhitelabelByDomain($englishMenuCacheKey, $englishMenu, Helpers_Time::DAY_IN_SECONDS);
                }
                $isNotEnglish = $currentCountryCode !== 'en';
                if ($isNotEnglish) {
                    $menuId = apply_filters(
                        "wpml_object_id",
                        $englishMenu->term_id,
                        'nav_menu',
                        false,
                        $currentCountryCode,
                    );
                    if (!$menuId) {
                        $fileLoggerService->error(
                            "$whitelabelName deleted menu for language: $currentCountryCode. Detailed message: " . $exception->getMessage()
                        );
                        $menuId = $englishMenu->term_id;
                    }
                    $translatedMenu = wp_get_nav_menu_object($menuId);
                    $cacheService->setCacheForWhitelabelByDomain($cacheKey, $translatedMenu, Helpers_Time::DAY_IN_SECONDS);
                }
            }
            $primaryMenuItems = wp_get_nav_menu_items($menuId);
            $cacheService->setCacheForWhitelabelByDomain($primaryMenuItemsCacheKey, $primaryMenuItems, Helpers_Time::DAY_IN_SECONDS);
        }

        try {
            $menuItemsWithInboxClass = array_filter($primaryMenuItems, fn($item) => in_array('infobox', $item->classes));
            $menuLotteriesSlugs = array_map(fn($item) => StringHelper::slugify($item->post_title), $menuItemsWithInboxClass);
        } catch (Throwable $exception) {
            $fileLoggerService->error(
                "$whitelabelName deleted menu for language: $currentCountryCode. Detailed message: " . $exception->getMessage()
            );
        }

        if (empty($menuLotteriesSlugs)) {
            return '';
        }

        /** @var LotteryRepository $lotteryRepository */
        $lotteryRepository = Container::get(LotteryRepository::class);
        $lotteries = $lotteryRepository->findEnabledByWhitelabelIdForInfoBoxes($whitelabel->id, $menuLotteriesSlugs);
        foreach ($lotteries as $lottery) {
            $lotteryImage = Lotto_View::get_lottery_image($lottery['id'], $whitelabel->to_array());
            $lotteryImagePath = Lotto_View::get_lottery_image_path($lottery['id'], $whitelabel->to_array());
            $lotteryImageSize = null;
            $lotteryImageExists = file_exists($lotteryImagePath) && !empty($lotteryImagePath);
            if ($lotteryImageExists) {
                $lotteryImageSizeCheck = getimagesize($lotteryImagePath);
                if ($lotteryImageSizeCheck !== false) {
                    $lotteryImageSize = $lotteryImageSizeCheck;
                }
            }

            $quickPickBaseUrl = lotto_platform_get_permalink_by_slug('order');
            $lotteryImageUrl = UrlHelper::esc_url($lotteryImage);

            $infoBoxesHtml .= ViewHelper::render('Header/Infobox', [
                'lotterySlug' => $lottery['slug'],
                'lotteryImageSize' => $lotteryImageSize,
                'lotteryImageUrl' => $lotteryImageUrl,
                'lotteryName' => $lottery['name'],
                'playInfoHref' => UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('play/' . $lottery['slug'])),
                'title' => $title,
                'quickPickBaseUrl' => $quickPickBaseUrl,
                'pickNumbersText' => _("or pick the numbers manually"),
                'resultsUrl' => lotto_platform_get_permalink_by_slug('results/' . $lottery['slug']),
                'informationUrl' => lotto_platform_get_permalink_by_slug('lotteries/' . $lottery['slug']),
                'resultsText' => sprintf(_("%s Results"), _($lottery['name'])),
                'informationText' => sprintf(_("%s Information"), _($lottery['name'])),
            ]);
        }

        return $infoBoxesHtml;
    }
}