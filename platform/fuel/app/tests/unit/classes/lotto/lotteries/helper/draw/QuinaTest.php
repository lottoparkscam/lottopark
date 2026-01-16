<?php

use Carbon\Carbon;
use Tests\Unit\Classes\Lotto\Lotteries\Helper\Draw\LottoLotteriesDrawParent;

final class QuinaTest extends LottoLotteriesDrawParent
{
    public function setUp(): void
    {
        // NOTE: I dont care about delay - its' only for SuperEnalotto
        $this->lottery = [
            'id' => 23,
            'draw_dates' => json_encode(["Mon 20:00", "Tue 20:00", "Wed 20:00", "Thu 20:00", "Fri 20:00", "Sat 20:00"]),
            'last_date_local' => '2021-06-14 20:00:00',
            'next_date_local' => '2021-06-26 20:00:00',
            'timezone' => 'America/Sao_Paulo',
        ];
    }

    /** @test */
    public function ticketBuyAfterDraw_UnscheuldedDraw_ReturnCorrectDrawDate(): void
    {
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-06-15 14:00:00", "2021-06-26 20:00:00");
    }

    /** @test */
    public function buyMultiDrawTicket_WithUnscheuldedDraw_ReturnCorrectDrawDate(): void
    {
        $this->assertNowToNextDrawDateCorrelationWithIteration($this->lottery, "2021-06-15 14:00:00", "2021-07-01 20:00:00", 5);
    }

    /** @test */
    public function buyMultiDrawTicket_ReturnIncorrectDrawDate(): void
    {
        $this->assertFalseNowToNextDrawDateCorrelationWithIteration($this->lottery, "2021-07-02 14:00:00", "2021-07-08 20:30:00", 5);
    }

    /** @test */
    public function buyMultiDrawTicket_WithUnscheuldedDraw_ReturnIncorrectDrawDate(): void
    {
        $this->assertFalseNowToNextDrawDateCorrelationWithIteration($this->lottery, "2021-06-15 14:00:00", "2021-07-02 20:00:00", 5);
    }

    /**
     * @test
     * @group skipped
     * todo: this test calls db in Lotto_Helper:943 - refactor it or move to feature
     */
    public function returnOfTheDelayedDrawWidget(): void
    {
        Carbon::setTestNow(Carbon::parse("2021-06-15 14:00:00", $this->lottery['timezone']));
        $nextDrawDate = Lotto_Helper::get_lottery_real_next_draw($this->lottery, false);
        $this->assertSame($this->lottery['timezone'], (string) $nextDrawDate->timezone);
        $this->assertSame("2021-06-26 20:00:00", $nextDrawDate->format(Helpers_Time::DATETIME_FORMAT));
    }
}
