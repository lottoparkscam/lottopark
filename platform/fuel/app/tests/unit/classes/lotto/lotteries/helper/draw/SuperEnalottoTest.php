<?php

namespace Tests\Unit\Classes\Lotto\Lotteries\Helper\Draw;

final class DrawSuperEnalottoTest extends LottoLotteriesDrawParent
{
    public function setUp(): void
    {
        $this->lottery = [
            'id' => 4,
            'draw_dates' => json_encode(["Tue 20:00", "Thu 20:00", "Sat 20:00"]),
            'timezone' => 'Europe/Rome',
        ];
    }

    /** @test */
    public function beforeTueDrawSameDay_ExpectsTue(): void
    {
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-05-04 19:59:00", "2021-05-04 20:00:00");
    }

    /** @test */
    public function afterTueDraw_ExpectsThu(): void
    {
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-05-11 20:01:00", "2021-05-13 20:00:00");
    }

    /** @test */
    public function beforeThuDrawSameDay_ExpectsThu(): void
    {
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-05-06 19:59:00", "2021-05-06 20:00:00");
    }

    /** @test */
    public function afterThuDrawSameDay_ExpectsSat(): void
    {
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-05-06 20:01:00", "2021-05-08 20:00:00");
    }

    /** @test */
    public function buyMultiDrawTicket_ReturnCorrectDrawDate(): void
    {
        $this->assertNowToNextDrawDateCorrelationWithIteration($this->lottery, "2021-06-01 14:00:00", "2021-06-10 20:00:00", 5);
    }

    /** @test */
    public function buyMultiDrawTicket_WithUnscheuldedDraw_ReturnCorrectDrawDate(): void
    {
        $this->lottery['next_date_local'] = "2021-06-23 20:00:00";
        $this->lottery['last_date_local'] = "2021-06-15 20:00:00";
        $this->assertNowToNextDrawDateCorrelationWithIteration($this->lottery, "2021-06-24 14:00:00", "2021-07-03 20:00:00", 5);
    }

    /** @test */
    public function buyMultiDrawTicket_ReturnIncorrectDrawDate(): void
    {
        $this->assertFalseNowToNextDrawDateCorrelationWithIteration($this->lottery, "2021-06-01 14:00:00", "2021-06-11 20:00:00", 5);
    }

    /** @test */
    public function buyMultiDrawTicket_WithUnscheuldedDraw_ReturnIncorrectDrawDate(): void
    {
        $this->lottery['next_date_local'] = "2021-06-23 20:00:00";
        $this->lottery['last_date_local'] = "2021-06-15 20:00:00";
        $this->assertFalseNowToNextDrawDateCorrelationWithIteration($this->lottery, "2021-06-16 14:00:00", "2021-07-01 20:00:00", 5);
    }
}
