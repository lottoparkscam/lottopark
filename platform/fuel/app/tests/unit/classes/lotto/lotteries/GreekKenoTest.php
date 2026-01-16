<?php

namespace Tests\Unit\Classes\Lotto\Lotteries;

use Carbon\Carbon;
use Helpers_App;
use Lotto_Lotteries_GreekKeno;
use Test_Unit;

final class GreekKenoTest extends Test_Unit
{
    private Lotto_Lotteries_GreekKeno $greekkeno;

    public function setUp(): void
    {
        parent::setUp();
        $this->greekkeno = new class extends Lotto_Lotteries_GreekKeno
        {
            protected Carbon $providerNextDrawDate;

            public function __construct()
            {
                $this->providerNextDrawDate = Carbon::parse('2023-10-04 16:30:00', 'Europe/Athens')->setTimezone('Europe/Athens');
            }

            public function get_primary_json_results(): array
            {
                $json = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/greekkeno-results-primary.json'));
                return json_decode($json, true);
            }

            public function get_secondary_json_results(): array
            {
                $json = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/greekkeno-results-secondary.json'));
                $data = json_decode($json, true);
                return $data['content'];
            }
        };
    }

    /** @test */
    public function jackpotTest(): void
    {
        $expected = 1;
        $actual = Lotto_Lotteries_GreekKeno::LOTTERY_JACKPOT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersCountTest(): void
    {
        $expected = 20;
        $actual = Lotto_Lotteries_GreekKeno::LOTTERY_NUMBERS_COUNT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersRangeTest(): void
    {
        $expected = [1,80];
        $actual = Lotto_Lotteries_GreekKeno::LOTTERY_NUMBERS_RANGE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function providerTimezoneTest(): void
    {
        $expected = 'Europe/Athens';
        $actual = Lotto_Lotteries_GreekKeno::PROVIDER_TIMEZONE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getNumbersPrimaryTest(): void
    {
        $results = $this->greekkeno->get_primary_json_results();
        $actual = $this->greekkeno->process_results($results);
        $expected = [1, 2, 3, 9, 12, 17, 19, 29, 31, 32, 41, 46, 55, 56, 62, 69, 70, 72, 73, 76];

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function getNumbersSecondaryTest(): void
    {
        $results = $this->greekkeno->get_secondary_json_results();
        $actual = $this->greekkeno->process_results($results);
        $expected = [1, 2, 3, 9, 12, 17, 19, 29, 31, 32, 41, 46, 55, 56, 62, 69, 70, 72, 73, 76];

        $this->assertEquals($expected, $actual);
    }
}
