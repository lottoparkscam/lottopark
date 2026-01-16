<?php

namespace Tests\Unit\Classes\Lotto\Lotteries\Helper\Draw;

use Carbon\Carbon;
use Lotto_Helper;

final class NextNextDrawTest extends LottoLotteriesDrawParent //{Vordis 2021-02-03 14:01:37} explode to monday & wed test
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
    public function beforeMonDrawSameDay_ExpectsWed(): void
    {
        $this->lottery['next_date_local'] = "2021-01-27 20:30:00";
        $this->assertNowToNextDrawDateCorrelationWithIteration($this->lottery, "2021-02-01 20:29:00", "2021-02-03 20:30:00");
    }

    /** @test */
    public function afterMonDrawSameDay_ExpectsMonNextWeek(): void
    {
        $this->assertNowToNextDrawDateCorrelationWithIteration($this->lottery, "2021-02-01 20:31:00", "2021-02-08 20:30:00");
    }

    /** @test */
    public function beforeWedDrawSameDay_ExpectsMonNextWeek(): void
    {
        $this->assertNowToNextDrawDateCorrelationWithIteration($this->lottery, "2021-02-03 20:29:00", "2021-02-08 20:30:00");
    }

    /** @test */
    public function afterWedDrawSameDay_ExpectsWedNextWeek(): void
    {
        $this->assertNowToNextDrawDateCorrelationWithIteration($this->lottery, "2021-02-03 20:31:00", "2021-02-10 20:30:00");
    }

    /** @test */
    public function nextDrawGeneration_ExpectsCorrectedIntervalBetweenDrawDates(): void
    {
        $this->lottery['next_date_local'] = "2021-01-27 20:30:00";
        $this->assertNowToNextDrawDateCorrelationWithIteration($this->lottery, "2021-02-01 20:29:00", $lastDrawDate = "2021-02-01 20:30:00", 1);
        $lastDrawDate = Carbon::parse($lastDrawDate, $this->lottery['timezone']);
        $diffInDaysFromMonToWed = 2;
        $diffInDaysFromWedToMon = 5;
        for ($i = 2; $i < 10; $i++) {
            $nextDrawDate = Lotto_Helper::get_lottery_next_draw($this->lottery, false, null, $i);
            $diffToLastDraw = $nextDrawDate->diffInDays($lastDrawDate);
            $expectedDiff = ($i % 2 === 0) ? $diffInDaysFromMonToWed : $diffInDaysFromWedToMon;
            $this->assertSame($expectedDiff, $diffToLastDraw);
            $lastDrawDate = $nextDrawDate;
        }
    }

    /** @test */
    public function futureNextDrawGeneration_ExpectsCorrectedIntervalBetweenDrawDates(): void
    {
        $this->lottery['next_date_local'] = "2031-02-03 20:30:00";
        $this->assertNowToNextDrawDateCorrelationWithIteration($this->lottery, "2031-02-03 20:29:00", $lastDrawDate = "2031-02-03 20:30:00", 1);
        $lastDrawDate = Carbon::parse($lastDrawDate, $this->lottery['timezone']);
        $diffInDaysFromMonToWed = 2;
        $diffInDaysFromWedToMon = 5;
        for ($i = 2; $i < 10; $i++) {
            $nextDrawDate = Lotto_Helper::get_lottery_next_draw($this->lottery, false, null, $i);
            $diffToLastDraw = $nextDrawDate->diffInDays($lastDrawDate);
            $expectedDiff = ($i % 2 === 0) ? $diffInDaysFromMonToWed : $diffInDaysFromWedToMon;
            $this->assertSame($expectedDiff, $diffToLastDraw);
            $lastDrawDate = $nextDrawDate;
        }
    }

    /** @test */
    public function futureNextDrawGenerationStartEndOfWeek_ExpectsCorrectedIntervalBetweenDrawDates(): void // TODO: {Vordis 2021-03-22 16:30:35} cleanup - DRY + probably one, more complicated is enough
    {
        $this->assertNowToNextDrawDateCorrelationWithIteration($this->lottery, "2031-01-31 20:29:00", $lastDrawDate = "2031-02-03 20:30:00", 1);
        $lastDrawDate = Carbon::parse($lastDrawDate, $this->lottery['timezone']);
        $diffInDaysFromMonToWed = 2;
        $diffInDaysFromWedToMon = 5;
        for ($i = 2; $i < 10; $i++) {
            $nextDrawDate = Lotto_Helper::get_lottery_next_draw($this->lottery, false, null, $i);
            $diffToLastDraw = $nextDrawDate->diffInDays($lastDrawDate);
            $expectedDiff = ($i % 2 === 0) ? $diffInDaysFromMonToWed : $diffInDaysFromWedToMon;
            $this->assertSame($expectedDiff, $diffToLastDraw);
            $lastDrawDate = $nextDrawDate;
        }
    }

    /** @test */
    public function oneDrawPerWeek_ExpectsSevenDaysInterval(): void
    {
        $this->lottery = [
            'id' => 13,
            'draw_dates' => json_encode(["Fri 21:00"]),
            'timezone' => 'Europe/Helsinki',
        ];

        $this->assertNowToNextDrawDateCorrelationWithIteration($this->lottery, "2021-03-05 20:59:59", $lastDrawDate = "2021-03-05 21:00:00", 1);
        $lastDrawDate = Carbon::parse($lastDrawDate, $this->lottery['timezone']);
        for ($i = 2; $i < 100; $i++) {
            $nextDrawDate = Lotto_Helper::get_lottery_next_draw($this->lottery, false, null, $i);
            $diffToLastDraw = $nextDrawDate->diffInDays($lastDrawDate);
            $expectedDiff = 7;
            $this->assertSame($expectedDiff, $diffToLastDraw);
            $lastDrawDate = $nextDrawDate;
        }
    }

    /** @test */
    public function daylightSaving_ExpectsNextDrawDate(): void // TODO: {Vordis 2021-03-01 20:07:45} move me to proper place
    {
        $this->lottery = [
            'id' => 13,
            'draw_dates' => json_encode(["Fri 21:00"]),
            'timezone' => 'Europe/Helsinki',
        ];

        $this->assertNowToNextDrawDateCorrelationWithIteration($this->lottery, "2021-03-27 20:59:59", "2021-04-02 21:00:00", 1);
    }

    /** @test */
    public function nextDrawGenerationFatDrawDates_Expects30MinutesIntervalBetweenDraws(): void
    {
        $this->lottery = [
            'id' => 13,
            'draw_dates' => json_encode(
                [
                    "Tue 17:00",
                    "Tue 18:00",
                    "Tue 18:30",
                    "Tue 19:00",
                    "Tue 19:30",
                    "Tue 20:00",
                    "Tue 20:30",
                    "Tue 21:00",
                    "Tue 21:30",
                    "Tue 22:00",
                    "Tue 22:30",
                    "Tue 23:00",
                    "Tue 23:30",
                    "Wed 00:00",
                    "Wed 00:30",
                    "Wed 01:00",
                    "Wed 01:30",
                    "Wed 02:00",
                    "Wed 02:30",
                    "Wed 03:00",
                    "Wed 03:30",
                    "Wed 04:00",
                    "Wed 04:30",
                    "Wed 05:00",
                    "Wed 05:30",
                    "Wed 06:00",
                    "Wed 06:30",
                    "Wed 07:00",
                    "Wed 07:30",
                    "Wed 08:00",
                    "Wed 08:30",
                    "Wed 09:00",
                    "Wed 09:30",
                    "Wed 10:00",
                    "Wed 10:30",
                    "Wed 11:00",
                    "Wed 11:30",
                    "Wed 12:00",
                    "Wed 12:30",
                    "Wed 13:00",
                    "Wed 13:30",
                    "Wed 14:00",
                    "Wed 14:30",
                    "Wed 15:00",
                    "Wed 15:30",
                    "Wed 16:00",
                    "Wed 16:30",
                    "Wed 17:00",
                    "Wed 17:30",
                    "Wed 18:00",
                    "Wed 18:30",
                    "Wed 19:00",
                    "Wed 19:30",
                    "Wed 20:00",
                    "Wed 20:30",
                    "Wed 21:00",
                    "Wed 21:30",
                    "Wed 22:00",
                    "Wed 22:30",
                    "Wed 23:00",
                    "Wed 23:30",
                    "Thu 00:00",
                    "Thu 00:30",
                    "Thu 01:00"
                ]
            ),
            'timezone' => 'Australia/Melbourne',
        ];
        $this->assertNowToNextDrawDateCorrelationWithIteration($this->lottery, "2021-02-16 17:59:00", $lastDrawDate = "2021-02-16 18:00:00", 1);
        $lastDrawDate = Carbon::parse($lastDrawDate, $this->lottery['timezone']);
        $diffInMinutes = 30;
        for ($i = 2; $i < 10; $i++) {
            $nextDrawDate = Lotto_Helper::get_lottery_next_draw($this->lottery, false, null, $i);
            $diffToLastDraw = $nextDrawDate->diffInMinutes($lastDrawDate);
            $expectedDiff = $diffInMinutes;
            $this->assertSame($expectedDiff, $diffToLastDraw);
            $lastDrawDate = $nextDrawDate;
        }
    }

    /** @test */
    public function nextDrawGenerationGivenDate_ExpectsCorrectedIntervalBetweenDrawDates(): void // TODO: {Vordis 2021-02-19 16:22:20} export from here to separate given date test
    {
        $this->lottery = [
            'id' => 4,
            'draw_dates' => json_encode(["Tue 20:00", "Thu 20:00", "Sat 20:00"]),
            'timezone' => 'Europe/Rome',
        ];
        $lastDrawDate = Carbon::parse("2021-01-30 20:00:00", $this->lottery['timezone']);
        $givenDate = Carbon::parse("2021-02-02 19:59:00", $this->lottery['timezone']);
        $diffInDaysFromSatToTue = 3;
        $diffInDaysOther = 2;
        for ($i = 1; $i < 10; $i++) {
            $nextDrawDate = Lotto_Helper::get_lottery_next_draw($this->lottery, false, $givenDate, $i);
            $diffToLastDraw = $nextDrawDate->diffInDays($lastDrawDate);
            $expectedDiff = ($i % 3 === 1) ? $diffInDaysFromSatToTue : $diffInDaysOther;
            $this->assertSame($expectedDiff, $diffToLastDraw);
            $lastDrawDate = $nextDrawDate;
        }
    }
}
