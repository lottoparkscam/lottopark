<?php

namespace Tests\Unit\Classes\Lotto\Lotteries\Helper\Draw;

final class DrawOzLottoTest extends LottoLotteriesDrawParent
{
    public function setUp(): void
    {
        $this->lottery = [
            'id' => 10,
            'draw_dates' => json_encode(["Tue 20:30"]),
            'timezone' => 'Australia/Melbourne',
        ];
    }

    /** @test */
    public function buyTicketInMonday_ExpectsTuesday(): void
    {
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-04-05 07:45:00", "2021-04-06 20:30:00");
    }

    /** @test */
    public function buyTicketInWednesday_ExpectsTuesday(): void
    {
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-04-07 07:45:00", "2021-04-13 20:30:00");
    }

    /** @test */
    public function buyTicketBeforeDraw_ExpectsTuesday(): void
    {
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-04-06 20:29:00", "2021-04-06 20:30:00");
    }

    /** @test */
    public function buyTicketBeforeDraw_ExpectsNextTuesday(): void
    {
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-04-06 20:31:00", "2021-04-13 20:30:00");
    }
}
