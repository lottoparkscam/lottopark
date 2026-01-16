<?php

namespace Tests\Unit\Classes\Lotto\Lotteries;

use Carbon\Carbon;
use Helpers_App;
use Lotto_Lotteries_CzechKeno;
use Test_Unit;

final class CzechKenoTest extends Test_Unit
{
    private Lotto_Lotteries_CzechKeno $czechkeno;

    public function setUp(): void
    {
        parent::setUp();
        $this->czechkeno = new class extends Lotto_Lotteries_CzechKeno
        {
            protected Carbon $providerNextDrawDate;

            public function __construct()
            {
                $this->providerNextDrawDate = Carbon::parse('2023-08-28 13:00:00', 'Europe/Prague')->setTimezone('Europe/Prague');
            }

            public function get_primary_json_ids(): array
            {
                $json = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/czechkeno-ids.json'));
                return json_decode($json, true);
            }

            public function get_primary_json_results(int $drawId): array
            {
                $json = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/czechkeno-results-primary.json'));
                return json_decode($json, true);
            }

            public function get_secondary_json_results(int $page): array
            {
                $json = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/czechkeno-results-secondary.json'));
                $data = json_decode($json, true);
                return $data['results'];
            }
        };
    }

    /** @test */
    public function jackpotTest(): void
    {
        $expected = 0.05;
        $actual = Lotto_Lotteries_CzechKeno::LOTTERY_JACKPOT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersCountTest(): void
    {
        $expected = 12;
        $actual = Lotto_Lotteries_CzechKeno::LOTTERY_NUMBERS_COUNT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersRangeTest(): void
    {
        $expected = [1,60];
        $actual = Lotto_Lotteries_CzechKeno::LOTTERY_NUMBERS_RANGE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function providerTimezoneTest(): void
    {
        $expected = 'Europe/Prague';
        $actual = Lotto_Lotteries_CzechKeno::PROVIDER_TIMEZONE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getNumbersPrimaryTest(): void
    {
        $actual = $this->czechkeno->get_numbers_primary();
        $expected = [3,4,7,33,35,37,41,43,48,49,52,55];

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function getNumbersSecondaryTest(): void
    {
        $actual = $this->czechkeno->get_numbers_secondary();
        $expected = [3,4,7,33,35,37,41,43,48,49,52,55];

        $this->assertEquals($expected, $actual);
    }
}
