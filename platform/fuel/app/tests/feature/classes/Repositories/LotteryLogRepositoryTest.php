<?php

namespace Tests\Feature\Classes\Repositories;

use Carbon\Carbon;
use Helpers_Time;
use Models\LotteryLog;
use Repositories\LotteryLogRepository;
use Test_Feature;

final class LotteryLogRepositoryTest extends Test_Feature
{
    private LotteryLogRepository $lotteryLogRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->lotteryLogRepository = $this->container->get(LotteryLogRepository::class);
    }

    /** @test */
    public function successLogNotExistsInTheLastHour_whenEmptyLotteryLog(): void
    {
        $actual = $this->lotteryLogRepository->successLogNotExistsInTheLastHour(1);
        $this->assertTrue($actual);
    }

    /** @test */
    public function successLogNotExistsInTheLastHour_whenSuccessWasMoreThanHourAgo(): void
    {
        $minuteAndHourAgo = Carbon::now(Helpers_Time::TIMEZONE)->subHour()->subMinute();
        $lotteryLog = new LotteryLog([
            'lottery_id' => 1,
            'type' => LotteryLog::TYPE_SUCCESS,
            'message' => 'Test',
            'date' => $minuteAndHourAgo->format(Helpers_Time::DATETIME_FORMAT),
        ]);
        $lotteryLog->save();

        $actual = $this->lotteryLogRepository->successLogNotExistsInTheLastHour(1);
        $this->assertTrue($actual);
    }

    /** @test */
    public function successLogNotExistsInTheLastHour_whenErrorExists(): void
    {
        $now = Carbon::now(Helpers_Time::TIMEZONE);
        $lotteryLog = new LotteryLog([
            'lottery_id' => 1,
            'type' => LotteryLog::TYPE_ERROR,
            'message' => 'Test',
            'date' => $now->format(Helpers_Time::DATETIME_FORMAT),
        ]);
        $lotteryLog->save();

        $actual = $this->lotteryLogRepository->successLogNotExistsInTheLastHour(1);
        $this->assertTrue($actual);
    }


    /** @test */
    public function successLogNotExistsInTheLastHour_whenSuccessExists(): void
    {
        $now = Carbon::now(Helpers_Time::TIMEZONE);
        $twoHoursAgo = $now->clone()->subHours(2);

        $oldLotteryLog = new LotteryLog([
            'lottery_id' => 1,
            'type' => LotteryLog::TYPE_SUCCESS,
            'message' => 'Test',
            'date' => $twoHoursAgo->format(Helpers_Time::DATETIME_FORMAT),
        ]);
        $oldLotteryLog->save();

        $lotteryLog = new LotteryLog([
            'lottery_id' => 1,
            'type' => LotteryLog::TYPE_SUCCESS,
            'message' => 'Test',
            'date' => $now->format(Helpers_Time::DATETIME_FORMAT),
        ]);
        $lotteryLog->save();

        $actual = $this->lotteryLogRepository->successLogNotExistsInTheLastHour(1);
        $this->assertFalse($actual);
    }
}
