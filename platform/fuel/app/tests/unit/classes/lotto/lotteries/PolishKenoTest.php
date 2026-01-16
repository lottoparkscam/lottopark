<?php

namespace Tests\Unit\Classes\Lotto\Lotteries;

use Carbon\Carbon;
use Helpers_App;
use Lotto_Lotteries_PolishKeno;
use Test_Unit;

final class PolishKenoTest extends Test_Unit
{
    private Lotto_Lotteries_PolishKeno $polishkeno;

    public function setUp(): void
    {
        parent::setUp();
        $this->polishkeno = new class extends Lotto_Lotteries_PolishKeno
        {
            protected Carbon $providerNextDrawDate;

            public function __construct()
            {
                $this->providerNextDrawDate = Carbon::parse('2024-05-06 17:18:00', 'Europe/Warsaw')->setTimezone('Europe/Bucharest');
            }

            public function get_primary_json_results(): array
            {
                $json = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/polishkeno-results-primary.json'));
                return json_decode($json, true);
            }

            public function get_secondary_json_results(): array
            {
                $json = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/polishkeno-results-secondary.json'));
                $data = json_decode($json, true);
                return $data['data'];
            }
        };
    }

    /** @test */
    public function jackpotTest(): void
    {
        $expected = 0.5;
        $actual = Lotto_Lotteries_PolishKeno::LOTTERY_JACKPOT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersCountTest(): void
    {
        $expected = 20;
        $actual = Lotto_Lotteries_PolishKeno::LOTTERY_NUMBERS_COUNT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersRangeTest(): void
    {
        $expected = [1,70];
        $actual = Lotto_Lotteries_PolishKeno::LOTTERY_NUMBERS_RANGE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function providerTimezoneTest(): void
    {
        $expected = 'Europe/Bucharest';
        $actual = Lotto_Lotteries_PolishKeno::PROVIDER_TIMEZONE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getNumbersPrimaryTest(): void
    {
        $actual = $this->polishkeno->get_numbers_primary();
        $expected = ['3', '8', '9', '21', '23', '24', '25', '29', '30', '33', '34', '35', '38', '41', '43', '52', '62', '63', '67', '69'];

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function getNumbersSecondaryTest(): void
    {
        $actual = $this->polishkeno->get_numbers_secondary();
        $expected = ['3', '8', '9', '21', '23', '24', '25', '29', '30', '33', '34', '35', '38', '41', '43', '52', '62', '63', '67', '69'];

        $this->assertEquals($expected, $actual);
    }
}
