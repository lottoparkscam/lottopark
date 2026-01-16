<?php

namespace Presenters\Wordpress\Base\Slots;

use Container;
use Fuel\Core\Cache;
use Fuel\Core\CacheNotFoundException;
use Fuel\Core\Input;
use Helper_Route;
use Helpers\CountryHelper;
use Helpers\RouteHelper;
use Helpers\SlotHelper;
use Helpers\WhitelabelHelper;
use Helpers\Wordpress\LanguageHelper;
use Helpers_Time;
use Models\Whitelabel;
use Presenters\Wordpress\AbstractWordpressPresenter;
use Repositories\WhitelabelSlotProviderRepository;
use Services\Logs\FileLoggerService;
use Services\PromoSliderService;
use Throwable;
use Services\Api\Slots\SlotCacheService;

/** Presenter for /wordpress/wp-content/themes/base/template-casino.php */
final class GameListPresenter extends AbstractWordpressPresenter
{
    public const AVAILABLE_GAMES_TYPES = [
        'Slots',
        'Roulette',
        'Blackjack',
        'Baccarat',
        'Video poker',
        'Poker',
        'Lottery',
        'Scratch card',
        'Other',
        'Live games'
    ];

    private int $whitelabelId;
    private Whitelabel $whitelabel;
    private WhitelabelSlotProviderRepository $whitelabelSlotProviderRepository;
    private FileLoggerService $fileLoggerService;
    private SlotCacheService $slotCacheService;

    public function __construct()
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');

        $this->whitelabel = $whitelabel;
        $this->whitelabelId = $whitelabel->id;
        $this->whitelabelSlotProviderRepository = Container::get(WhitelabelSlotProviderRepository::class);
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        $this->slotCacheService = Container::get(SlotCacheService::class);
    }

    public function view(): string
    {
        $typeInput = Input::get('type');

        $data = [
            'casinoPageTitle' => $this->casinoPageTitle(),
            'gameTypes' => $this->gameTypes(),
            'gameProviders' => $this->gameProviders(),
            'promoBannerElement' => $this->promoSliderElement(),
            'translatedShowMore' => _('See more'),
            'hasNotTypeFilter' => empty($typeInput),
            'hasTypeFilter' => !empty($typeInput),
            'casinoContentWidget' => $this->casinoContentWidget(),
        ];

        return $this->forge($data);
    }

    public function shouldHideListOfGamesOnHomepage(): bool
    {
        return false;
    }

    private function gameTypes(): string
    {
        $allowedGameTypes  = SlotHelper::getAllowedGameTypes($this->whitelabel->domain);
        $hasSpecificFilters = is_array($allowedGameTypes);
        if ($hasSpecificFilters) {
            $gameTypes = $allowedGameTypes;
        } else {
            $gameTypes = self::AVAILABLE_GAMES_TYPES;
        }
        sort($gameTypes);

        return $this->generateOptionHtmlFromArray($gameTypes);
    }

    private function gameProviders(): string
    {
        $userCountry = CountryHelper::iso() ?: 'UK';
        $providersCacheKey = $this->slotCacheService->getProvidersCacheKey($this->whitelabelId, $userCountry);

        try {
            $gameProviders = Cache::get($providersCacheKey);
        } catch (CacheNotFoundException $exception) {
            $gameProviders = $this->whitelabelSlotProviderRepository->getAllowedSubprovidersNamesByWhitelabelId($this->whitelabelId);
            Cache::set($providersCacheKey, $gameProviders, Helpers_Time::DAY_IN_SECONDS);
        }

        if (empty($gameProviders)) {
            return '';
        }

        return $this->generateOptionHtmlFromArray($gameProviders);
    }

    /**
     * Handles translation of page title based on current language
     * Uses "casino" slug to find page id
     */
    private function casinoPageTitle(): string
    {
        $currentLanguageCode = LanguageHelper::getCurrentLanguageShortcode();
        $casinoPageTitleCacheKey = $currentLanguageCode . '_casino_page_title_' . $this->whitelabelId;

        try {
            $casinoPageTitle = Cache::get($casinoPageTitleCacheKey);
        } catch (CacheNotFoundException $exception) {
            $casinoPageTitle = $this->getCasinoPageTitleByLanguageCode($currentLanguageCode);
            Cache::set($casinoPageTitleCacheKey, $casinoPageTitle, Helpers_Time::DAY_IN_SECONDS);
        }

        return $casinoPageTitle;
    }

    private function getCasinoPageTitleByLanguageCode(string $languageCode): string
    {
        $pageId = 0;

        try {
            $pageId = RouteHelper::getCasinoHomePageId($languageCode, $this->whitelabel->domain);
        } catch (Throwable $exception) {
            $this->fileLoggerService->error(
                "Could not find page by ID: {$pageId}; Slug: " . Helper_Route::CASINO_HOMEPAGE .
                    ' Detailed message: ' . $exception->getMessage()
            );

            return $this->convertTitle('Casino');
        }

        return $this->convertTitle(get_the_title($pageId));
    }

    private function convertTitle(string $title): string
    {
        return WhitelabelHelper::convertTitle($title, $this->whitelabel->domain);
    }

    protected function generateOptionHtmlFromArray(array $data): string
    {
        $html = null;
        foreach ($data as $value) {
            $selected = !empty(Input::get($value)) ? 'selected' : '';
            $html .= "<option value='" . strtolower($value) . "' $selected>$value</option>";
        }
        return $html;
    }

    public function shouldHidePromoSlider(): bool
    {
        return false;
    }

    public function casinoContentWidget(): string
    {
        if (is_active_sidebar('casino-frontpage-sidebar-content-id')) {
            ob_start();
            dynamic_sidebar('casino-frontpage-sidebar-content-id');
            $sidebar = ob_get_contents();
            ob_end_clean();

            return $sidebar;
        }

        return '';
    }

    /**
     * Slider handles following slug examples (includes language support):
     * 1. Pages:
     * -- "casino-promotions"
     * 2. Posts:
     * -- post:slug
     * 3. Game link:
     * -- "?game_uuid=04a5af8c8e16ebfd944bd8805332226e55d51964&mode=demo"
     * -- "?game_uuid=04a5af8c8e16ebfd944bd8805332226e55d51964"
     * -- "lobby?game_uuid=04a5af8c8e16ebfd944bd8805332226e55d51964" - for games with lobby
     * 4. Game filters:
     * -- "?type=baccarat"
     * -- "?provider=amatic"
     * -- "?slot_game_name=blackjack"
     * -- any filter mixes e.g. type + provider, or name + type
     */
    public function promoSliderElement(bool $isCasino = true): string
    {
        if ($this->shouldHidePromoSlider()) {
            return '';
        }

        $promoSliderId = $isCasino ? 'casino_promo_slider' : 'lotto_promo_slider';

        try {
            return (new PromoSliderService($promoSliderId, $isCasino))->render();
        } catch (Throwable $exception) {
            $this->fileLoggerService->warning(
                'One of slugs used in promo slider does not exist. Detailed message: ' . $exception->getMessage()
            );
        }

        return '';
    }

    public function getCasinoPlayLink(string $slug = null): string
    {
        return RouteHelper::getCasinoPlayLink($this->whitelabel->domain) . $slug;
    }

    public function getCasinoLobbyLink(string $slug = null): string
    {
        return RouteHelper::getCasinoLobbyLink($this->whitelabel->domain) . $slug;
    }
}
