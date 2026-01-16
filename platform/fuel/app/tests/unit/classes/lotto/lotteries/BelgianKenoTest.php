<?php

namespace Tests\Unit\Classes\Lotto\Lotteries;

use Carbon\Carbon;
use Helpers_App;
use Lotto_Lotteries_BelgianKeno;
use Lotto_Scraperhtml;
use Test_Unit;

final class BelgianKenoTest extends Test_Unit
{
    private Lotto_Lotteries_BelgianKeno $keno;
    private Lotto_Scraperhtml $scraper;

    public function setUp(): void
    {
        parent::setUp();
        $this->keno = new class extends Lotto_Lotteries_BelgianKeno
        {
            protected Carbon $providerNextDrawDate;

            public function __construct()
            {
                $this->providerNextDrawDate = Carbon::parse('2024-11-21 20:00:00', 'Europe/Brussels');
            }

            public function getPrimaryJsonResults(): array
            {
                $json = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/belgiankeno-results-primary.json'));
                $data = json_decode($json, true);
                return $data;
            }
        };

        $this->scraper = new class extends Lotto_Scraperhtml
        {
            protected function fetchRawWebsite(string $url)
            {
                $this->rawHTML = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/belgiankeno-results-secondary.html'));
                $this->areaOfWorkHTML = $this->rawHTML;
            }
        };
    }

    /** @test */
    public function jackpotTest(): void
    {
        $expected = 2.5;
        $actual = Lotto_Lotteries_BelgianKeno::LOTTERY_JACKPOT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersCountTest(): void
    {
        $expected = 20;
        $actual = Lotto_Lotteries_BelgianKeno::LOTTERY_NUMBERS_COUNT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersRangeTest(): void
    {
        $expected = [1,70];
        $actual = Lotto_Lotteries_BelgianKeno::LOTTERY_NUMBERS_RANGE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function providerTimezoneTest(): void
    {
        $expected = 'Europe/Brussels';
        $actual = Lotto_Lotteries_BelgianKeno::PROVIDER_TIMEZONE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getNumbersPrimaryTest(): void
    {
        $actual = $this->keno->getNumbersPrimary();
        sort($actual);
        $expected = ['9', '20', '21', '24', '25', '27', '29', '30', '32', '34', '38', '40', '49', '53', '56', '58', '62', '65', '66', '67'];

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function getNumbersSecondaryTest(): void
    {
        $scraper = $this->scraper->build('https://www.lotteryextreme.com/belgium/keno-results', 0, $this->scraper);
        $expected = ['9', '20', '21', '24', '25', '27', '29', '30', '32', '34', '38', '40', '49', '53', '56', '58', '62', '65', '66', '67'];

        $actual = $this->keno->getNumbersSecondary($scraper);
        $this->assertSame($expected, $actual);
    }
}
