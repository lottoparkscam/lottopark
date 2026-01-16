<?php

namespace Tests\Unit\Classes\Helpers;

use Helpers_Time;
use Test_Unit;

final class TimeTest extends Test_Unit
{
    /** @test */
    public function drawDateToDrawDays_TwoInTheSameDay_StillReturnsTwo(): void
    {
        $draw_dates = ['Mon 16:00', "Mon 18:00", "Wed 23:00"];

        $draw_days = Helpers_Time::drawDateToDrawDays($draw_dates);
        $this->assertSame([1,3], $draw_days);
    }

    /** @test */
    public function getTimestampBeforeXMinutes_ShouldReturnSame(): void
    {
        $getTimestampBeforeXMinutes = 30;
        $timezone = 'UTC';
        $timestampToTest = '2021-11-02 14:30:00';
        $validReturnForProvidedMinutes = '2021-11-02 14:00:00';

        $receivedTimestamp = Helpers_Time::getTimestampBeforeXMinutes($getTimestampBeforeXMinutes, $timezone, $timestampToTest);
        $this->assertSame($receivedTimestamp, $validReturnForProvidedMinutes);
    }

    /** @test */
    public function generateMultipleDrawsPerDayJsonTest(): void
    {
        $expected = '["Mon 07:30","Mon 19:30","Tue 07:30","Tue 19:30","Sun 07:30","Sun 19:30"]';

        $days = [1,2,7];
        $times = ['7:30', '19:30'];
        $actual = Helpers_Time::generateMultipleDrawsPerDayJson($days, $times);

        $this->assertSame($expected, $actual);
    }
}
