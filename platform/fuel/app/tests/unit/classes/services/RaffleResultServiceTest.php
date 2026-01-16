<?php

namespace Tests\Unit\Classes\Services;

use Lotto_Settings;
use Models\Currency;
use Models\Raffle;
use Models\RafflePrize;
use Models\RaffleRule;
use Models\RaffleRuleTier;
use Models\RaffleRuleTierInKindPrize;
use Models\WhitelabelRaffleTicketLine;
use Modules\Account\Reward\PrizeType;
use Services\RaffleResultService;
use Test_Unit;

class RaffleResultServiceTest extends Test_Unit
{
    private RaffleResultService $raffleResultService;

    private array $fakePrizes = [];
    private Raffle $fakeRaffle;

    public function setUp(): void
    {
        $this->raffleResultService = new RaffleResultService();
        Lotto_Settings::getInstance()->set('locale_default', 'en_GB.utf8');
        $this->prepareFakeDataForTest();
    }

    /** @test */
    public function getRaffleResultTableHtml_shouldReturnCorrectHtml(): void
    {
        $expectedHtml = file_get_contents(
            __DIR__ . '/../../../data/classes/services/expectedRaffleResultTable.html'
        );

        $actualHtml = $this->raffleResultService->getWinnersTableHtml(
            $this->fakePrizes,
            $this->fakeRaffle
        );

        $this->assertSame($expectedHtml, $actualHtml);
    }

    /** @test */
    public function formatLineNumber_shouldReturnCorrectFormattedValue(): void
    {
        $number = 5;
        $expected = '0005';

        $result = $this->raffleResultService->formatLineNumber(
            $number,
            $this->fakeRaffle
        );

        $this->assertSame($expected, $result);
    }


    private function prepareFakeDataForTest(): void
    {
        $this->fakePrizes = $this->createFakePrizes();
        $this->fakeRaffle = $this->createFakeRaffle();
    }

    private function createFakePrizes(): array
    {
        return [
            new RafflePrize([
                'lines_won_count' => 1,
                'total' => 325.00,
                'per_user' => 325.00,
                'raffle_draw_id' => 136,
                'raffle_rule_id' => 6,
                'raffle_rule_tier_id' => 11,
                'currency_id' => 1,
                'prize_amount' => 123,
                'tier' => new RaffleRuleTier([
                    'tier_prize_in_kind' => new RaffleRuleTierInKindPrize([
                        'type' => PrizeType::TICKET,
                        'name' => '10 x Mega Millions tickets'
                    ]),
                ]),
                'lines' => [
                    new WhitelabelRaffleTicketLine([
                        'number' => 233
                    ])
                ],
                'currency' => new Currency(['code' => 'EUR'])
            ]),
            new RafflePrize([
                'lines_won_count' => 1,
                'total' => 325.00,
                'per_user' => 325.00,
                'raffle_draw_id' => 136,
                'raffle_rule_id' => 6,
                'raffle_rule_tier_id' => 11,
                'currency_id' => 1,
                'prize_amount' => 123,
                'tier' => new RaffleRuleTier([
                    'tier_prize_in_kind' => new RaffleRuleTierInKindPrize([
                        'type' => PrizeType::TICKET,
                        'name' => '100 x Powerball tickets'
                    ]),
                ]),
                'lines' => [
                    new WhitelabelRaffleTicketLine([
                        'number' => 165
                    ])
                ],
                'currency' => new Currency(['code' => 'EUR'])
            ]),
        ];
    }

    private function createFakeRaffle(): Raffle
    {
        $raffle = new Raffle();
        $raffle->rules = [new RaffleRule(['max_lines_per_draw' => 1000])];
        $raffle->timezone = 'UTC';

        return $raffle;
    }
}
