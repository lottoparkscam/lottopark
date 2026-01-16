<?php

namespace Tests\Unit\Classes\Lotto\Lotteries\Helper\Closed;

use Tests\Unit\Lotto\Lotteries\Helper\Closed\ClosedTimeTestParent;
use Tests\Unit\Lotto\Lotteries\Helper\Closed\ClosedTimeTestContract;

/**
 * This test will compare simple case, when timezone differs but it's still the same day
 */
final class ClosedDifferentTimezoneWithDifferentTimeTest extends ClosedTimeTestParent implements ClosedTimeTestContract
{
    public function setUp(): void
    {
        parent::setUp();
        // we have america new york zone in lottery. We shall test utc
        // NOTE: we will check edge case, when we want to have lottery closed 5 hours before draw date time (+ 1 hour static)
        // NOTE: due to convoluted logic we cannot achieve that easily - the easiest way is to set utc time and use offset to move enough hours.
        // NOTE: If we need concrete minutes and seconds these should be stored in closing time
        $this->lottery_provider->timezone = 'UTC';
        $this->lottery_provider->closing_time = '03:59:00';
        $this->lottery_provider->offset = 5;
    }

    /** @test */
    public function beforeClosingTimeLongBeforeDrawTime_LotteryIsOpen(): void
    {
        $this->assertLotteryIsOpen("2021-02-03 12:55:59", "2021-02-03 22:59:00");
    }

    /** @test */
    public function justBeforeClosingTime_LotteryIsOpen(): void
    {
        $this->assertLotteryIsOpen("2021-02-03 21:58:59", "2021-02-03 22:59:00");
    }

    /** @test */
    public function exactClosingTime_LotteryIsClosed(): void
    {
        $this->assertLotteryIsClosed("2021-02-03 21:59:00", "2021-02-03 22:59:00");
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
