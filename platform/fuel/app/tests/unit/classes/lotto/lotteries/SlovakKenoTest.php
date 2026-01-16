<?php

namespace Tests\Unit\Classes\Lotto\Lotteries;

use Carbon\Carbon;
use Helpers_App;
use Lotto_Lotteries_SlovakKeno;
use Test_Unit;

final class SlovakKenoTest extends Test_Unit
{
    private Lotto_Lotteries_SlovakKeno $slovakkeno;

    public function setUp(): void
    {
        parent::setUp();
        $this->slovakkeno = new class extends Lotto_Lotteries_SlovakKeno
        {
            protected Carbon $providerNextDrawDate;

            public function __construct()
            {
                $this->providerNextDrawDate = Carbon::parse('2023-11-22 13:18:00', 'Europe/Bratislava')->setTimezone('UTC');
            }

            public function get_primary_json_results(): array
            {
                $json = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/slovakkeno-results-primary.json'));
                $data = json_decode($json, true);
                return $data['results'];
            }
        };
    }

    /** @test */
    public function jackpotTest(): void
    {
        $expected = 0.0072;
        $actual = Lotto_Lotteries_SlovakKeno::LOTTERY_JACKPOT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersCountTest(): void
    {
        $expected = 20;
        $actual = Lotto_Lotteries_SlovakKeno::LOTTERY_NUMBERS_COUNT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersRangeTest(): void
    {
        $expected = [1,80];
        $actual = Lotto_Lotteries_SlovakKeno::LOTTERY_NUMBERS_RANGE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function providerTimezoneTest(): void
    {
        $expected = 'UTC';
        $actual = Lotto_Lotteries_SlovakKeno::PROVIDER_TIMEZONE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getNumbersPrimaryTest(): void
    {
        $actual = $this->slovakkeno->get_numbers_primary();
        $expected = ['6', '10', '15', '25', '26', '32', '38', '40', '41', '44', '48', '50', '53', '56', '58', '60', '63', '64', '68', '79'];

        $this->assertEquals($expected, $actual);
    }
}
