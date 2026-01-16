<?php

namespace Tests\Unit\Classes\Lotto\Lotteries\Helper\Draw;

use Tests\Unit\Classes\Lotto\Lotteries\Helper\Draw\LottoLotteriesDrawParent;

final class MonWedTest extends LottoLotteriesDrawParent
{
    protected array $lottery;

    public function setUp(): void
    {
        // NOTE: I dont care about delay - its' only for SuperEnalotto
        $this->lottery = [
            'id' => 13,
            'draw_dates' => json_encode(["Mon 20:30", "Wed 20:30"]),
            'timezone' => 'Australia/Melbourne',
        ];
    }

    /** @test */
    public function beforeMonDrawSameDay_ExpectsMon(): void
    {
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-02-01 20:29:00", "2021-02-01 20:30:00");
    }

    /** @test */
    public function afterMonDrawSameDay_ExpectsWed(): void
    {
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-02-01 20:31:00", "2021-02-03 20:30:00");
    }

    /** @test */
    public function beforeWedDrawSameDay_ExpectsWed(): void
    {
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-02-03 20:29:00", "2021-02-03 20:30:00");
    }

    /** @test */
    public function afterWedDrawSameDay_ExpectsMonNextWeek(): void
    {
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-02-03 20:31:00", "2021-02-08 20:30:00");
    }

    /** @test */
    public function buyMultiDrawTicket_ReturnCorrectDrawDate(): void
    {
        $this->assertNowToNextDrawDateCorrelationWithIteration($this->lottery, "2021-06-01 14:00:00", "2021-06-16 20:30:00", 5);
    }

    /** @test */
    public function buyMultiDrawTicket_WithUnscheuldedDraw_ReturnCorrectDrawDate(): void
    {
        $this->lottery['next_date_local'] = "2021-06-22 20:30:00";
        $this->lottery['last_date_local'] = "2021-06-14 20:30:00";
        $this->assertNowToNextDrawDateCorrelationWithIteration($this->lottery, "2021-06-15 14:00:00", "2021-07-07 20:30:00", 5);
    }

    /** @test */
    public function buyMultiDrawTicket_WithUnscheuldedDrawAndBeforeChangedDSTWinterTime_ReturnCorrectDrawDate(): void
    {
        $this->lottery['next_date_local'] = "2021-04-07 20:30:00";
        $this->lottery['last_date_local'] = "2021-03-31 20:30:00";
        $this->assertNowToNextDrawDateCorrelationWithIteration($this->lottery, "2021-04-01 14:00:00", "2021-04-21 20:30:00", 5);
    }

    /** @test */
    public function buyMultiDrawTicket_WithUnscheuldedDrawAndBeforeChangedDSTSummerTime_ReturnCorrectDrawDate(): void
    {
        $this->lottery['next_date_local'] = "2021-10-06 20:30:00";
        $this->lottery['last_date_local'] = "2021-09-29 20:30:00";
        $this->assertNowToNextDrawDateCorrelationWithIteration($this->lottery, "2021-10-01 14:00:00", "2021-10-20 20:30:00", 5);
    }

    /** @test */
    public function buyMultiDrawTicket_ReturnIncorrectDrawDate(): void
    {
        $this->assertFalseNowToNextDrawDateCorrelationWithIteration($this->lottery, "2021-06-01 14:00:00", "2021-06-21 20:30:00", 5);
    }

    /** @test */
    public function buyMultiDrawTicket_WithUnscheuldedDraw_ReturnIncorrectDrawDate(): void
    {
        $this->lottery['next_date_local'] = "2021-06-22 20:30:00";
        $this->lottery['last_date_local'] = "2021-06-14 20:30:00";
        $this->assertFalseNowToNextDrawDateCorrelationWithIteration($this->lottery, "2021-06-15 14:00:00", "2021-07-05 20:30:00", 5);
    }
}
