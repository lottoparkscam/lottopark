<?php

namespace Tests\Unit\Classes\Lotto\Lotteries;

use Carbon\Carbon;
use Lotto_Lotteries_NorwegianKeno;
use Test_Unit;

final class NorwegianKenoTest extends Test_Unit
{
    private Lotto_Lotteries_NorwegianKeno $keno;

    public function setUp(): void
    {
        parent::setUp();
        $this->keno = new class extends Lotto_Lotteries_NorwegianKeno
        {
            protected Carbon $providerNextDrawDate;

            public function __construct()
            {
                $this->providerNextDrawDate = Carbon::parse('2024-12-12 20:30:00', 'Europe/Oslo');
            }
        };
    }

    /** @test */
    public function jackpotTest(): void
    {
        $expected = 2;
        $actual = Lotto_Lotteries_NorwegianKeno::LOTTERY_JACKPOT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersCountTest(): void
    {
        $expected = 20;
        $actual = Lotto_Lotteries_NorwegianKeno::LOTTERY_NUMBERS_COUNT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersRangeTest(): void
    {
        $expected = [1,70];
        $actual = Lotto_Lotteries_NorwegianKeno::LOTTERY_NUMBERS_RANGE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function providerTimezoneTest(): void
    {
        $expected = 'Europe/Oslo';
        $actual = Lotto_Lotteries_NorwegianKeno::PROVIDER_TIMEZONE;
        $this->assertSame($expected, $actual);
    }
}
