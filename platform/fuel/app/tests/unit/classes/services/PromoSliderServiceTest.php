<?php

namespace Tests\Unit\Classes\Services;

use Models\Whitelabel;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\MockObject\MockObject;
use Services\PromoSliderService;
use Test_Unit;

final class PromoSliderServiceTest extends Test_Unit
{
    use PHPMock;

    private MockObject $getThemeModMock;

    public function setUp(): void
    {
        parent::setUp();

        $callback = function ($parameter): string {
            switch ($parameter) {
                case '/':
                    return 'https://lottopark.loc/';
                default:
                    return 'https://lottopark.loc/' . $parameter . '/';
            }
        };

        $this->getThemeModMock = $this->getFunctionMock('Services', 'get_theme_mod');

        $permalinkBySlugMock1 = $this->getFunctionMock(
            'Presenters\Wordpress\Base\Slots',
            'lotto_platform_get_permalink_by_slug'
        );

        $permalinkBySlugMock2 = $this->getFunctionMock(
            'Services',
            'lotto_platform_get_permalink_by_slug'
        );

        $permalinkBySlugMock3 = $this->getFunctionMock(
            'Helpers',
            'lotto_platform_get_permalink_by_slug'
        );

        $permalinkBySlugMock1->expects($this->any())->willReturnCallback($callback);
        $permalinkBySlugMock2->expects($this->any())->willReturnCallback($callback);
        $permalinkBySlugMock3->expects($this->any())->willReturnCallback($callback);

        $this->container->set('whitelabel', $this->getFakeWhitelabel());
    }

    public function getLottoCasinoDataProvider(): array
    {
        return [
            'lotto' => [false],
            'casino' => [true],
        ];
    }

    public function getCasinoSlideSlugFilterProvider(): array
    {
        return [
            'Out of casino filter scope' => [
                '?lotto-param=test',
                null
            ],
            [
                '?game_uuid=954eb970119c67043bd42b6a8d7c9b6534a352e4&mode=demo',
                'https://lottopark.loc/casino-play/?game_uuid=954eb970119c67043bd42b6a8d7c9b6534a352e4&mode=demo'
            ],
            'Casino play link' => [
                'lobby?game_uuid=954eb970119c67043bd42b6a8d7c9b6534a352e4',
                'https://lottopark.loc/casino-lobby/?game_uuid=954eb970119c67043bd42b6a8d7c9b6534a352e4'
            ],
            'Casino filter provider' => [
                '?provider=amatic',
                'https://lottopark.loc/?provider=amatic'
            ],
            'Casino filter type' => [
                '?type=baccarat',
                'https://lottopark.loc/?type=baccarat'
            ],
            'Casino filter slot_game_name' => [
                '?slot_game_name=blackjack',
                'https://lottopark.loc/?slot_game_name=blackjack'
            ]
        ];
    }

    public function getSliderHtmlIsCorrectDataProvider(): array
    {
        $expectedHtmlLotto = <<<ELEM
            <div class="promo-slider slick-slider ">
                   <div>
                        <a href="https://lottopark.loc/casino-promotions/"><img src="https://lottopark.loc/image1.png"></a>
                    </div>
                    <div>
                        <a href="https://lottopark.loc/casino-post/">
                            <img src="https://lottopark.loc/image2.png">
                        </a>
                    </div>
                    <div>
                        <a href="https://casino.lottopark.loc/casino-play/?game_uuid=81a34cdbabe04cc7a0794b1e5ac0d480&mode=demo">
                            <img src="https://lottopark.loc/image3.png">
                        </a>
                    </div>
                </div>
            ELEM;

        $expectedHtmlCasino = <<<ELEM
            <div class="promo-slider slick-slider ">
                   <div>
                        <a href="https://lottopark.loc/casino-promotions/"><img src="https://lottopark.loc/image1.png"></a>
                    </div>
                    <div>
                        <a href="https://lottopark.loc/casino-play/?game_uuid=954eb970119c67043bd42b6a8d7c9b6534a352e4&mode=demo">
                            <img src="https://lottopark.loc/image2.png">
                        </a>
                    </div>
                    <div>
                        <a href="https://lottopark.loc/?type=baccarat">
                            <img src="https://lottopark.loc/image3.png">
                        </a>
                    </div>
                    <div>
                        <a href="https://lottopark.loc/?provider=amatic">
                            <img src="https://lottopark.loc/image4.png">
                        </a>
                    </div>
                    <div>
                        <a href="https://lottopark.loc/?slot_game_name=blackjack">
                            <img src="https://lottopark.loc/image5.png">
                        </a>
                    </div>
                    <div>
                        <a href="https://lottopark.loc/casino-lobby/?game_uuid=954eb970119c67043bd42b6a8d7c9b6534a352e4">
                            <img src="https://lottopark.loc/image6.png">
                        </a>
                    </div>
                    <div>
                        <a href="https://lottopark.loc/casino-post/">
                            <img src="https://lottopark.loc/image7.png">
                        </a>
                    </div>
                </div>
            ELEM;

        return [
            'lotto' => [false, $expectedHtmlLotto],
            'casino' => [true, $expectedHtmlCasino],
        ];
    }

    /**
     * @test
     * @dataProvider getCasinoSlideSlugFilterProvider
     */
    public function casinoSlideSlugFilter(string $slideSlug, ?string $expectedLink): void
    {
        $slideLink = null;

        $promoSliderService = $this->getPromoSliderService(true);
        $casinoSlideSlugFilter = $promoSliderService->getCasinoSlideSlugFilter();
        $this->assertIsCallable($casinoSlideSlugFilter);
        $casinoSlideSlugFilter($slideLink, $slideSlug);
        $this->assertSame($expectedLink, $slideLink);
    }

    /**
     * @test
     */
    public function defaultValues_nonExistingSlider(): void
    {
        $promoSliderService = new PromoSliderService('promo_slider_id');

        $displaySlider = $promoSliderService->displaySlider();
        $slidesCount = $promoSliderService->getSlidesCount();

        $this->assertFalse($displaySlider);
        $this->assertSame(0, $slidesCount);
    }

    /**
     * @test
     * @dataProvider getSliderHtmlIsCorrectDataProvider
     */
    public function render_SliderHtmlIsCorrect(bool $isCasino, string $expectedHtml): void
    {
        $lotterySlidesCount = 3;
        $casinoSlidesCount = 7;
        $sliderSettings = [
            'display_%s' => true,
            '%s_slides_count' => $isCasino ? $casinoSlidesCount : $lotterySlidesCount,
        ];

        if ($isCasino) {
            $sliderSlides = [
                1 => ['link' => 'https://lottopark.loc/image1.png', 'slug' => 'casino-promotions'],
                2 => [
                    'link' => 'https://lottopark.loc/image2.png',
                    'slug' => '?game_uuid=954eb970119c67043bd42b6a8d7c9b6534a352e4&mode=demo'
                ],
                3 => ['link' => 'https://lottopark.loc/image3.png', 'slug' => '?type=baccarat'],
                4 => ['link' => 'https://lottopark.loc/image4.png', 'slug' => '?provider=amatic'],
                5 => ['link' => 'https://lottopark.loc/image5.png', 'slug' => '?slot_game_name=blackjack'],
                6 => [
                    'link' => 'https://lottopark.loc/image6.png',
                    'slug' => 'lobby?game_uuid=954eb970119c67043bd42b6a8d7c9b6534a352e4'
                ],
                7 => ['link' => 'https://lottopark.loc/image7.png', 'slug' => 'post:casino-post'],
            ];
        } else {
            $sliderSettings['%s_url_target_3'] = 'casino';
            $sliderSlides = [
                1 => ['link' => 'https://lottopark.loc/image1.png', 'slug' => 'casino-promotions'],
                2 => ['link' => 'https://lottopark.loc/image2.png', 'slug' => 'post:casino-post'],
                3 => [
                    'link' => 'https://lottopark.loc/image3.png',
                    'slug' => '?game_uuid=81a34cdbabe04cc7a0794b1e5ac0d480&mode=demo'
                ],
            ];
        }

        $callback = $this->getThemeModMockCallback($sliderSettings, $sliderSlides);
        $this->getThemeModMock->expects($this->atLeastOnce())->willReturnCallback($callback);

        $promoSliderService = $this->getPromoSliderService($isCasino);
        $actualHtml = $promoSliderService->render();
        $slidesCount = $promoSliderService->getSlidesCount();

        // Remove whitespace characters and verify html is the same
        $expectedHtml = preg_replace('/\s/', "", $expectedHtml);
        $expectedCount = count($sliderSlides);
        $actualHtml = preg_replace('/\s/', "", $actualHtml);

        $this->assertSame($expectedCount, $slidesCount);
        $this->assertEquals($expectedHtml, $actualHtml);
    }

    /**
     * @test
     * @dataProvider getLottoCasinoDataProvider
     */
    public function render_NotEnabled_DoesNotDisplayAnyHtml(bool $isCasino): void
    {
        $callback = $this->getThemeModMockCallback([
            'display_%s' => false,
        ]);

        $this->getThemeModMock->expects($this->atLeastOnce())->willReturnCallback($callback);

        $promoSliderService = $this->getPromoSliderService($isCasino);

        $displaySlider = $promoSliderService->displaySlider();
        $actualHtml = $promoSliderService->render();
        $slidesCount = $promoSliderService->getSlidesCount();

        $this->assertFalse($displaySlider);
        $this->assertSame(0, $slidesCount);
        $this->assertEmpty($actualHtml);
    }

    /**
     * @test
     * @dataProvider getLottoCasinoDataProvider
     */
    public function render_SliderMissingSlugOrImage(bool $isCasino): void
    {
        $sliderSettings = [
            'display_%s' => true,
            '%s_slides_count' => 2,
        ];

        $sliderSlides = [
            1 => ['link' => '', 'slug' => 'casino-promotions'],
            2 => ['link' => 'https://lottopark.loc/image2.png', 'slug' => '']
        ];

        $callback = $this->getThemeModMockCallback($sliderSettings, $sliderSlides);

        $this->getThemeModMock->expects($this->atLeastOnce())->willReturnCallback($callback);

        $expectedHtml = <<<ELEM
            <div class="promo-slider slick-slider ">
                    <div>
                        <img src="https://lottopark.loc/image2.png">
                    </div>
                </div>
            ELEM;

        $promoSliderService = $this->getPromoSliderService($isCasino);

        $actualHtml = $promoSliderService->render();
        $slidesCount = $promoSliderService->getSlidesCount();

        // Remove whitespace characters and verify html is the same
        $expectedHtml = preg_replace('/\s/', "", $expectedHtml);
        $actualHtml = preg_replace('/\s/', "", $actualHtml);

        $this->assertSame(2, $slidesCount);
        $this->assertEquals($expectedHtml, $actualHtml);
    }

    /**
     * @test
     * @dataProvider getLottoCasinoDataProvider
     */
    public function render_HtmlShouldBeEmptyWhenAllSlidesHaveMissingImage(bool $isCasino): void
    {
        $sliderSettings = [
            'display_%s' => true,
            '%s_slides_count' => 1,
        ];

        $sliderSlides = [
            1 => ['link' => '', 'slug' => 'casino-promotions'],
        ];

        $callback = $this->getThemeModMockCallback($sliderSettings, $sliderSlides);

        $this->getThemeModMock->expects($this->atLeastOnce())->willReturnCallback($callback);

        $promoSliderService = $this->getPromoSliderService($isCasino);
        $actualHtml = $promoSliderService->render();
        $slidesCount = $promoSliderService->getSlidesCount();

        $this->assertSame(1, $slidesCount);
        $this->assertEmpty($actualHtml);
    }

    private function getPromoSliderService(bool $isCasino): PromoSliderService
    {
        $promoSliderId = $isCasino ? 'casino_promo_slider' : 'lotto_promo_slider';

        return new PromoSliderService($promoSliderId, $isCasino);
    }

    private function getThemeModMockCallback(array $sliderSettings, array $sliderSlides = []): callable
    {
        return function ($parameter) use ($sliderSettings, $sliderSlides): int|string|null {
            foreach (['lotto_promo_slider', 'casino_promo_slider'] as $id) {
                foreach ($sliderSettings as $attributeName => $value) {
                    if ($parameter === sprintf($attributeName, $id)) {
                        return $value;
                    }
                }

                foreach ($sliderSlides as $index => $slide) {
                    $slideLinkAttributeName = sprintf('%s_%s', $id, $index);
                    $slideSlugAttributeName = sprintf('%s_slug_%s', $id, $index);

                    switch ($parameter) {
                        case $slideLinkAttributeName:
                            return $slide['link'];
                        case $slideSlugAttributeName:
                            return $slide['slug'];
                    }
                }
            }

            return null;
        };
    }

    private function getFakeWhitelabel(): Whitelabel
    {
        $whitelabel = new Whitelabel();
        $whitelabel->id = $this->random_id();
        $whitelabel->domain = 'lottopark.loc';
        $whitelabel->currency = $this->get_currency(['code' => 'EUR']);
        $whitelabel->managerSiteCurrencyId = $whitelabel->currency->id;
        $whitelabel->margin = 11.00;

        return $whitelabel;
    }
}
