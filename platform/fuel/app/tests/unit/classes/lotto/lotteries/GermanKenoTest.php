<?php

namespace Tests\Unit\Classes\Lotto\Lotteries;

use Carbon\Carbon;
use Helpers_App;
use Lotto_Lotteries_GermanKeno;
use Lotto_Scraperhtml;
use Test_Unit;

final class GermanKenoTest extends Test_Unit
{
    private Lotto_Lotteries_GermanKeno $keno;
    private Lotto_Scraperhtml $scraper;

    public function setUp(): void
    {
        parent::setUp();
        $this->keno = new class extends Lotto_Lotteries_GermanKeno
        {
            protected Carbon $providerNextDrawDate;

            public function __construct()
            {
                $this->providerNextDrawDate = Carbon::parse('2024-12-02 19:10:00', 'Europe/Berlin');
            }

            public function getPrimaryJsonResults(): array
            {
                $json = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/germankeno-results-primary.json'));
                $data = json_decode($json, true);
                return $data;
            }
        };

        $this->scraper = new class extends Lotto_Scraperhtml
        {
            protected function fetchRawWebsite(string $url)
            {
                $this->rawHTML = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/germankeno-results-secondary.html'));
                $this->areaOfWorkHTML = $this->rawHTML;
            }
        };
    }

    /** @test */
    public function jackpotTest(): void
    {
        $expected = 1;
        $actual = Lotto_Lotteries_GermanKeno::LOTTERY_JACKPOT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersCountTest(): void
    {
        $expected = 20;
        $actual = Lotto_Lotteries_GermanKeno::LOTTERY_NUMBERS_COUNT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersRangeTest(): void
    {
        $expected = [1,70];
        $actual = Lotto_Lotteries_GermanKeno::LOTTERY_NUMBERS_RANGE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function providerTimezoneTest(): void
    {
        $expected = 'Europe/Berlin';
        $actual = Lotto_Lotteries_GermanKeno::PROVIDER_TIMEZONE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getNumbersPrimaryTest(): void
    {
        $actual = $this->keno->getNumbersPrimary();
        sort($actual);
        $expected = ['9', '17', '20', '23', '28', '35', '38', '41', '42', '46', '48', '52', '53', '54', '57', '60', '61', '62', '63', '67'];

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function getNumbersSecondaryTest(): void
    {
        $scraper = $this->scraper->build('https://www.lotteryextreme.com/germany-keno/results', 0, $this->scraper);
        $expected = ['9', '17', '20', '23', '28', '35', '38', '41', '42', '46', '48', '52', '53', '54', '57', '60', '61', '62', '63', '67'];

        $actual = $this->keno->getNumbersSecondary($scraper);
        $this->assertSame($expected, $actual);
    }
}
