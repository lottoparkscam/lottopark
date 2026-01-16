<?php

namespace Tests\Unit\Classes\Lotto\Lotteries\Helper\Draw;

final class Draw6Aus49Test extends LottoLotteriesDrawParent
{
    /** @test */
    public function buyTicketInTuesdayMorning_ExpectsWed(): void
    {
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-04-06 07:45:00", "2021-04-07 18:25:00");
    }

    /** @test */
    public function buyTicketInTuesdayAfternoon_ExpectsWed(): void
    {
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-04-06 17:45:00", "2021-04-07 18:25:00");
    }

    /** @test */
    public function buyTicketBeforeDrawInWed_ExpectsWed(): void
    {
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-04-07 18:20:00", "2021-04-07 18:25:00");
    }

    /** @test */
    public function buyTicketInMondayMorning_ExpectsWed(): void
    {
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-04-05 08:25:00", "2021-04-07 18:25:00");
    }

    /** @test */
    public function buyTicketAfterWedDraw_ExpectsSat(): void
    {
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-04-07 18:35:00", "2021-04-10 19:25:00");
    }

    /** @test */
    public function buyTicketInThursdayMorning_ExpectsSat(): void
    {
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-04-08 07:35:00", "2021-04-10 19:25:00");
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-04-08 08:17:00", "2021-04-10 19:25:00");
    }

    /** @test */
    public function buyTicketInFridayMorning_ExpectsSat(): void
    {
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-04-09 07:35:00", "2021-04-10 19:25:00");
    }

    /** @test */
    public function buyTicketInSaturdayMorning_ExpectsSat(): void
    {
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-04-10 07:35:00", "2021-04-10 19:25:00");
    }

    /** @test */
    public function buyTicketBeforeDrawInSat_ExpectsSat(): void
    {
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-04-10 19:20:00", "2021-04-10 19:25:00");
    }

    /** @test */
    public function buyTicketAfterSatDraw_ExpectsWed(): void
    {
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-04-10 19:35:00", "2021-04-14 18:25:00");
    }

    /** @test */
    public function buyMultiDrawTicket_ReturnCorrectDrawDate(): void
    {
        $this->assertNowToNextDrawDateCorrelationWithIteration($this->lottery, "2021-06-01 14:00:00", "2021-06-16 18:25:00", 5);
    }

    /** @test */
    public function buyMultiDrawTicket_WithUnscheuldedDraw_ReturnCorrectDrawDate(): void
    {
        $this->lottery['next_date_local'] = "2021-06-23 18:25:00";
        $this->lottery['last_date_local'] = "2021-06-12 19:25:00";
        $this->assertNowToNextDrawDateCorrelationWithIteration($this->lottery, "2021-06-15 14:00:00", "2021-07-07 18:25:00", 5);
    }

    /** @test */
    public function buyMultiDrawTicket_ReturnIncorrectDrawDate(): void
    {
        $this->assertFalseNowToNextDrawDateCorrelationWithIteration($this->lottery, "2021-06-01 14:00:00", "2021-06-16 19:25:00", 5);
    }

    /** @test */
    public function buyMultiDrawTicket_WithUnscheuldedDraw_ReturnIncorrectDrawDate(): void
    {
        $this->lottery['next_date_local'] = "2021-06-23 18:25:00";
        $this->lottery['last_date_local'] = "2021-06-12 19:25:00";
        $this->assertFalseNowToNextDrawDateCorrelationWithIteration($this->lottery, "2021-06-15 14:00:00", "2021-07-10 18:25:00", 5);
    }
}
