<?php

namespace Tests\Unit\Classes\Services;

use Lotto_Settings;
use Services\LotteryResultService;
use Test_Unit;

class LotteryResultServiceTest extends Test_Unit
{
    private LotteryResultService $lotteryResultService;

    private array $fakeLottery = [];
    private array $fakeDrawsData = [];
    private array $fakeLotteryType = [];
    private array $fakeLotteryDraw = [];

    public function setUp(): void
    {
        $this->lotteryResultService = new LotteryResultService();
        Lotto_Settings::getInstance()->set('locale_default', 'en_GB.utf8');
        $this->prepareFakeDataForTest();
    }

    /** @test */
    public function getLotteryResultTableHtml_shouldReturnCorrectHtml(): void
    {
        $expectedHtml = file_get_contents(
            __DIR__ . '/../../../data/classes/services/expectedLotteryResultTable.html'
        );

        $actualHtml = $this->lotteryResultService->getLotteryResultTableHtml(
            $this->fakeLottery,
            $this->fakeDrawsData,
            $this->fakeLotteryType,
            $this->fakeLotteryDraw
        );

        $this->assertSame($expectedHtml, $actualHtml);
    }

    private function prepareFakeDataForTest(): void
    {
        $this->fakeLottery = [
            'currency' => ['code' => 'USD'],
            'id' => 1,
        ];
        $this->fakeDrawsData = [
            [
                'multiplier' => 2,
                'match_n' => 2,
                'match_b' => 1,
                'winners' => 5,
                'prizes' => 1000,
                'type' => 1,
                'additional_data' => 'a:1:{s:6:"refund";i:1;}'
            ],
            [
                'multiplier' => 3,
                'match_n' => 3,
                'match_b' => 0,
                'winners' => 2,
                'prizes' => 2000,
                'type' => 2,
                'additional_data' => 'a:1:{s:5:"super";i:1;}'
            ]
        ];
        $this->fakeLotteryType = [
            'bcount' => 0,
            'bextra' => 1,
            'additional_data' => '',
        ];
        $this->fakeLotteryDraw = [
            'lottery_id' => 1,
            'draw_no' => null,
            'jackpot' => 60.00000000,
            'numbers' => '1,7,23,38,55',
            'bnumbers' => '2',
            'total_prize' => 5700366.00,
            'total_winners' => 651907,
            'final_jackpot' => '0.00',
            'additional_data' => ':0:{}',
        ];
    }
}
