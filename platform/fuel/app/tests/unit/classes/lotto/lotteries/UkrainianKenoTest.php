<?php

namespace Tests\Unit\Classes\Lotto\Lotteries;

use Carbon\Carbon;
use Helpers_App;
use Lotto_Lotteries_UkrainianKeno;
use Lotto_Scraperhtml;
use Test_Unit;

final class UkrainianKenoTest extends Test_Unit
{
    private Lotto_Lotteries_UkrainianKeno $keno;
    private Lotto_Scraperhtml $scraper;

    public function setUp(): void
    {
        parent::setUp();
        $this->keno = new class extends Lotto_Lotteries_UkrainianKeno
        {
            protected Carbon $providerNextDrawDate;

            public function __construct()
            {
                $this->providerNextDrawDate = Carbon::parse('2024-11-21 22:00:00', 'Europe/Kyiv');
            }

            public function getPrimaryJsonResults(): array
            {
                $json = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/ukrainiankeno-results-primary.json'));
                $data = json_decode($json, true);
                return $data;
            }
        };

        $this->scraper = new class extends Lotto_Scraperhtml
        {
            protected function fetchRawWebsite(string $url)
            {
                $this->rawHTML = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/ukrainiankeno-results-secondary.html'));
                $this->areaOfWorkHTML = $this->rawHTML;
            }
        };
    }

    /** @test */
    public function jackpotTest(): void
    {
        $expected = 0.375;
        $actual = Lotto_Lotteries_UkrainianKeno::LOTTERY_JACKPOT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersCountTest(): void
    {
        $expected = 20;
        $actual = Lotto_Lotteries_UkrainianKeno::LOTTERY_NUMBERS_COUNT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersRangeTest(): void
    {
        $expected = [1,80];
        $actual = Lotto_Lotteries_UkrainianKeno::LOTTERY_NUMBERS_RANGE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function providerTimezoneTest(): void
    {
        $expected = 'Europe/Kyiv';
        $actual = Lotto_Lotteries_UkrainianKeno::PROVIDER_TIMEZONE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getNumbersPrimaryTest(): void
    {
        $actual = $this->keno->getNumbersPrimary();
        sort($actual);
        $expected = [1, 2, 6, 7, 10, 16, 17, 21, 22, 30, 34, 37, 44, 45, 50, 51, 53, 59, 77, 78];

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function getNumbersSecondaryTest(): void
    {
        $scraper = $this->scraper->build('https://www.lotteryextreme.com/ukraine/keno-results', 0, $this->scraper);
        $expected = ['1', '2', '6', '7', '10', '16', '17', '21', '22', '30', '34', '37', '44', '45', '50', '51', '53', '59', '77', '78'];

        $actual = $this->keno->getNumbersSecondary($scraper);
        $this->assertSame($expected, $actual);
    }
}
