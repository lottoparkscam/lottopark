<?php

namespace Tests\Unit\Classes\Lotto\Lotteries;

use Carbon\Carbon;
use Helpers_App;
use Lotto_Lotteries_SlovakKeno10;
use Lotto_Scraperhtml;
use Test_Unit;

final class SlovakKeno10Test extends Test_Unit
{
    private Lotto_Lotteries_SlovakKeno10 $keno;
    private Lotto_Scraperhtml $scraper;

    public function setUp(): void
    {
        parent::setUp();
        $this->keno = new class extends Lotto_Lotteries_SlovakKeno10
        {
            protected Carbon $providerNextDrawDate;

            public function __construct()
            {
                $this->providerNextDrawDate = Carbon::parse('2024-11-21 18:00:00', 'Europe/Bratislava');
            }

            public function getPrimaryJsonResults(): array
            {
                $json = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/slovakkeno10.json'));
                $data = json_decode(json_decode($json, true), true);
                return $data;
            }
        };

        $this->scraper = new class extends Lotto_Scraperhtml
        {
            protected function fetchRawWebsite(string $url)
            {
                $this->rawHTML = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/slovakkeno10-results.html'));
                $this->areaOfWorkHTML = $this->rawHTML;
            }
        };
    }

    /** @test */
    public function jackpotTest(): void
    {
        $expected = 2;
        $actual = Lotto_Lotteries_SlovakKeno10::LOTTERY_JACKPOT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersCountTest(): void
    {
        $expected = 20;
        $actual = Lotto_Lotteries_SlovakKeno10::LOTTERY_NUMBERS_COUNT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersRangeTest(): void
    {
        $expected = [1,80];
        $actual = Lotto_Lotteries_SlovakKeno10::LOTTERY_NUMBERS_RANGE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function providerTimezoneTest(): void
    {
        $expected = 'Europe/Bratislava';
        $actual = Lotto_Lotteries_SlovakKeno10::PROVIDER_TIMEZONE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getNumbersPrimaryTest(): void
    {
        $actual = $this->keno->getNumbersPrimary();
        sort($actual);
        $expected = ['1', '4', '8', '15', '18', '28', '30', '31', '33', '34', '39', '58', '65', '69', '70', '72', '74', '75', '78', '79'];

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function getNumbersSecondaryTest(): void
    {
        $scraper = $this->scraper->build('https://www.lotteryextreme.com/slovakia/keno10-results', 0, $this->scraper);
        $expected = ['1', '4', '8', '15', '18', '28', '30', '31', '33', '34', '39', '58', '65', '69', '70', '72', '74', '75', '78', '79'];

        $actual = $this->keno->getNumbersSecondary($scraper);
        $this->assertSame($expected, $actual);
    }
}
