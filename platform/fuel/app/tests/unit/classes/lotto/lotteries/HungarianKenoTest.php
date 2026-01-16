<?php

namespace Tests\Unit\Classes\Lotto\Lotteries;

use Carbon\Carbon;
use Helpers_App;
use Lotto_Lotteries_HungarianKeno;
use Lotto_Scraperhtml;
use Test_Unit;

final class HungarianKenoTest extends Test_Unit
{
    private Lotto_Lotteries_HungarianKeno $hungariankeno;
    private Lotto_Scraperhtml $scraperPrimary;
    private Lotto_Scraperhtml $scraperSecondary;

    public function setUp(): void
    {
        parent::setUp();
        $this->hungariankeno = new class extends Lotto_Lotteries_HungarianKeno
        {
            protected Carbon $providerNextDrawDate;

            public function __construct()
            {
                $this->providerNextDrawDate = Carbon::parse('2024-11-25 20:00:00', 'Europe/Budapest')->setTimezone('Europe/Budapest');
            }
        };

        $this->scraperPrimary = new class extends Lotto_Scraperhtml
        {
            protected function fetchRawWebsite(string $url)
            {
                $this->rawHTML = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/hungariankeno-results-primary.html'));
                $this->areaOfWorkHTML = $this->rawHTML;
            }
        };

        $this->scraperSecondary = new class extends Lotto_Scraperhtml
        {
            protected function fetchRawWebsite(string $url)
            {
                $this->rawHTML = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/hungariankeno-results-secondary.html'));
                $this->areaOfWorkHTML = $this->rawHTML;
            }
        };
    }

    /** @test */
    public function jackpotTest(): void
    {
        $expected = 7.5;
        $actual = Lotto_Lotteries_HungarianKeno::LOTTERY_JACKPOT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersCountTest(): void
    {
        $expected = 20;
        $actual = Lotto_Lotteries_HungarianKeno::LOTTERY_NUMBERS_COUNT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersRangeTest(): void
    {
        $expected = [1, 80];
        $actual = Lotto_Lotteries_HungarianKeno::LOTTERY_NUMBERS_RANGE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function providerTimezoneTest(): void
    {
        $expected = 'Europe/Budapest';
        $actual = Lotto_Lotteries_HungarianKeno::PROVIDER_TIMEZONE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getNumbersPrimaryTest(): void
    {
        $scraper = $this->scraperPrimary->build('https://bet.szerencsejatek.hu/cmsfiles/keno.html', 0, $this->scraperPrimary);
        $expected = [5, 6, 8, 10, 11, 13, 14, 16, 18, 21, 22, 26, 29, 31, 32, 53, 54, 61, 65, 76];

        $actual = $this->hungariankeno->getNumbersPrimary($scraper);
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getNumbersSecondaryTest(): void
    {
        $scraper = $this->scraperSecondary->build('https://www.lotteryextreme.com/HungarianLottery/Keno-Results_History', 0, $this->scraperSecondary);
        $expected = ['5', '6', '8', '10', '11', '13', '14', '16', '18', '21', '22', '26', '29', '31', '32', '53', '54', '61', '65', '76'];

        $actual = $this->hungariankeno->getNumbersSecondary($scraper);
        $this->assertSame($expected, $actual);
    }
}
