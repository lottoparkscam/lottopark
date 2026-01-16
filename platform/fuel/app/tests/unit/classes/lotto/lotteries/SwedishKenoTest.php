<?php

namespace Tests\Unit\Classes\Lotto\Lotteries;

use Carbon\Carbon;
use Helpers_App;
use Lotto_Lotteries_SwedishKeno;
use Lotto_Scraperhtml;
use Test_Unit;

final class SwedishKenoTest extends Test_Unit
{
    private Lotto_Lotteries_SwedishKeno $keno;
    private Lotto_Scraperhtml $scraper;

    public function setUp(): void
    {
        parent::setUp();
        $this->keno = new class extends Lotto_Lotteries_SwedishKeno
        {
            protected Carbon $providerNextDrawDate;

            public function __construct()
            {
                $this->providerNextDrawDate = Carbon::parse('2024-11-18 19:00:00', 'Europe/Stockholm');
            }

            public function getPrimaryJsonResults(): array
            {
                $json = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/swedishkeno-results-primary.json'));
                $data = json_decode($json, true);
                return $data;
            }
        };

        $this->scraper = new class extends Lotto_Scraperhtml
        {
            protected function fetchRawWebsite(string $url)
            {
                $this->rawHTML = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/swedishkeno-results-secondary.html'));
                $this->areaOfWorkHTML = $this->rawHTML;
            }
        };
    }

    /** @test */
    public function jackpotTest(): void
    {
        $expected = 3.5;
        $actual = Lotto_Lotteries_SwedishKeno::LOTTERY_JACKPOT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersCountTest(): void
    {
        $expected = 20;
        $actual = Lotto_Lotteries_SwedishKeno::LOTTERY_NUMBERS_COUNT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersRangeTest(): void
    {
        $expected = [1,70];
        $actual = Lotto_Lotteries_SwedishKeno::LOTTERY_NUMBERS_RANGE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function providerTimezoneTest(): void
    {
        $expected = 'Europe/Stockholm';
        $actual = Lotto_Lotteries_SwedishKeno::PROVIDER_TIMEZONE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getDrawNumberTest(): void
    {
        $actual = $this->keno->getDrawNumber();
        $expected = 11647;

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function getNumbersPrimaryTest(): void
    {
        $actual = $this->keno->getNumbersPrimary();
        sort($actual);
        $expected = [2, 5, 7, 15, 22, 23, 24, 27, 31, 37, 41, 42, 44, 45, 49, 50, 52, 56, 64, 69];

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function getNumbersSecondaryTest(): void
    {
        $scraper = $this->scraper->build('https://www.lotteryextreme.com/belgium/keno-results', 0, $this->scraper);
        $expected = ['2', '5', '7', '15', '22', '23', '24', '27', '31', '37', '41', '42', '44', '45', '49', '50', '52', '56', '64', '69'];

        $actual = $this->keno->getNumbersSecondary($scraper);
        $this->assertSame($expected, $actual);
    }
}
