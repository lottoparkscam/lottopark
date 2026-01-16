<?php

namespace Tests\Unit\Helpers;

use Carbon\Carbon;
use Helpers_Time;
use Test_Unit;

final class TimeHelperTest extends Test_Unit
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function isDayPassed(): void
    {
        $result = Helpers_Time::isDayPassed('2022-03-02 16:02:00');
        $this->assertTrue($result);
    }

    /** @test */
    public function isDayPassed_dateNow(): void
    {
        $result = Helpers_Time::isDayPassed(Carbon::now()->toDateTimeString());
        $this->assertFalse($result);
    }

    /** @test */
    public function isNotDayPassed(): void
    {
        $result = Helpers_Time::isDayNotPassed('2022-03-02 16:02:00');
        $this->assertFalse($result);
    }

    /** @test */
    public function isDayNotPassed_dateNow(): void
    {
        $result = Helpers_Time::isDayNotPassed(Carbon::now()->toDateTimeString());
        $this->assertTrue($result);
    }

    /** @test */
    public function isDateBefore_IsBeforeAndDatesHaveDifferentTimezones(): void
    {
        $mainDate = Carbon::parse('2022-03-02 16:02:00', 'Europe/Warsaw');
        $secondDate = Carbon::parse('2022-03-02 18:08:00', 'Europe/Istanbul');

        $this->assertTrue(Helpers_Time::isDateBeforeDate($mainDate, $secondDate));
    }

    /** @test */
    public function isDateBefore_IsBeforeAndDatesHaveTheSameTimezones(): void
    {
        $mainDate = Carbon::parse('2022-03-02 16:02:00', 'Europe/Warsaw');
        $secondDate = Carbon::parse('2022-03-02 16:02:01', 'Europe/Warsaw');

        $this->assertTrue(Helpers_Time::isDateBeforeDate($mainDate, $secondDate));
    }

    /** @test */
    public function isDateBefore_IsEqualShouldReturnFalse(): void
    {
        $timestamp = '2022-03-02 17:09:00';
        $mainDate = Carbon::parse($timestamp, 'Europe/Warsaw');
        $secondDate = Carbon::parse($timestamp, 'Europe/Warsaw');

        $this->assertFalse(Helpers_Time::isDateBeforeDate($mainDate, $secondDate));
    }

    /** @test */
    public function isDateBefore_IsAfterShouldReturnFalse(): void
    {
        $mainDate = Carbon::parse('2022-03-02 17:09:00', 'Europe/Warsaw');
        $secondDate = Carbon::parse('2022-03-01 17:09:00', 'Europe/Istanbul');

        $this->assertFalse(Helpers_Time::isDateBeforeDate($mainDate, $secondDate));
    }
}
