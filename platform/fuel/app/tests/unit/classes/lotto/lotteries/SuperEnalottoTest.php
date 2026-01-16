<?php

namespace Tests\Unit\Classes\Lotto\Lotteries;

use Carbon\Carbon;
use Helpers_App;
use Lotto_Lotteries_SuperEnalotto;
use Lotto_Scraperhtml;
use Test_Unit;

final class SuperEnalottoTest extends Test_Unit
{
    private Lotto_Lotteries_SuperEnalotto $game;
    private Lotto_Scraperhtml $scraper;

    public function setUp(): void
    {
        parent::setUp();
        $this->game = new class extends Lotto_Lotteries_SuperEnalotto
        {
            protected Carbon $nextDrawDate;

            public function __construct()
            {
                $this->nextDrawDate = Carbon::parse('2025-02-04 20:00:00', 'Europe/Rome');

                $this->lottery = [
                    'timezone' => 'Europe/Rome',
                    'next_date_local' => '2025-02-04 20:00:00',
                    'draw_dates' => '["Tue 20:00", "Thu 20:00", "Fri 20:00", "Sat 20:00"]',
                ];
            }

            public function getJsonPrimary(): array
            {
                $json = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/superenalotto-results-primary.json'));
                $data = json_decode($json, true);
                return $data;
            }

            public function getJackpotJsonPrimary(): array
            {
                $json = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/superenalotto-jackpot-primary.json'));
                $data = json_decode($json, true);
                return $data;
            }
        };

        $this->scraper = new class extends Lotto_Scraperhtml
        {
            protected function fetchRawWebsite(string $url)
            {
                $this->rawHTML = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/superenalotto-results-secondary.html'));
                $this->areaOfWorkHTML = $this->rawHTML;
            }
        };
    }

    public function getExpectedData(): array
    {
        return [
            'numbers' => [
                [10, 29, 41, 77, 83, 85],
                [56],
            ],
            'date' => '2025-02-04 20:00:00',
            'jackpot' => 69.1,
            'prizes' => [
                ['0', 0],
                ['0', 0],
                ['2',  90860.4],
                ['471', 398.6],
                ['19214', 29.13],
                ['323132', 5.35],
            ]
        ];
    }

    /** @test */
    public function getDataPrimaryTest(): void
    {
        [$numbers, $prizes, $date, $jackpot] = $this->game->getDataPrimary();
        $expectedData = $this->getExpectedData();

        $this->assertEquals($expectedData['numbers'], $numbers);
        $this->assertEquals($expectedData['prizes'], $prizes);
        $this->assertEquals($expectedData['date'], $date->format('Y-m-d H:i:s'));
        $this->assertEquals($expectedData['jackpot'], $jackpot);
    }

    /** @test */
    public function getDataSecondaryTest(): void
    {
        $scraper = $this->scraper->build('', 0, $this->scraper);
        [$numbers, $prizes, $date, $jackpot] = $this->game->getDataSecondary($scraper);
        $expectedData = $this->getExpectedData();

        $this->assertSame($expectedData['numbers'], $numbers);
        $this->assertEquals($expectedData['prizes'], $prizes);
        $this->assertEquals($expectedData['date'], $date->format('Y-m-d H:i:s'));
        $this->assertEquals($expectedData['jackpot'], $jackpot);
    }
}
