<?php

namespace Tests\Unit\Classes\Lotto\Lotteries;

use Carbon\Carbon;
use Helpers_App;
use Lotto_Lotteries_DanishKeno;
use Lotto_Scraperhtml;
use Test_Unit;

final class DanishKenoTest extends Test_Unit
{
    private Lotto_Lotteries_DanishKeno $keno;
    private Lotto_Scraperhtml $scraper;

    public function setUp(): void
    {
        parent::setUp();
        $this->keno = new class extends Lotto_Lotteries_DanishKeno
        {
            protected Carbon $providerNextDrawDate;

            public function __construct()
            {
                $this->providerNextDrawDate = Carbon::parse('2024-12-05 21:30:00', 'Europe/Copenhagen');
            }
        };

        $this->scraper = new class extends Lotto_Scraperhtml
        {
            protected function fetchRawWebsite(string $url)
            {
                $this->rawHTML = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/danishkeno-results-primary.html'));
                $this->areaOfWorkHTML = $this->rawHTML;
            }
        };
    }

    /** @test */
    public function jackpotTest(): void
    {
        $expected = 3.08;
        $actual = Lotto_Lotteries_DanishKeno::LOTTERY_JACKPOT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersCountTest(): void
    {
        $expected = 20;
        $actual = Lotto_Lotteries_DanishKeno::LOTTERY_NUMBERS_COUNT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersRangeTest(): void
    {
        $expected = [1,70];
        $actual = Lotto_Lotteries_DanishKeno::LOTTERY_NUMBERS_RANGE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function providerTimezoneTest(): void
    {
        $expected = 'Europe/Copenhagen';
        $actual = Lotto_Lotteries_DanishKeno::PROVIDER_TIMEZONE;
        $this->assertSame($expected, $actual);
    }

    public static function getPrimarySourceUrlCases(): array
    {
        return [
            [Carbon::parse('2022-01-10'), 'https://www.lottotal.dk/keno/keno-mandag-10-januar-2022'],
            [Carbon::parse('2022-02-15'), 'https://www.lottotal.dk/keno/keno-tirsdag-15-februar-2022'],
            [Carbon::parse('2022-03-23'), 'https://www.lottotal.dk/keno/keno-onsdag-23-marts-2022'],
            [Carbon::parse('2023-04-13'), 'https://www.lottotal.dk/keno/keno-torsdag-13-april-2023'],
            [Carbon::parse('2023-05-19'), 'https://www.lottotal.dk/keno/keno-fredag-19-maj-2023'],
            [Carbon::parse('2023-06-24'), 'https://www.lottotal.dk/keno/keno-loerdag-24-juni-2023'],
            [Carbon::parse('2024-07-14'), 'https://www.lottotal.dk/keno/keno-soendag-14-juli-2024'],
            [Carbon::parse('2024-08-12'), 'https://www.lottotal.dk/keno/keno-mandag-12-august-2024'],
            [Carbon::parse('2024-09-17'), 'https://www.lottotal.dk/keno/keno-tirsdag-17-september-2024'],
            [Carbon::parse('2025-10-15'), 'https://www.lottotal.dk/keno/keno-onsdag-15-oktober-2025'],
            [Carbon::parse('2025-11-06'), 'https://www.lottotal.dk/keno/keno-torsdag-6-november-2025'],
            [Carbon::parse('2025-12-05'), 'https://www.lottotal.dk/keno/keno-fredag-5-december-2025'],
        ];
    }

    /**
     * @test
     * @dataProvider getPrimarySourceUrlCases
     */
    public function getPrimarySourceUrlTest(Carbon $input, string $expected): void
    {
        $actual = $this->keno->getPrimarySourceUrl($input);
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getNumbersPrimaryTest(): void
    {
        $scraper = $this->scraper->build('https://www.lottotal.dk/keno/keno-torsdag-5-december-2024', 0, $this->scraper);
        $expected = [3, 4, 7, 13, 14, 17, 20, 22, 26, 30, 39, 40, 41, 42, 47, 51, 54, 56, 57, 64];

        $actual = $this->keno->getNumbersPrimary($scraper);
        $this->assertSame($expected, $actual);
    }
}
