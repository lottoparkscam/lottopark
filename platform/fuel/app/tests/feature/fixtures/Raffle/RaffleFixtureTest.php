<?php

namespace Feature\Fixtures\Raffle;

use Models\Currency;
use Models\Raffle;
use Models\RaffleRule;
use Models\RaffleRuleTier;
use Test_Feature;
use Tests\Fixtures\Raffle\RaffleFixture;
use Unit\Fixtures\Cases\CreateOneBasicState;

/**
 * @group fixture
 * @covers \Tests\Fixtures\Raffle\RaffleFixture
 */
final class RaffleFixtureTest extends Test_Feature implements CreateOneBasicState
{
    private RaffleFixture $fixture;

    public function setUp(): void
    {
        parent::setUp();
        $this->fixture = $this->container->get(RaffleFixture::class);
    }

    /** @test */
    public function createOne_Basic_SavesInDb(): Raffle
    {
        // Given factory with basic state
        $this->fixture->with($this->fixture::BASIC);

        // When createOne is called
        /** @var Raffle $model */
        $model = $this->fixture->createOne();

        // Then db should be affected with basic relations

        // Raffle
        $where = ['slug', '=', $model->slug];
        $this->assertDbHasRows(Raffle::class, $where, 1);

        // Rule
        $this->assertGreaterThanOrEqual(1, RaffleRule::count());

        // Tiers
        $where = ['raffle_rule_id', '=', $model->getFirstRule()->id];
        $this->assertDbHasRows(RaffleRuleTier::class, $where);

        // Currency
        $where = ['code', '=', $model->currency->code];
        $this->assertDbHasRows(Currency::class, $where);

        return $model;
    }

    /**
     * @test
     * @depends createOne_Basic_SavesInDb
     */
    public function createOne_Basic_CurrencyCodeIsSameInRaffleAndRule(Raffle $raffle): void
    {
        // Then currency code should be the same in Raffle and related Rule
        $this->assertSame($raffle->currency->code, $raffle->getFirstRule()->currency->code);
    }
}
