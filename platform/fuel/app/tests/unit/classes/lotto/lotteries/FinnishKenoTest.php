<?php

namespace Tests\Unit\Classes\Lotto\Lotteries;

use Carbon\Carbon;
use Helpers_App;
use Lotto_Lotteries_FinnishKeno;
use Test_Unit;

final class FinnishKenoTest extends Test_Unit
{
    private Lotto_Lotteries_FinnishKeno $finnishkeno;

    public function setUp(): void
    {
        parent::setUp();
        $this->finnishkeno = new class extends Lotto_Lotteries_FinnishKeno
        {
            protected Carbon $providerNextDrawDate;

            public function __construct()
            {
                $this->providerNextDrawDate = Carbon::parse('2023-09-17 15:00:00', 'Europe/Helsinki')->setTimezone('Europe/Helsinki');
            }

            public function get_primary_json_results(): array
            {
                $json = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/finnishkeno-results.json'));
                return json_decode($json, true);
            }
        };
    }

    /** @test */
    public function jackpotTest(): void
    {
        $expected = 2;
        $actual = Lotto_Lotteries_FinnishKeno::LOTTERY_JACKPOT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersCountTest(): void
    {
        $expected = 20;
        $actual = Lotto_Lotteries_FinnishKeno::LOTTERY_NUMBERS_COUNT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersRangeTest(): void
    {
        $expected = [1,70];
        $actual = Lotto_Lotteries_FinnishKeno::LOTTERY_NUMBERS_RANGE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function providerTimezoneTest(): void
    {
        $expected = 'Europe/Helsinki';
        $actual = Lotto_Lotteries_FinnishKeno::PROVIDER_TIMEZONE;
        $this->assertSame($expected, $actual);
    }

    public static function nextDrawTimeCases(): array
    {
        return [
            ['15:00', 'Keno Päiväarvonta'],
            ['20:58', 'Keno Ilta-arvonta'],
            ['23:00', 'Keno Myöhäisillan arvonta'],
        ];
    }

    /**
     * @test
     * @dataProvider nextDrawTimeCases
     */
    public function nextDrawTimeTest(string $input, string $expected): void
    {
        $actual = $this->finnishkeno->getNextDrawTime($input);
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function nextDrawTimeExceptionTest(): void
    {
        $this->expectExceptionMessage('finnish-keno - unable to get next draw time');
        $this->finnishkeno->getNextDrawTime('abc');
    }

    /** @test */
    public function getNumbersPrimaryTest(): void
    {
        $actual = $this->finnishkeno->get_numbers_primary();
        $expected = [1, 5, 6, 11, 21, 25, 27, 30, 32, 34, 39, 45, 50, 53, 59, 63, 66, 68, 69, 70];

        $this->assertEquals($expected, $actual);
    }
}
