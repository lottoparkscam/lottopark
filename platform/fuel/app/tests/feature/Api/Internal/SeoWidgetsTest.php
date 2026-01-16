<?php

namespace Feature\Api\Internal;

use Carbon\Carbon;
use Fuel\Core\Request;
use Helpers_Lottery;
use Models\Lottery;
use Models\LotteryType;
use Models\WhitelabelLottery;
use Repositories\LotteryRepository;
use Repositories\WhitelabelLotteryRepository;
use Symfony\Component\DomCrawler\Crawler;
use Test_Feature;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class SeoWidgetsTest extends Test_Feature
{
    private const TEST_WIDGET_TYPE = 'pickNumbers';
    private const TEST_LOTTERY_SLUG = 'powerball';
    private WhitelabelLotteryRepository $whitelabelLotteryRepository;
    private LotteryRepository $lotteryRepository;

    public function setUp(): void
    {
        parent::setUp();

        $_SERVER['REQUEST_URI'] = 'api/internal/seoWidgets';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $this->whitelabelLotteryRepository = $this->container->get(WhitelabelLotteryRepository::class);
        $this->lotteryRepository = $this->container->get(LotteryRepository::class);
    }

    /** @test */
    public function getPickNumbersWidget_fieldLotterySlugIsMissing(): void
    {
        // When
        $response = Request::forge('api/internal/seoWidgets')
            ->set_method('GET')
            ->set_get('widgetType', self::TEST_WIDGET_TYPE)
            ->execute()
            ->response()->body;

        // Then
        $this->assertStringContainsString('Field lotterySlug is required', $response);
    }

    /** @test */
    public function getPickNumbersWidget_fieldWidgetTypeIsMissing(): void
    {
        // When
        $response = Request::forge('api/internal/seoWidgets')
            ->set_method('GET')
            ->set_get('lotterySlug', self::TEST_LOTTERY_SLUG)
            ->execute()
            ->response()->body;

        // Then
        $this->assertStringContainsString('Field widgetType is required', $response);
    }

    /** @test */
    public function getPickNumbersWidget_wrongFieldWidgetType(): void
    {
        // When
        $response = Request::forge('api/internal/seoWidgets')
            ->set_method('GET')
            ->set_get('lotterySlug', self::TEST_LOTTERY_SLUG)
            ->set_get('widgetType', 'TyyPPee')
            ->execute()
            ->response()->body;

        // Then
        $this->assertStringContainsString('Provided widgetType property: TyyPPee is wrong', $response);
    }

    /** @test */
    public function getPickNumbersWidget_disabledLottery(): void
    {
        // Given
        /** @var WhitelabelLottery $powerball */
        $powerball = $this->whitelabelLotteryRepository->findOneById(1);
        $powerball->isEnabled = false;
        $powerball->save();

        // When
        $response = Request::forge('api/internal/seoWidgets')
            ->set_method('GET')
            ->set_get('lotterySlug', self::TEST_LOTTERY_SLUG)
            ->set_get('widgetType', self::TEST_WIDGET_TYPE)
            ->execute()
            ->response()->body;

        // Then
        $this->assertStringContainsString('Lottery is not available for this whitelabel', $response);
    }

    /** @test */
    public function getPickNumbersWidget_isKeno(): void
    {
        // Given
        /** @var Lottery $powerball */
        $someKeno = $this->lotteryRepository->findOneByType(Helpers_Lottery::TYPE_KENO);
        $someKeno->isEnabled = true;
        $someKeno->save();

        /** @var WhitelabelLottery $whitelabelsKeno */
        $whitelabelsKeno = $this->whitelabelLotteryRepository->findOneByLotteryId($someKeno->id);
        $whitelabelsKeno->isEnabled = true;
        $whitelabelsKeno->save();

        // When
        $response = Request::forge('api/internal/seoWidgets')
            ->set_method('GET')
            ->set_get('lotterySlug', $someKeno->slug)
            ->set_get('widgetType', self::TEST_WIDGET_TYPE)
            ->execute()
            ->response()->body;

        // Then
        $this->assertStringContainsString('This lottery is not available yet.', $response);
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function getPickNumbersWidget_correctResponse(): void
    {
        // Given
        /** @var Lottery $lottery */
        $lottery = $this->lotteryRepository->findOneBySlug(self::TEST_LOTTERY_SLUG);
        $lottery->currentJackpot = 1;
        $lottery->nextDateLocal = Carbon::now()->addDays(2);
        $lottery->save();

        $_SERVER['REQUEST_URI'] = 'api/internal/seoWidgets';

        // When
        $responseHtml = Request::forge('api/internal/seoWidgets')
            ->set_method('GET')
            ->set_get('lotterySlug', self::TEST_LOTTERY_SLUG)
            ->set_get('widgetType', self::TEST_WIDGET_TYPE)
            ->execute()
            ->response()->body;

        // Then
        $this->assertStringContainsString('Powerball', $responseHtml);
        $this->assertStringContainsString('draw in 2 days', $responseHtml);

        $crawler = new Crawler($responseHtml);
        $jackpot = $crawler->filter('.widget-jackpot')->text();
        $this->assertStringContainsString('€841,800', $jackpot);

        $playButton = $crawler->filter('#seo-widget-play-button');
        $this->assertSame('Play now', $playButton->text());

        // Play button link
        $this->assertStringContainsString('/order/quickpick/powerball/1/', $playButton->attr('href'));

        /** @var LotteryType $lotteryType */
        $lotteryType = $lottery->lotteryType;
        $normalNumbersCount = $lotteryType->nrange;
        $bonusNumbersCount = $lotteryType->brange;

        $foundNormalNumbers = $crawler->filter('.widget-ticket-numbers > .widget-ticket-number');
        $this->assertSame($foundNormalNumbers->count(), $normalNumbersCount);

        // Check value and order
        $startNumber = 1;
        $foundNormalNumbers->each(function ($normalNumber) use (&$startNumber) {
            $this->assertSame((int)$normalNumber->text(), $startNumber);
            $startNumber++;
        });

        $foundBonusNumbers = $crawler->filter('.widget-ticket-bnumbers > .widget-ticket-number');
        $this->assertSame($foundBonusNumbers->count(), $bonusNumbersCount);

        // Check value and order
        $startNumber = 1;
        $foundBonusNumbers->each(function ($bonusNumber) use (&$startNumber) {
            $this->assertSame((int)$bonusNumber->text(), $startNumber);
            $startNumber++;
        });
    }

    /** @test */
    public function getPickNumbersWidget_withValidOrderUrl_correctResponse(): void
    {
        // Given
        $orderUrl = 'https://correct.url/test/';

        // When
        $responseHtml = Request::forge('api/internal/seoWidgets')
            ->set_method('GET')
            ->set_get('lotterySlug', self::TEST_LOTTERY_SLUG)
            ->set_get('widgetType', self::TEST_WIDGET_TYPE)
            ->set_get('orderUrl', $orderUrl)
            ->execute()
            ->response()->body;

        // Then
        $this->assertStringContainsString($orderUrl, $responseHtml);
    }

    /** @test */
    public function getPickNumbersWidget_withInvalidOrderUrl_correctResponse(): void
    {
        // Given
        $orderUrl = 'correct.url/test/';

        // When
        $responseHtml = Request::forge('api/internal/seoWidgets')
            ->set_method('GET')
            ->set_get('lotterySlug', self::TEST_LOTTERY_SLUG)
            ->set_get('widgetType', self::TEST_WIDGET_TYPE)
            ->set_get('orderUrl', $orderUrl)
            ->execute()
            ->response()->body;

        // Then
        $this->assertStringContainsString('The field orderUrl must contain a valid URL', $responseHtml);
    }

    /** @test */
    public function getPickNumbersWidget_withValidCurrencyCode_correctResponse(): void
    {
        // Given
        $currencyCode = 'PLN';

        // When
        $responseHtml = Request::forge('api/internal/seoWidgets')
            ->set_method('GET')
            ->set_get('lotterySlug', self::TEST_LOTTERY_SLUG)
            ->set_get('widgetType', self::TEST_WIDGET_TYPE)
            ->set_get('currencyCode', $currencyCode)
            ->execute()
            ->response()->body;

        // Then
        $this->assertStringContainsString($currencyCode, $responseHtml);
    }

    /** @test */
    public function getPickNumbersWidget_withInvalidCurrencyCode_correctResponse(): void
    {
        // Given
        $currencyCode = 'ABCD';

        // When
        $responseHtml = Request::forge('api/internal/seoWidgets')
            ->set_method('GET')
            ->set_get('lotterySlug', self::TEST_LOTTERY_SLUG)
            ->set_get('widgetType', self::TEST_WIDGET_TYPE)
            ->set_get('currencyCode', $currencyCode)
            ->execute()
            ->response()->body;

        // Then
        $this->assertStringContainsString('The field currencyCode must contain exactly 3 characters', $responseHtml);
    }

    /** @test */
    public function getPickNumbersWidget_withCurrencyCodeNotFound_correctResponse(): void
    {
        // Given
        $currencyCode = 'ABC';

        // When
        $responseHtml = Request::forge('api/internal/seoWidgets')
            ->set_method('GET')
            ->set_get('lotterySlug', self::TEST_LOTTERY_SLUG)
            ->set_get('widgetType', self::TEST_WIDGET_TYPE)
            ->set_get('currencyCode', $currencyCode)
            ->execute()
            ->response()->body;

        // Then
        $this->assertStringContainsString('Currency ABC not exists', $responseHtml);
    }
}
