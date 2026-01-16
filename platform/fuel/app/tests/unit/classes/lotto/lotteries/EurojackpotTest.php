<?php

namespace Tests\Unit\Classes\Lotto\Lotteries;

use Carbon\Carbon;
use Helpers_App;
use Lotto_Lotteries_Eurojackpot;
use Test_Unit;

final class EurojackpotTest extends Test_Unit
{
    private Lotto_Lotteries_Eurojackpot $game;

    public function setUp(): void
    {
        parent::setUp();
        $this->game = new class extends Lotto_Lotteries_Eurojackpot
        {
            protected Carbon $nextDrawDate;
            protected $lottery;

            public function __construct()
            {
                $this->nextDrawDate = Carbon::parse('2024-11-22 20:00:00', 'Europe/Madrid');
                $this->lottery = [
                    'timezone' => 'Europe/Madrid',
                ];
            }

            public function getPrimaryJsonResults(): array
            {
                $json = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/eurojackpot-results-primary.json'));
                $data = json_decode($json, true);
                return $data;
            }
        };
    }

    /** @test */
    public function getDataPrimaryTest(): void
    {
        $expectedNumbers = [[30, 10, 41, 6, 34], [7, 10]];
        $expectedPrizes = [
            // [winners, amount]
            [0, 0],
            [6, 4243653],
            [8, 299271],
            [74, 5336.6],
            [1526, 323.4],
            [3102, 175],
            [3102, 127.3],
            [46773, 26.9],
            [65987, 21.3],
            [142091, 18.7],
            [252609, 13.1],
            [984713, 10.1],
        ];
        $expectedDrawDateFormatted = '2024-11-22 20:00:00';
        $expectedJacpot = 120;

        $actual = $this->game->getDataPrimary();

        $this->assertEquals($expectedNumbers, $actual[0]);
        $this->assertEquals($expectedPrizes, $actual[1]);
        $this->assertEquals($expectedDrawDateFormatted, $actual[2]->format('Y-m-d H:i:s'));
        $this->assertEquals($expectedJacpot, $actual[3]);
    }
}
