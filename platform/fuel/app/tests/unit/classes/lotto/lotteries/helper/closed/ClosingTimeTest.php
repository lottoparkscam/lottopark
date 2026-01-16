<?php

namespace Tests\Unit\Classes\Lotto\Lotteries\Helper\Closed;

use Tests\Unit\Lotto\Lotteries\Helper\Closed\ClosedTimeTestContract;
use Tests\Unit\Lotto\Lotteries\Helper\Closed\ClosedTimeTestParent;

/**
 * This test will compare simple case, when timezone differs but it's still the same day
 */
final class ClosingTimeTest extends ClosedTimeTestParent implements ClosedTimeTestContract
{
    public function setUp(): void
    {
        parent::setUp();
        // we need lottery timezone which is earlier than provider
        // NOTE: this test also covers case when provider day is lower than lottery day e.g. l 06 vs lp 05
        $this->lottery['id'] = 22; // we want to be considered 'special'
        $this->lottery_provider->timezone = 'America/New_York'; // +14 from timezone and +1 static in checks
        $this->lottery['timezone'] = 'Asia/Seoul';
        $this->lottery_provider->closing_times = json_encode([
            1 => '13:00',
            2 => '13:00',
            3 => '01:00', // this one will be processed
            4 => '13:00',
            5 => '13:00',
            6 => '13:00',
        ]);
    }

    /** @test */
    public function beforeClosingTimeLongBeforeDrawTime_LotteryIsOpen(): void
    {
        $this->assertLotteryIsOpen("2021-02-01 12:55:59", "2021-02-03 22:59:00");
    }

    /** @test */
    public function beforeClosingTimeLongBeforeDrawTimeAfterClosingTime_LotteryIsOpen(): void
    {
        $this->assertLotteryIsOpen("2021-02-01 14:55:59", "2021-02-03 22:59:00");
    }

    /** @test */
    public function beforeClosingTimeLongBeforeDrawTimeBeforeUnspecifiedClosingTime_LotteryIsOpen(): void
    {
        $this->assertLotteryIsOpen("2021-01-31 22:58:59", "2021-02-03 22:59:00");
    }

    /** @test */
    public function beforeClosingTimeLongBeforeDrawTimeAfterUnspecifiedClosingTime_LotteryIsOpen(): void
    {
        $this->assertLotteryIsOpen("2021-01-31 23:58:59", "2021-02-03 22:59:00");
    }

    /** @test */
    public function justBeforeClosingTime_LotteryIsOpen(): void
    {
        $this->assertLotteryIsOpen("2021-02-02 23:59:59", "2021-02-03 22:59:00");
    }

    /** @test */
    public function exactClosingTime_LotteryIsClosed(): void
    {
        $this->assertLotteryIsClosed("2021-02-03 00:00:00", "2021-02-03 22:59:00");
    }

    /** @test */
    public function closingTimeBeforeDrawTime_LotteryIsClosed(): void
    {
        $this->assertLotteryIsClosed("2021-02-03 07:58:59", "2021-02-03 22:59:00");
    }

    /** @test */
    public function closingTimeAfterDrawTime_LotteryIsClosed(): void
    {
        $this->assertLotteryIsClosed("2021-02-03 22:59:01", "2021-02-03 22:59:00");
    }

    /** @test */
    public function closingTimeLongAfterDrawTime_LotteryIsClosed(): void
    {
        $this->assertLotteryIsClosed("2021-02-05 23:55:59", "2021-02-03 22:59:00");
    }

    /** @test */
    public function closingTimesNotFound_FallBackToProviderClosingTimes(): void
    {
        // it shall fall back to provider.closing_time
        $this->assertLotteryIsClosed("2021-02-07 23:55:59", "2021-02-07 22:59:00");
    }
}
