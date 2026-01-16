<?php

namespace Tests\Unit\Classes\Lotto\Lotteries;

use Carbon\Carbon;
use Helpers_App;
use Lotto_Lotteries_LatvianKeno;
use Lotto_Scraperhtml;
use Test_Unit;

final class LatvianKenoTest extends Test_Unit
{
    private Lotto_Lotteries_LatvianKeno $latviankeno;
    private Lotto_Scraperhtml $scraper;

    public function setUp(): void
    {
        parent::setUp();
        $this->latviankeno = new class extends Lotto_Lotteries_LatvianKeno
        {
            protected Carbon $providerNextDrawDate;

            public function __construct()
            {
                $this->providerNextDrawDate = Carbon::parse('2024-07-30 15:30:00', 'Europe/Riga')->setTimezone('Europe/Riga');
            }
        };

        $this->scraper = new class extends Lotto_Scraperhtml
        {
            protected function fetchRawWebsite(string $url)
            {
                $this->rawHTML = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/latviankeno-results.html'));
                $this->areaOfWorkHTML = $this->rawHTML;
            }
        };
    }

    /** @test */
    public function jackpotTest(): void
    {
        $expected = 0.8;
        $actual = Lotto_Lotteries_LatvianKeno::LOTTERY_JACKPOT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersCountTest(): void
    {
        $expected = 20;
        $actual = Lotto_Lotteries_LatvianKeno::LOTTERY_NUMBERS_COUNT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersRangeTest(): void
    {
        $expected = [1,62];
        $actual = Lotto_Lotteries_LatvianKeno::LOTTERY_NUMBERS_RANGE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function providerTimezoneTest(): void
    {
        $expected = 'Europe/Riga';
        $actual = Lotto_Lotteries_LatvianKeno::PROVIDER_TIMEZONE;
        $this->assertSame($expected, $actual);
    }

    public static function nextDrawTimeCases(): array
    {
        return [
            ['11:30', 'Rīta'],
            ['15:30', 'Dienas'],
            ['19:30', 'Vakara'],
        ];
    }

    /**
     * @test
     * @dataProvider nextDrawTimeCases
     */
    public function nextDrawTimeTest(string $input, string $expected): void
    {
        $actual = $this->latviankeno->getNextDrawTime($input);
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function nextDrawTimeExceptionTest(): void
    {
        $this->expectExceptionMessage('latvian-keno - unable to get next draw time');
        $this->latviankeno->getNextDrawTime('abc');
    }

    /** @test */
    public function getNumbersPrimaryTest(): void
    {
        $scraper = $this->scraper->build("https://www.latloto.lv/lv/arhivs/keno", 0, $this->scraper);
        $expected = [1, 5, 7, 14, 16, 18, 24, 27, 30, 31, 34, 35, 40, 41, 46, 48, 52, 53, 59, 62];

        $actual = $this->latviankeno->get_numbers_primary($scraper);
        $this->assertSame($expected, $actual);
    }
}
