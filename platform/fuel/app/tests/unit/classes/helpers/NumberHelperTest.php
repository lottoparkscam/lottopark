<?php

namespace Tests\Unit\Classes\Helpers;

use Helpers\NumberHelper;
use Test_Unit;

final class NumberHelperTest extends Test_Unit
{
    /** @test */
    public function addZeroBeforeIfLowerThanTen_shouldAdd(): void
    {
        $number = 1;
        $expected = '01';

        $this->assertSame($expected, NumberHelper::addZeroBeforeIfLowerThanTen($number));
    }

    /** @test */
    public function addZeroBeforeIfLowerThanTen_shouldNotAdd(): void
    {
        $number = 10;
        $expected = 10;

        $this->assertSame($expected, (int) NumberHelper::addZeroBeforeIfLowerThanTen($number));
    }

    /** @test */
    public function isFloatNumberNegative(): void
    {
        $this->assertTrue(NumberHelper::isFloatNumberNegative(-1.123123));
        $this->assertFalse(NumberHelper::isFloatNumberNegative(1.123123));
    }

    /**
     * @test
     * @dataProvider numbersToRoundUpProvider
     */
    public function roundUpWhenNumberAfterPrecisionIsBiggerThenZero(float $number, float $expectedResult, int $precision = 2): void
    {
        $result = NumberHelper::roundUpWhenNumberAfterPrecisionIsBiggerThenZero($number, $precision);
        $this->assertSame($expectedResult, $result);
    }

    public function numbersToRoundUpProvider(): array
    {
        return [
            [1.000, 1.00],
            [1.001, 1.01],
            [1.002, 1.01],
            [1.003, 1.01],
            [1.004, 1.01],
            [1.005, 1.01],
            [1.006, 1.01],
            [1.007, 1.01],
            [1.008, 1.01],
            [1.009, 1.01],
            [1.199, 1.20],
            [1.999, 2.00],
            [1.900001, 1.91],
            [5.421, 5.43],
            [-5.2412, -5.24],
            [5.42131, 5.43],
            [5.42131, 5.4214, 4],
            [5.42130, 5.4213, 4],
            [5.12312312334, 5.1231231234, 10],
        ];
    }
}
