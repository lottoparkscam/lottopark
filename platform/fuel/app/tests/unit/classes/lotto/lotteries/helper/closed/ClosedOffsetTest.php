<?php

namespace Tests\Unit\Classes\Lotto\Lotteries\Helper\Closed;

use Tests\Unit\Lotto\Lotteries\Helper\Closed\ClosedTimeTestParent;

/**
 * This test will compare simple case, when timezone differs but it's still the same day
 */
final class ClosedOffsetTest extends ClosedTimeTestParent
{
    // NOTE: remember that in every offset below we have to include 1 hour static e.g. offset 3 sub 4 hours in total
    /** @test */
    public function justBeforeClosingTime__PositiveOffset(): void
    {
        $this->lottery_provider->offset = 3;
        $this->assertLotteryIsOpen("2021-02-03 18:58:59", "2021-02-03 22:59:00");
    }

    /** @test */
    public function exactClosingTime__PositiveOffset(): void
    {
        $this->lottery_provider->offset = 3;
        $this->assertLotteryIsClosed("2021-02-03 18:59:00", "2021-02-03 22:59:00");
    }

    /** @test */
    public function justBeforeClosingTime__PositiveOffsetBig(): void
    {
        $this->lottery_provider->offset = 33;
        $this->assertLotteryIsOpen("2021-02-02 12:58:59", "2021-02-03 22:59:00");
    }

    /** @test */
    public function exactClosingTime__PositiveOffsetBig(): void
    {
        $this->lottery_provider->offset = 33;
        $this->assertLotteryIsClosed("2021-02-02 12:59:00", "2021-02-03 22:59:00");
    }

    /** @test */
    public function justBeforeClosingTime__NegativeOffset(): void
    {
        $this->lottery_provider->offset = -3;
        $this->assertLotteryIsOpen("2021-02-04 00:58:59", "2021-02-03 22:59:00");
    }

    /** @test */
    public function exactClosingTime__NegativeOffset(): void
    {
        $this->lottery_provider->offset = -3;
        $this->assertLotteryIsClosed("2021-02-04 00:59:00", "2021-02-03 22:59:00");
    }

    /** @test */
    public function justBeforeClosingTime__NegativeOffsetBig(): void
    {
        $this->lottery_provider->offset = -33;
        $this->assertLotteryIsOpen("2021-02-05 06:58:59", "2021-02-03 22:59:00");
    }

    /** @test */
    public function exactClosingTime__NegativeOffsetBig(): void
    {
        $this->lottery_provider->offset = -33;
        $this->assertLotteryIsClosed("2021-02-05 06:59:00", "2021-02-03 22:59:00");
    }
}
