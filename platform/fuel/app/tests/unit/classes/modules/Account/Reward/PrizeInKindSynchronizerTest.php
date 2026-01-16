<?php

namespace Unit\Modules\Account\Reward;

use Factory_Orm_Tier;
use Fuel\Tasks\Factory\Utils\Faker;
use Models\Lottery;
use Models\RafflePrize;
use Models\RaffleRuleTier;
use Models\RaffleRuleTierInKindPrize;
use Modules\Account\Reward\PrizeInKindSynchronizer;
use Services_Raffle_Logger;
use Test_Unit;
use Wrappers\Decorators\ConfigContract;

class PrizeInKindSynchronizerTest extends Test_Unit
{
    private Lottery $lottery_dao;
    private Services_Raffle_Logger $logger;
    private ConfigContract $config;
    private PrizeInKindSynchronizer $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->lottery_dao = $this->createMock(Lottery::class);
        $this->logger = $this->createMock(Services_Raffle_Logger::class);
        $this->config = $this->createMock(ConfigContract::class);

        $this->service = new PrizeInKindSynchronizer(
            $this->lottery_dao,
            $this->logger,
            $this->config
        );
    }

    /** @test */
    public function recalculatePrizes__prizes_differs_and_sync_is_enabled_in_config__recalculates(): void
    {
        // Given
        $prize = new RafflePrize();
        $tier = $this->get_tier();
        $prize->tier = $tier;
        $tier_prize_in_kind = new RaffleRuleTierInKindPrize();
        $tier_prize_in_kind->slug = Faker::forge()->slug(2);
        $reward_count = 5;
        $tier_prize_in_kind->config = ['count' => $reward_count];
        $prize->tier->tier_prize_in_kind = $tier_prize_in_kind;
        $lottery = $this->get_lottery();
        $new_prize_from_lottery_price = $lottery->price;
        $tier_winners = $tier->winners_count;

        $expected_new_price = $reward_count * $new_prize_from_lottery_price;
        $expected_new_total_price = $expected_new_price * $tier_winners;

        $this->config
            ->expects($this->once())
            ->method('get')
            ->with('sync.raffle.update_prizes')
            ->willReturn(true);

        $this->lottery_dao
            ->expects($this->once())
            ->method('get_by_slug')
            ->with($tier_prize_in_kind->slug)
            ->willReturn($lottery);

        // When
        $this->service->recalculatePrizes($prize);

        // Then
        $this->assertSame($expected_new_price, $tier_prize_in_kind->per_user);
        $this->assertSame($expected_new_price, $prize->per_user);
        $this->assertSame($expected_new_total_price, $prize->total);
        $this->assertSame($expected_new_price, $prize->tier->prize);
    }

    /** @test */
    public function recalculatePrizes__prizes_not_differs_and_sync_is_enabled_in_config__skips_recalculation(): void
    {
        // Given
        $expected_prize = 10.0;

        $prize = new RafflePrize();
        $prize->per_user = $expected_prize;
        $tier = $this->get_tier();
        $tier->prize = $expected_prize;
        $prize->tier = $tier;
        $tier_prize_in_kind = new RaffleRuleTierInKindPrize();
        $tier_prize_in_kind->slug = Faker::forge()->slug(2);
        $tier_prize_in_kind->per_user = $expected_prize;
        $reward_count = 1;
        $tier_prize_in_kind->config = ['count' => $reward_count];
        $prize->tier->tier_prize_in_kind = $tier_prize_in_kind;
        $lottery = $this->get_lottery();

        $this->config
            ->method('get')
            ->with('sync.raffle.update_prizes')
            ->willReturn(true);

        $this->lottery_dao
            ->expects($this->once())
            ->method('get_by_slug')
            ->with($tier_prize_in_kind->slug)
            ->willReturn($lottery);

        $this->logger
            ->expects($this->never())
            ->method('log_info');

        // When
        $this->service->recalculatePrizes($prize);
    }

    /** @test */
    public function recalculatePrizes__sync_is_disabled_in_config__skips(): void
    {
        // Given
        $prize = new RafflePrize();

        $this->config
            ->expects($this->once())
            ->method('get')
            ->with('sync.raffle.update_prizes')
            ->willReturn(false);

        $this->lottery_dao
            ->expects($this->never())
            ->method('get_by_slug');

        // When
        $this->service->recalculatePrizes($prize);
    }

    /** @test */
    public function recalculatePrizes_CustomPrizeInKindLotterySlug_Recalculates(): void
    {
        // Given
        $prize = new RafflePrize();
        $tier = $this->get_tier();
        $prize->tier = $tier;
        $tier_prize_in_kind = new RaffleRuleTierInKindPrize();
        $tier_prize_in_kind->slug = '100x-euromillions';
        $reward_count = 5;
        $tier_prize_in_kind->config = ['count' => $reward_count];
        $prize->tier->tier_prize_in_kind = $tier_prize_in_kind;
        $lottery = $this->get_lottery();

        $expected_slug = 'euromillions';

        $this->config
            ->expects($this->once())
            ->method('get')
            ->with('sync.raffle.update_prizes')
            ->willReturn(true);

        $this->lottery_dao
            ->expects($this->once())
            ->method('get_by_slug')
            ->with($expected_slug)
            ->willReturn($lottery);

        // When
        $this->service->recalculatePrizes($prize);
    }

    private function get_tier(): RaffleRuleTier
    {
        $tier = $this->container->get(Factory_Orm_Tier::class)->build(false);
        $tier->matches = [[0, 5]];
        return $tier;
    }
}
