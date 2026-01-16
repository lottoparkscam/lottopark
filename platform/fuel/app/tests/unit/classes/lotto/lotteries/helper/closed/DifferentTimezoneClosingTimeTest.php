<?php

namespace Tests\Unit\Classes\Lotto\Lotteries\Helper\Closed;

use Tests\Unit\Lotto\Lotteries\Helper\Closed\ClosedTimeTestContract;
use Tests\Unit\Lotto\Lotteries\Helper\Closed\ClosedTimeTestParent;

/**
 * This test will compare simple case, when timezone differs but it's still the same day
 */
final class DifferentTimezoneClosingTimeTest extends ClosedTimeTestParent implements ClosedTimeTestContract
{
    public function setUp(): void
    {
        parent::setUp();
        // we have america new york zone in lottery. We shall test utc provider
        // we have the same time as in draw date, so we close lottery at exact moment of draw
        $this->lottery_provider->timezone = 'UTC';
        $this->lottery_provider->closing_time = '3:59:00';
    }

    /** @test */
    public function beforeClosingTimeLongBeforeDrawTime_LotteryIsOpen(): void
    {
        $this->assertLotteryIsOpen("2021-02-03 12:55:59", "2021-02-03 22:59:00");
    }

    /** @test */
    public function justBeforeClosingTime_LotteryIsOpen(): void
    {
        $this->assertLotteryIsOpen("2021-02-04 02:58:59", "2021-02-03 22:59:00");
    }

    /** @test */
    public function exactClosingTime_LotteryIsClosed(): void
    {
        $this->assertLotteryIsClosed("2021-02-04 02:59:00", "2021-02-03 22:59:00");
    }

    /** @test */
    public function closingTimeBeforeDrawTime_LotteryIsClosed(): void
    {
        $this->assertLotteryIsClosed("2021-02-04 04:58:59", "2021-02-03 22:59:00");
    }

    /** @test */
    public function closingTimeAfterDrawTime_LotteryIsClosed(): void
    {
        $this->assertLotteryIsClosed("2021-02-04 05:55:59", "2021-02-03 22:59:00");
    }

    /** @test */
    public function closingTimeLongAfterDrawTime_LotteryIsClosed(): void
    {
        $this->assertLotteryIsClosed("2021-02-05 23:55:59", "2021-02-03 22:59:00");
    }
}
