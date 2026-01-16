<?php

namespace Tests\Unit\Classes\Lotto\Lotteries;

use Carbon\Carbon;
use Helpers_App;
use Lotto_Lotteries_BrazilianKeno;
use Lotto_Scraperhtml;
use Test_Unit;

final class BrazilianKenoTest extends Test_Unit
{
    private Lotto_Lotteries_BrazilianKeno $keno;
    private Lotto_Scraperhtml $scraper;

    public function setUp(): void
    {
        parent::setUp();
        $this->keno = new class extends Lotto_Lotteries_BrazilianKeno
        {
            protected Carbon $providerNextDrawDate;

            public function __construct()
            {
                $this->providerNextDrawDate = Carbon::parse('2024-11-04 13:12:00', 'America/Sao_Paulo');
            }

            public function getPrimaryResultsRaw(string $nonce): string
            {
                $json = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/braziliankeno-results-primary.json'));
                $data = json_decode($json, true);
                return $data['data']['html'];
            }
        };

        $this->scraper = new class extends Lotto_Scraperhtml
        {
            protected function fetchRawWebsite(string $url)
            {
                $this->rawHTML = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/braziliankeno-nonce.html'));
                $this->areaOfWorkHTML = $this->rawHTML;
            }
        };
    }

    /** @test */
    public function jackpotTest(): void
    {
        $expected = 0.5;
        $actual = Lotto_Lotteries_BrazilianKeno::LOTTERY_JACKPOT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersCountTest(): void
    {
        $expected = 20;
        $actual = Lotto_Lotteries_BrazilianKeno::LOTTERY_NUMBERS_COUNT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersRangeTest(): void
    {
        $expected = [1,80];
        $actual = Lotto_Lotteries_BrazilianKeno::LOTTERY_NUMBERS_RANGE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function providerTimezoneTest(): void
    {
        $expected = 'America/Sao_Paulo';
        $actual = Lotto_Lotteries_BrazilianKeno::PROVIDER_TIMEZONE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getNonceTest(): void
    {
        $scraper = $this->scraper->build('https://keno.com.br/resultados/', 0, $this->scraper);
        $expected = 'da6dd36527';

        $actual = $this->keno->getNonce($scraper);
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getNumbersPrimaryTest(): void
    {
        $actual = $this->keno->getNumbersPrimary('');
        sort($actual);
        $expected = ['10', '15', '17', '22', '25', '31', '33', '35', '38', '40', '42', '45', '53', '55', '56', '62', '64', '71', '74', '78'];

        $this->assertEquals($expected, $actual);
    }
}
