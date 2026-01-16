<?php

namespace Tests\E2e\Controller\Api\Internal;

use Carbon\Carbon;
use Container;
use Helpers_Time;
use Lotto_Helper;
use Models\Lottery;
use Models\Raffle;
use Repositories\LotteryRepository;
use Test_E2e_Controller_Api;
use Tests\Fixtures\Raffle\RaffleFixture;

class LotteryTest extends Test_E2e_Controller_Api
{
    private LotteryRepository $lotteryRepository;
    private RaffleFixture $raffleFixture;
    private Carbon $oldLastDateLocal;
    private Carbon $oldNextDateLocal;
    private array $oldDrawDates;
    private Lottery $powerballLottery;
    private Raffle $raffle;

    public function setUp(): void
    {
        parent::setUp();
        $_SERVER['HTTP_HOST'] = 'lottopark.loc';
        $this->lotteryRepository = Container::get(LotteryRepository::class);
        $this->raffleFixture = Container::get(RaffleFixture::class);

        $this->powerballLottery = $this->lotteryRepository->findOneBySlug('powerball');
        $now = Carbon::now($this->powerballLottery->timezone);
        $nextDraw = $now->addMonths(3)->addMinutes(5);
        $this->powerballLottery->drawDates = [$nextDraw->format('D H:i')];
        $this->powerballLottery->lastDateLocal = $now->addMonths(2)->format(Helpers_Time::DATETIME_FORMAT);
        $this->powerballLottery->nextDateLocal = $now->addMonths(3)->format(Helpers_Time::DATETIME_FORMAT);
        $this->oldLastDateLocal = $this->powerballLottery->lastDateLocal;
        $this->oldNextDateLocal = $this->powerballLottery->nextDateLocal;
        $this->oldDrawDates = $this->powerballLottery->drawDates;
        $this->powerballLottery->save();

        $raffle = $this->raffleFixture->with('basic')->createOne();
        /** @var Raffle $raffle */
        $this->raffle = Raffle::find($raffle->id);
        $this->raffle->isEnabled = true;
        $this->raffle->mainPrize = 127000;
        $this->raffle->save();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->raffle->delete(true);
        $this->powerballLottery->lastDateLocal = $this->oldLastDateLocal;
        $this->powerballLottery->nextDateLocal = $this->oldNextDateLocal;
        $this->powerballLottery->drawDates = $this->oldDrawDates;
        $this->powerballLottery->save();
    }

    /** @test */
    public function getAll(): void
    {
        $response = $this->getResponse(
            'GET',
            '/api/internal/lottery/all'
        );

        $nextRealDraw = Lotto_Helper::get_lottery_real_next_draw($this->powerballLottery->to_array());
        $powerball = $response['body']['lotteries'][0];
        $this->assertSame('Powerball', $powerball['name']);
        $this->assertSame('powerball', $powerball['slug']);
        $this->assertSame('draw in 8 months from now', $powerball['nextRealDrawFromNow']);
        $this->assertSame('draw in 8 months', $powerball['nextRealDrawShort']);
        $this->assertSame($nextRealDraw->getTimestamp(), $powerball['nextRealDrawTimestamp']);
        $this->assertSame('1 days 0 hrs', $powerball['nextDrawForListWidget']);
        $this->assertSame('false', $powerball['jackpotHasThousands']);

        $jackpotFormatted = "<span class=\"tooltip tooltip-bottom local-amount\" data-tooltip=\"$122,000,000\">€102,699,600</span>";
        $this->assertSame($jackpotFormatted, $powerball['jackpotFormatted']);

        $this->assertSame('€8.22', $powerball['price']);
        $this->assertSame('quickpick/powerball/3/', $powerball['quickPickPath']);
        $this->assertSame('3 Quick-Pick lines', $powerball['quickPickLinesText']);
        $this->assertSame('only €8.22', $powerball['quickPickLinesPriceText']);
        $this->assertSame('false', $powerball['isPending']);
        $this->assertSame('Pending', $powerball['pendingText']);

        $exampleRaffle = end($response['body']['lotteries']);
        $this->assertSame('raffle', $exampleRaffle['type']);
        $this->assertSame('$127,000', $exampleRaffle['jackpotFormatted']);
    }
}