<?php

namespace Tests\Feature\Helpers\Scan;

use Carbon\Carbon;
use Helpers_Time;
use LtechManualDrawService;
use Models\Lottery;
use Models\LtechManualDraw;
use Repositories\CurrencyRepository;
use Repositories\LotteryRepository;
use Repositories\LtechManualDrawRepository;
use Test_Feature;

class LtechManualDrawServiceTest extends Test_Feature
{
    private Lottery $powerballLottery;
    private LotteryRepository $lotteryRepository;
    private LtechManualDrawService $serviceUnderTest;
    private CurrencyRepository $currencyRepository;
    private LtechManualDrawRepository $ltechManualDrawRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->lotteryRepository = $this->container->get(LotteryRepository::class);
        $this->powerballLottery = $this->lotteryRepository->findOneBySlug('powerball');
        $this->currencyRepository = $this->container->get(CurrencyRepository::class);

        $this->serviceUnderTest = $this->container->get(LtechManualDrawService::class);

        $this->ltechManualDrawRepository = $this->container->get(LtechManualDrawRepository::class);
    }

    /**
     * @test
     */
    public function add_withAdditionalNumber(): void
    {
        // Given
        $nextDraw = (Carbon::now($this->powerballLottery->timezone))->addDays(4);
        $this->powerballLottery->nextDateLocal = $nextDraw;
        $this->powerballLottery->nextDateUtc = $nextDraw->setTimezone('UTC');
        $this->powerballLottery->save();

        $currency = $this->currencyRepository->findOneByCode('PLN');

        // When
        $now = Carbon::now();
        $this->serviceUnderTest->add(
            $this->powerballLottery,
            '1998-01-01',
            [2, 4, 6],
            [8, 7],
            700,
            $currency,
            ['match-1' => 2.74],
            ['match-2' => 3],
            2
        );
        /** @var LtechManualDraw $ltechManualDraw */
        $ltechManualDraw = $this->ltechManualDrawRepository->findOne();

        // Then
        $this->assertSame(
            $this->powerballLottery->nextDateLocal->format(Helpers_Time::DATETIME_FORMAT),
            $ltechManualDraw->currentDrawDate->format(Helpers_Time::DATETIME_FORMAT),
        );
        $this->assertSame(
            $this->powerballLottery->nextDateUtc->format(Helpers_Time::DATETIME_FORMAT),
            $ltechManualDraw->currentDrawDateUtc->format(Helpers_Time::DATETIME_FORMAT),
        );
        $this->assertSame('1998-01-01', $ltechManualDraw->nextDrawDate->format(Helpers_Time::DATE_FORMAT));
        $this->assertSame($this->powerballLottery, $ltechManualDraw->lottery);
        $this->assertSame([2, 4, 6], $ltechManualDraw->normalNumbers);
        $this->assertSame([8, 7], $ltechManualDraw->bonusNumbers);
        $this->assertSame(2, $ltechManualDraw->additionalNumber);
        $this->assertSame(['match-1' => 2.74], $ltechManualDraw->prizes);
        $this->assertSame(['match-2' => 3], $ltechManualDraw->winners);
        $this->assertSame($currency->code, $ltechManualDraw->currency->code);
        $this->assertSame($now->format(Helpers_Time::DATETIME_FORMAT), $ltechManualDraw->createdAt->format(Helpers_Time::DATETIME_FORMAT));
    }

    /**
     * @test
     */
    public function add_withoutAdditionalNumber(): void
    {
        // Given
        $nextDraw = (Carbon::now($this->powerballLottery->timezone))->addDays(4);
        $this->powerballLottery->nextDateLocal = $nextDraw;
        $this->powerballLottery->nextDateUtc = $nextDraw->setTimezone('UTC');
        $this->powerballLottery->save();

        $currency = $this->currencyRepository->findOneByCode('PLN');

        // When
        $now = Carbon::now();
        $this->serviceUnderTest->add(
            $this->powerballLottery,
            '1998-01-01',
            [2, 4, 6],
            [8, 7],
            700,
            $currency,
            ['match-1' => 2.74],
            ['match-2' => 3]
        );

        /** @var LtechManualDraw $ltechManualDraw */
        $ltechManualDraw = $this->ltechManualDrawRepository->findOne();

        // Then
        $this->assertNull($ltechManualDraw->additionalNumber);
    }
}
