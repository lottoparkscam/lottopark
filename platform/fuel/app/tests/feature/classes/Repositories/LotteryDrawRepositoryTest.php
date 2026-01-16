<?php

namespace Tests\Feature\Classes\Repositories;

use Carbon\Carbon;
use Helpers_Time;
use Models\Lottery;
use Models\LotteryDraw;
use Repositories\LotteryDrawRepository;
use Repositories\LotteryRepository;
use Test_Feature;

class LotteryDrawRepositoryTest extends Test_Feature
{
    private LotteryDrawRepository $lotteryDrawRepository;

    private Lottery $lottery;
    private string $lotteryDrawDate;
    private string $lotteryDrawDateTime;

    public function setUp(): void
    {
        parent::setUp();
        $this->lotteryDrawRepository = $this->container->get(LotteryDrawRepository::class);
        $lotteryRepository = $this->container->get(LotteryRepository::class);

        $this->lottery = $lotteryRepository->findOneBySlug('powerball');
        $this->createSomeFakeLotteryDraws();

        $this->lotteryDrawDate = '2029-03-05';
        $this->lotteryDrawDateTime = '202903052000';
    }

    /** @test */
    public function getLotteryDrawsByLotteryIdAndDrawDate_shouldReturnCorrectDateLocal(): void
    {
        $lotteryDraws = $this->lotteryDrawRepository->getLotteryDrawsByLotteryIdAndDrawDate(
            $this->lottery,
            $this->lotteryDrawDate,
            $this->lotteryDrawDateTime,
        );

        $lotteryDrawDateWithoutTime = Carbon::parse($lotteryDraws[0]['date_local'])
            ->format(Helpers_Time::DATE_FORMAT);
        $this->assertSame($this->lotteryDrawDate, $lotteryDrawDateWithoutTime);
    }

    /** @test */
    public function getLotteryDrawDateTimesForLotteryId_shouldReturnCorrectLotteryDrawDateTimes(): void
    {
        $lotteryDrawDateTimes = $this->lotteryDrawRepository->getLotteryDrawDateTimesForLotteryId(
            $this->lottery,
            $this->lotteryDrawDate
        );

        $this->assertIsArray($lotteryDrawDateTimes);
        $this->assertSame('2029-03-05 21:00', $lotteryDrawDateTimes[$this->lotteryDrawDate][0]);
        $this->assertSame('2029-03-05 20:00', $lotteryDrawDateTimes[$this->lotteryDrawDate][1]);
    }

    /** @test */
    public function getLotteryDrawDatesForLotteryId_shouldReturnCorrectLotteryDrawDates(): void
    {
        $lotteryDrawDates = $this->lotteryDrawRepository->getLotteryDrawDatesForLotteryId(
            $this->lottery->id,
        );

        $this->assertIsArray($lotteryDrawDates);
        $this->assertSame('2029-03-07', $lotteryDrawDates[0]);
        $this->assertSame('2029-03-05', $lotteryDrawDates[1]);
    }

    private function createSomeFakeLotteryDraws(): void
    {
        (new LotteryDraw([
            'lottery_id' => $this->lottery->id,
            'lottery_type_id' => 1,
            'date_download' => '2029-03-07 21:00:20',
            'date_local' => '2029-03-07 20:00:00',
            'jackpot' => 64.00000000,
            'numbers' => '2,38,49,64,81,89',
            'bnumbers' => '43',
            'total_prize' => 3721156.39,
            'total_winners' => 435867,
            'final_jackpot' => 0.00,
            'has_pending_tickets' => 1,
            'additional_data' => 'N;'
        ]))->save();

        (new LotteryDraw([
            'lottery_id' => $this->lottery->id,
            'lottery_type_id' => 1,
            'date_download' => '2029-03-05 21:00:20',
            'date_local' => '2029-03-05 20:00:00',
            'jackpot' => 34.00000000,
            'numbers' => '2,38,49,64,81,81',
            'bnumbers' => '43',
            'total_prize' => 3721156.39,
            'total_winners' => 435867,
            'final_jackpot' => 0.00,
            'has_pending_tickets' => 1,
            'additional_data' => 'N;'
        ]))->save();

        (new LotteryDraw([
            'lottery_id' => $this->lottery->id,
            'lottery_type_id' => 1,
            'date_download' => '2029-03-05 21:00:20',
            'date_local' => '2029-03-05 21:00:00',
            'jackpot' => 34.00000000,
            'numbers' => '2,38,49,64,81,81',
            'bnumbers' => '43',
            'total_prize' => 3721156.39,
            'total_winners' => 435867,
            'final_jackpot' => 0.00,
            'has_pending_tickets' => 1,
            'additional_data' => 'N;'
        ]))->save();
    }
}
