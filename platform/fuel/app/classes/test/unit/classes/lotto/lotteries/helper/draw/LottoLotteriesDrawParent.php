<?php 

namespace Tests\Unit\Classes\Lotto\Lotteries\Helper\Draw;

use Carbon\Carbon;
use Helpers_Time;
use Lotto_Helper;

abstract class LottoLotteriesDrawParent extends \Test_Unit
{
    protected array $lottery;

    public function setUp(): void
    {
        $this->lottery = [
            'id' => 30,
            'draw_dates' => json_encode(["Wed 18:25", "Sat 19:25"]),
            'timezone' => 'Europe/Berlin',
        ];
    }
    
    /**
     * @param array $lottery
     * @param string $now
     * Date format "YYYY-mm-dd GG:ii:ss -- lottery Timezone"
     * @param string expectedNextDrawDate
     * Date format "YYYY-mm-dd GG:ii:ss -- lottery Timezone"
     * Example: today is 04.05 and draw is in 05.05, 07.05, 10.05 ect. $expectedNextDrawDate should be 05.05.
     */
    protected function assertNowToNextDrawDateCorrelation(array $lottery, string $now, string $expectedNextDrawDate): void
    {
        Carbon::setTestNow(Carbon::parse($now, $lottery['timezone']));
        $nextDrawDate = Lotto_Helper::get_lottery_next_draw($lottery, false);
        $this->assertSame($lottery['timezone'], (string) $nextDrawDate->timezone);
        $this->assertSame($expectedNextDrawDate, $nextDrawDate->format(Helpers_Time::DATETIME_FORMAT));
    }

    /**
     * @param array $lottery
     * @param string $now
     * Date format "YYYY-mm-dd GG:ii:ss -- lottery Timezone"
     * @param string expectedDrawDate
     * Date format "YYYY-mm-dd GG:ii:ss -- lottery Timezone"
     * @param int $iteration
     * $iteration it is a variable that returns another draw depending on the variable's value
     * Example: today is 04.05 and draw is in 05.05, 07.05, 10.05 ect. $iteration = 2, expectedDrawDate should be 07.05.
     */
    protected function assertNowToNextDrawDateCorrelationWithIteration(array $lottery, string $now, string $expectedDrawDate, int $iteration = 2): void
    {
        Carbon::setTestNow(Carbon::parse($now, $lottery['timezone']));
        $nextDrawDate = Lotto_Helper::get_lottery_next_draw($lottery, false, null, $iteration);
        $this->assertSame($lottery['timezone'], (string) $nextDrawDate->timezone);
        $this->assertSame($expectedDrawDate, $nextDrawDate->format(Helpers_Time::DATETIME_FORMAT));
    }

    protected function assertFalseNowToNextDrawDateCorrelationWithIteration(array $lottery, string $now, string $expectedDrawDate, int $iteration = 2): void
    {
        Carbon::setTestNow(Carbon::parse($now, $lottery['timezone']));
        $nextDrawDate = Lotto_Helper::get_lottery_next_draw($lottery, false, null, $iteration);
        $this->assertNotSame($expectedDrawDate, $nextDrawDate->format(Helpers_Time::DATETIME_FORMAT));
    }
}