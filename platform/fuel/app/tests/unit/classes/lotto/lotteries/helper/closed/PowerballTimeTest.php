<?php

namespace Tests\Unit\Classes\Lotto\Lotteries\Helper\Closed;

use Tests\Unit\Lotto\Lotteries\Helper\Closed\ClosedTimeTestContract;
use Tests\Unit\Lotto\Lotteries\Helper\Closed\ClosedTimeTestParent;

final class PowerballTimeTest extends ClosedTimeTestParent implements ClosedTimeTestContract
{
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
        $this->assertLotteryIsClosed("2021-02-03 22:59:00", "2021-02-03 22:59:00");
    }

    /** @test */
    public function closingTimeBeforeDrawTime_LotteryIsClosed(): void
    {
        $this->assertLotteryIsClosed("2021-02-03 22:58:59", "2021-02-03 22:59:00");
    }

    /** @test */
    public function closingTimeAfterDrawTime_LotteryIsClosed(): void
    {
        $this->assertLotteryIsClosed("2021-02-03 23:55:59", "2021-02-03 22:59:00");
    }

    /** @test */
    public function closingTimeLongAfterDrawTime_LotteryIsClosed(): void
    {
        $this->assertLotteryIsClosed("2021-02-05 23:55:59", "2021-02-03 22:59:00");
    }
}
