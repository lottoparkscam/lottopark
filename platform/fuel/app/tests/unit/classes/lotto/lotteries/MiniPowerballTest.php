<?php

namespace Tests\Unit\Classes\Lotto\Lotteries;

use Helpers_App;
use Lotto_Lotteries_MiniPowerball;
use Lotto_Scraperhtml;
use Test_Unit;

final class MiniPowerballTest extends Test_Unit
{
    private Lotto_Lotteries_MiniPowerball $miniPowerball;
    private Lotto_Scraperhtml $scraper;

    public function setUp(): void
    {
        parent::setUp();
        $this->miniPowerball = new class extends Lotto_Lotteries_MiniPowerball
        {
            public function __construct()
            {
                $this->lottery = [
                    'timezone' => 'America/New_York',
                    'next_date_local' => '2024-11-02 22:59:00',
                    'draw_dates' => '["Mon 22:59", "Wed 22:59", "Sat 22:59"]'
                ];
            }
        };

        $this->scraper = new class extends Lotto_Scraperhtml
        {
            protected function fetchRawWebsite(string $url)
            {
                $this->rawHTML = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/minipowerball-results-primary.html'));
                $this->areaOfWorkHTML = $this->rawHTML;
            }
        };
    }

    /** @test */
    public function getLotteryDataFromFirstSourceTest(): void
    {
        $scraper = $this->scraper->build("https://www.powerball.com/draw-result?gc=powerball&date=2024-11-02", 0, $this->scraper);

        $expectedNumbers = [[10,45,48,58,61],[2]];
        $expectedPrizes = [
            ['0',0],
            ['0',100000],
            ['2', 5000],
            ['174', 10],
            ['473', 10],
            ['11560', 0.7],
            ['9951', 0.7],
            ['80148', 0.4],
            ['199942', 0.4],
        ];
        $expectedDrawDateTime = '2024-11-02 22:59:00';

        $data = $this->miniPowerball->getLotteryDataFromFirstSource($scraper);
        [$numbersArray, $prizes, $drawDateTime, $jackpot] = $this->miniPowerball->getUpdatedResults($data);

        $this->assertSame($expectedNumbers, $numbersArray);
        $this->assertSame($expectedPrizes, $prizes);
        $this->assertSame($expectedDrawDateTime, $drawDateTime->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function getLotteryDataFromSecondSourceTest(): void
    {
        $this->scraper = new class extends Lotto_Scraperhtml
        {
            protected function fetchRawWebsite(string $url)
            {
                $this->rawHTML = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/minipowerball-results-secondary.html'));
                $this->areaOfWorkHTML = $this->rawHTML;
            }
        };

        $scraper = $this->scraper->build("https://www.powerball.net/numbers/2024-11-02", 0, $this->scraper);

        $expectedNumbers = [[10,45,48,58,61],[2]];

        $expectedPrizes = [
            ['0',5510000],
            ['0',100000],
            ['2', 5000],
            ['174', 10],
            ['473', 10],
            ['11560', 0.7],
            ['9951', 0.7],
            ['80148', 0.4],
            ['199942', 0.4],
        ];

        $expectedDrawDateTime = '2024-11-02 22:59:00';
        $expectedJackpot = 6.3;

        $data = $this->miniPowerball->getLotteryDataFromSecondSource($scraper);
        [$numbersArray, $prizes, $drawDateTime, $jackpot] = $this->miniPowerball->getUpdatedResults($data);

        $this->assertSame($expectedNumbers, $numbersArray);
        $this->assertSame($expectedPrizes, $prizes);
        $this->assertSame($expectedDrawDateTime, $drawDateTime->format('Y-m-d H:i:s'));
        $this->assertSame($expectedJackpot, $jackpot);
    }
}
