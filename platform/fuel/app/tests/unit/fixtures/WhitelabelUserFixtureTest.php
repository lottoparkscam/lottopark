<?php

namespace Unit\Fixtures;

use Generator;
use Models\WhitelabelUser as User;
use Test_Unit;
use Tests\Fixtures\WhitelabelUserFixture;

/**
 * @group fixture
 * @covers \Tests\Fixtures\WhitelabelUserFixture
 */
final class WhitelabelUserFixtureTest extends Test_Unit
{
    private WhitelabelUserFixture $fixture;

    public function setUp(): void
    {
        parent::setUp();
        $this->fixture = $this->container->get(WhitelabelUserFixture::class);
    }

    /** @test */
    public function makeOne_Balance0_UserBalanceAndBonusIsZero(): void
    {
        // When entry is created wit BALANCE_0 state
        /** @var User $model */
        $model = $this->fixture->with($this->fixture::BALANCE_0)->makeOne();

        // Then it's bonuses should be 0.0
        $this->assertSame(0.0, $model->balance);
        $this->assertSame(0.0, $model->bonus_balance);
    }

    /**
     * @test
     * @dataProvider provideMakeOneWIthCurrencyCode
     */
    public function makeOne_ValidStateGiven_CurrencyShouldBeUsd(string $state, string $expectedCurrencyCode): void
    {
        // When entry is created with given state
        /** @var User $model */
        $model = $this->fixture->with($state)->makeOne();

        // Then currency code should be the expected one
        $this->assertSame($expectedCurrencyCode, $model->currency->code);
    }

    public function provideMakeOneWIthCurrencyCode(): Generator
    {
        yield 'usd state' => ['usd', 'USD'];
        yield 'eur state' => ['eur', 'EUR'];
    }
}
