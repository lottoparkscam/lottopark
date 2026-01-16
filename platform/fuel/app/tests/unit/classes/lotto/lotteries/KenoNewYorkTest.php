<?php

namespace Tests\Unit\Classes\Lotto\Lotteries;

use Carbon\Carbon;
use Helpers_App;
use Lotto_Lotteries_KenoNewYork;
use Test_Unit;

final class KenoNewYorkTest extends Test_Unit
{
    private Lotto_Lotteries_KenoNewYork $keno;

    public function setUp(): void
    {
        parent::setUp();
        $this->keno = new class extends Lotto_Lotteries_KenoNewYork
        {
            protected Carbon $providerNextDrawDate;

            public function __construct()
            {
                $this->providerNextDrawDate = Carbon::parse('2024-08-06 10:32:00', 'America/New_York');
            }

            public function getPrimaryJsonResults(int $page): array
            {
                $json = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/kenonewyork-results-primary.json'));
                $data = json_decode($json, true);
                return $data['rows'];
            }
        };
    }

    /** @test */
    public function jackpotTest(): void
    {
        $expected = 1;
        $actual = Lotto_Lotteries_KenoNewYork::LOTTERY_JACKPOT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersCountTest(): void
    {
        $expected = 20;
        $actual = Lotto_Lotteries_KenoNewYork::LOTTERY_NUMBERS_COUNT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersRangeTest(): void
    {
        $expected = [1,80];
        $actual = Lotto_Lotteries_KenoNewYork::LOTTERY_NUMBERS_RANGE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function providerTimezoneTest(): void
    {
        $expected = 'America/New_York';
        $actual = Lotto_Lotteries_KenoNewYork::PROVIDER_TIMEZONE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getNumbersPrimaryTest(): void
    {
        $actual = $this->keno->getNumbersPrimary(0);
        sort($actual);
        $expected = ['4', '7', '24', '25', '35', '36', '38', '39', '41', '44', '49', '51', '56', '62', '63', '70', '71', '73', '75', '78'];

        $this->assertEquals($expected, $actual);
    }
}
