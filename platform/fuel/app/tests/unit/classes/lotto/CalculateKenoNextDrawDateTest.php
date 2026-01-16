<?php

namespace Tests\Unit\Classes\Lotto;

use Carbon\Carbon;
use Helpers_Time;
use Lotto_Helper;
use Test_Unit;

final class CalculateKenoNextDrawDateTest extends Test_Unit
{
    /**
     * @test
     * @dataProvider dateProvider
     */
    public function calculatesNextDrawDateCorrectly($lastDrawDate, $expectedNextDrawDate)
    {
        $lottery = [
            'id' => 38,
            'timezone' => 'Europe/Warsaw',
            'draw_dates' => json_encode(Helpers_Time::generateDrawDatesArray('6:34', 4, 261)),
            'next_date_local' => null,
        ];

        $lastDrawDate = Carbon::parse($lastDrawDate, $lottery['timezone']);
        $nextDrawDate = Lotto_Helper::get_lottery_next_draw($lottery, false, $lastDrawDate, 1);

        $this->assertEquals($expectedNextDrawDate, $nextDrawDate->format('Y-m-d H:i:s'));
    }

    public function dateProvider()
    {
        return [
            // last draw date | expected next draw date
            ['2023-09-08 14:54:00', '2023-09-08 14:58:00'],
            ['2023-09-08 23:54:00', '2023-09-09 06:34:00'],
            ['2023-09-09 06:34:00', '2023-09-09 06:38:00'],
            ['2023-09-10 23:54:00', '2023-09-11 06:34:00'],
            ['2023-09-13 08:34:00', '2023-09-13 08:38:00'],
            ['2023-09-17 23:54:00', '2023-09-18 06:34:00'],
            ['2023-09-29 23:54:00', '2023-09-30 06:34:00'],
            ['2023-09-30 23:54:00', '2023-10-01 06:34:00'],
            ['2023-10-01 23:54:00', '2023-10-02 06:34:00'],
            ['2023-10-02 23:54:00', '2023-10-03 06:34:00'],
            ['2023-12-31 23:54:00', '2024-01-01 06:34:00'],
        ];
    }
}
