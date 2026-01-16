<?php

namespace Tests\Unit\Classes\Lotto\Lotteries;

use Carbon\Carbon;
use Helpers_App;
use Lotto_Lotteries_ItalianKeno;
use Test_Unit;

final class ItalianKenoTest extends Test_Unit
{
    private Lotto_Lotteries_ItalianKeno $italiankeno;

    public function setUp(): void
    {
        parent::setUp();
        $this->italiankeno = new class extends Lotto_Lotteries_ItalianKeno
        {
            protected Carbon $providerNextDrawDate;

            public function __construct()
            {
                $this->providerNextDrawDate = Carbon::parse('2024-06-04 00:00:00', 'Europe/Rome');
            }

            public function get_primary_json_results(): array
            {
                $json = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/italiankeno-results-primary.json'));
                $data = json_decode($json, true);
                return $data;
            }
        };
    }

    /** @test */
    public function jackpotTest(): void
    {
        $expected = 5;
        $actual = Lotto_Lotteries_ItalianKeno::LOTTERY_JACKPOT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersCountTest(): void
    {
        $expected = 20;
        $actual = Lotto_Lotteries_ItalianKeno::LOTTERY_NUMBERS_COUNT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersRangeTest(): void
    {
        $expected = [1,90];
        $actual = Lotto_Lotteries_ItalianKeno::LOTTERY_NUMBERS_RANGE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function providerTimezoneTest(): void
    {
        $expected = 'Europe/Rome';
        $actual = Lotto_Lotteries_ItalianKeno::PROVIDER_TIMEZONE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function calculateDrawNumber(): void
    {
        $actual = $this->italiankeno->calculateDrawNumber();
        $expected = 288;

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function getNumbersPrimaryTest(): void
    {
        $actual = $this->italiankeno->get_numbers_primary();
        $expected = ['6', '16', '18', '19', '30', '31', '42', '51', '55', '61', '68', '70', '71', '72', '74', '76', '78', '81', '82', '88'];

        $this->assertEquals($expected, $actual);
    }
}
