<?php

namespace Tests\Unit\Lotto\Lotteries\Helper\Closed;

interface ClosedTimeTestContract
{
    public function beforeClosingTimeLongBeforeDrawTime_LotteryIsOpen(): void;

    public function justBeforeClosingTime_LotteryIsOpen(): void;

    public function exactClosingTime_LotteryIsClosed(): void;

    public function closingTimeBeforeDrawTime_LotteryIsClosed(): void;

    public function closingTimeAfterDrawTime_LotteryIsClosed(): void;

    public function closingTimeLongAfterDrawTime_LotteryIsClosed(): void;
}