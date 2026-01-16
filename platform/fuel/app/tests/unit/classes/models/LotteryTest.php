<?php

namespace Unit\Classes\Models;

use Models\Lottery;
use Test_Unit;

class LotteryTest extends Test_Unit
{
    /** @test */
    public function hasManyDrawsPerDay_true(): void
    {
        // Given
        $drawDates = [
            "Mon 12:00",
            "Tue 15:00",
            "Tue 16:00",
        ];

        // When
        $actual = Lottery::hasManyDrawsPerDay($drawDates);

        // Then
        $this->assertTrue($actual);
    }

    /** @test */
    public function hasManyDrawsPerDay_false(): void
    {
        // Given
        $drawDates = [
            "Mon 12:00",
            "Tue 16:00",
        ];

        // When
        $actual = Lottery::hasManyDrawsPerDay($drawDates);

        // Then
        $this->assertFalse($actual);
    }
}
