<?php

declare(strict_types=1);

namespace Services;

use Container;
use Helpers\RouteHelper;
use Helpers\UrlHelper;
use Models\Whitelabel;

class PromoSliderService
{
    private string $nameId;
    private Whitelabel $whitelabel;
    private bool $isCasino;
    private int $slidesCount;
    private array $slideLinkFilters = [];

    public function __construct(string $id, bool $isCasino = false)
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $this->whitelabel = $whitelabel;
        $this->isCasino = $isCasino;
        $this->nameId = $id;

        $this->setSlidesCount();
    }

    public function displaySlider(): bool
    {
        return (bool) get_theme_mod('display_' . $this->nameId);
    }

    public function getSlidesCount(): int
    {
        return $this->slidesCount;
    }

    public function addSlideLinkFilter(callable $filter): void
    {
        $this->slideLinkFilters[] = $filter;
    }

    public function render(): string
    {
        if (!$this->displaySlider()) {
            return '';
        }

        $oneSlideOnlyClass = '';
        $items = [];

        for ($index = 1; $index <= $this->slidesCount; $index++) {
            $item = $this->getPromoSliderItem($index);

            if (is_array($item)) {
                list($image, $link, $slug) = $item;
                $items[] = $this->renderItem($image, $link, $slug);
            }
        }

        if ($this->slidesCount == 1) {
            $oneSlideOnlyClass = 'one-slide-only';
        }

        $slidesHtml = implode('', $items);

        if (empty($slidesHtml)) {
            return '';
        }

        return <<<ELEM
                    <div class="promo-slider slick-slider $oneSlideOnlyClass">
                       $slidesHtml 
                    </div>
                ELEM;
    }

    private function renderItem(string $slideImage, string $slideLink, string $slideSlug = ''): string
    {
        $isSlugNotEmpty = !empty($slideSlug);

        $image = <<<ELEM
                    <img src="$slideImage">
                ELEM;

        if ($isSlugNotEmpty) {
            $link = <<<ELEM
                        <a href="$slideLink">$image</a>
                    ELEM;

            return <<<ELEM
                    <div>$link</div>
                ELEM;

        }

        return <<<ELEM
                    <div>$image</div>
                ELEM;
    }

    private function getPromoSliderItem(int $index): ?array
    {
        $slideLink = null;
        $slideImage = $this->getSlideImageByIndex($index);

        if (empty($slideImage)) {
            return null;
        }

        $slideSlug = $this->getSlideSlugByIndex($index);

        if ($this->isCasino || $this->isSlideTargetUrlIsCasino($index)) {
            $this->addSlideLinkFilter($this->getCasinoSlideSlugFilter());
        }

        $this->applySlideLinkFilters($slideLink, $slideSlug);

        if ($slideLink === null) {
            $isPost = str_starts_with($slideSlug, 'post:');

            if ($isPost) {
                $slideSlug = str_replace('post:', '', $slideSlug);
                $slideLink = lotto_platform_get_permalink_by_slug($slideSlug, 'post');
            } else {
                $slideLink = lotto_platform_get_permalink_by_slug($slideSlug);
            }
        }

        if (!empty($slideLink) && !$this->isCasino && $this->isSlideTargetUrlIsCasino($index)) {
            $slideLink = $this->changeLotteryUrlToCasinoUrl($slideLink);
        }

        if (!empty($slideLink) && $this->isSlideTargetUrlIsLottery($index)) {
            $slideLink = $this->changeCasinoUrlToLotteryUrl($slideLink);
        }

        return [
            $slideImage,
            $slideLink,
            $slideSlug,
        ];
    }

    private function setSlidesCount(): void
    {
        $this->slidesCount = (int) get_theme_mod($this->nameId . '_slides_count', 0);
    }

    private function getSlideImageByIndex(int $index): string
    {
        return get_theme_mod($this->nameId . '_' . $index);
    }

    private function getSlideSlugByIndex(int $index): string
    {
        return get_theme_mod($this->nameId . '_slug_' . $index);
    }

    private function getSlideTargetUrlByIndex(int $index): mixed
    {
        return get_theme_mod($this->nameId . '_url_target_' . $index);
    }

    private function isSlideTargetUrlIsCasino(int $index): bool
    {
        return $this->getSlideTargetUrlByIndex($index) === 'casino';
    }

    private function isSlideTargetUrlIsLottery(int $index): bool
    {
        return $this->getSlideTargetUrlByIndex($index) === 'lottery';
    }

    private function applySlideLinkFilters(?string &$slideLink, string $slideSlug): void
    {
        foreach ($this->slideLinkFilters as $filter) {
            if (is_callable($filter)) {
                $filter($slideLink, $slideSlug);
            }
        }
    }

    public function getCasinoSlideSlugFilter(): callable
    {
        return function (?string &$slideLink, string $slideSlug): void {
            $isCasinoGameUrl = str_starts_with($slideSlug, '?game_uuid=');
            $isCasinoFilterUrl = str_starts_with($slideSlug, '?provider=') || str_starts_with($slideSlug, '?type=') || str_starts_with($slideSlug, '?slot_game_name=');
            $isLobbyGameUrl = str_starts_with($slideSlug, 'lobby?game_uuid=');

            if ($isLobbyGameUrl) {
                $slideSlugWithoutLobby = str_replace('lobby', '', $slideSlug);
                $slideLink = $this->getCasinoLobbyLink($slideSlugWithoutLobby);
            } elseif ($isCasinoGameUrl) {
                $slideLink = $this->getCasinoPlayLink($slideSlug);
            } elseif ($isCasinoFilterUrl) {
                $slideLink = lotto_platform_get_permalink_by_slug('/') . $slideSlug;
            }
        };
    }

    private function getCasinoPlayLink(string $slug = null): string
    {
        return RouteHelper::getCasinoPlayLink($this->whitelabel->domain) . $slug;
    }

    private function getCasinoLobbyLink(string $slug = null): string
    {
        return RouteHelper::getCasinoLobbyLink($this->whitelabel->domain) . $slug;
    }

    private function changeLotteryUrlToCasinoUrl(string $url): string
    {
        return str_replace(
            $this->whitelabel->domain,
            UrlHelper::getCurrentCasinoPrefix() . '.' . $this->whitelabel->domain,
            $url
        );
    }

    private function changeCasinoUrlToLotteryUrl(string $url): string
    {
        $casinoPrefixesWithDot = array_map(static fn($prefix) => $prefix . '.', UrlHelper::getCasinoPrefixes());

        return str_replace(
            $casinoPrefixesWithDot,
            '',
            $url
        );
    }
}
