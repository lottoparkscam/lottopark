<?php

namespace Unit\Fixtures\Raffle;

use fixtures\Exceptions\MissingRelation;
use Fuel\Core\Date;
use Models\Currency;
use Models\Raffle as Raffle;
use Stwarog\FuelFixtures\State;
use Test_Unit;
use Tests\Fixtures\Raffle\RaffleFixture;

/**
 * @group fixture
 * @covers \Tests\Fixtures\Raffle\RaffleFixture
 */
final class RaffleFixtureTest extends Test_Unit
{
    private RaffleFixture $fixture;

    public function setUp(): void
    {
        parent::setUp();
        $this->fixture = $this->container->get(RaffleFixture::class);
    }

    /** @test */
    public function makeOne_WithRelationAsStateObjectWithParams_ShouldCallRelationWithGivenAttributes(): void
    {
        // Given state with given concrete attributes
        $state = State::for('currency', ['code' => 'USD']);

        // When makeOne called with "currency" state as Object
        $model = $this->fixture
            ->with($state)
            ->makeOne(['name' => 'Some name']);

        // Then created model should be instance of Raffle
        // with Currency relation data
        // without calling DB
        $this->assertInstanceOf(Raffle::class, $model);
    }

    /** @test */
    public function makeOne_WithRelationAsStringWithConcreteSubState_WillCallSubStateFromRelation(): void
    {
        // Given state as a string with it's state "usd"
        $state = 'currency.usd';
        $expectedCurrencyCode = 'USD';

        // When makeOne called with "currency.usd" state
        /** @var Raffle $model */
        $model = $this->fixture->with($state)->makeOne();

        // Then created model should be instance of Raffle
        // with Currency relation data and code as "USD"
        $this->assertInstanceOf(Raffle::class, $model);
        $this->assertInstanceOf(Currency::class, $model->currency);
        $this->assertSame($expectedCurrencyCode, $model->currency->code);
    }

    /**
     * @test
     * @dataProvider makeOne_WithGGWorldStateDataProvider
     */
    public function makeOne_WithGGWorldState_WillCreateValidGGWorldModel(bool $useMethod): void
    {
        // Given state as string
        $stateName = RaffleFixture::GGWORLD;
        if (!$useMethod) {
            $this->fixture->with($stateName);
        } else {
            // Or state passed through method
            $this->fixture->withGGWorld();
        }

        // When makeOne is called with provided states
        /** @var Raffle $raffle */
        $raffle = $this->fixture->makeOne();

        // Then GG World should be valid instance
        // Relations
        $this->assertInstanceOf(Currency::class, $raffle->currency, 'currency relation');
        $this->assertNotEmpty($raffle->rules, 'rule relation');

        // Values
        $this->assertSame($raffle->currency->code, $raffle->getFirstRule()->currency->code);
    }

    /**
     * @return array<string, array<bool>>
     */
    public function makeOne_WithGGWorldStateDataProvider(): array
    {
        return [
            'using state as string' => [false],
            'using method' => [true],
        ];
    }

    /** @test */
    public function makeOne_TemporaryDisabledState(): void
    {
        // Given state
        $state = 'temporary_disabled';

        // When created
        /** @var Raffle $raffle */
        $raffle = $this->fixture->with($state)->makeOne();

        // Then logic fields are valid
        $this->assertTrue($raffle->is_sell_limitation_enabled);
        $this->assertFalse($raffle->is_sell_enabled);
        $this->assertNotEmpty($raffle->sell_open_dates);

        // And carbon date can be created
        foreach ($raffle->sell_open_dates as $dateAsString) {
            $date = Date::create_from_string($dateAsString, 'mysql');
            $this->assertSame($dateAsString, $date->format('mysql'));
        }
    }

    /** @test */
    public function makeOne_TemporaryEnabledState(): void
    {
        // Given state
        $state = 'temporary_enabled';

        // When created
        /** @var Raffle $raffle */
        $raffle = $this->fixture->with($state)->makeOne();

        // Then logic fields are valid
        $this->assertTrue($raffle->is_sell_limitation_enabled);
        $this->assertTrue($raffle->is_sell_enabled);
    }

    /** @test */
    public function makeOne_BasicRegularPriceState_PriceAndFeeShouldBeDefined(): void
    {
        // Given state
        $state = 'regular_price';

        // When created
        /** @var Raffle $raffle */
        $raffle = $this->fixture->with('basic', $state)->makeOne();

        // Then line price and fee should be:
        $this->assertSame(10.0, $raffle->getFirstRule()->line_price);
        $this->assertSame(1.0, $raffle->getFirstRule()->fee);
    }

    /** @test */
    public function makeOne_RegularPriceState_PriceAndFeeShouldBeDefinedInNewlyCreatedRule(): void
    {
        // Given state
        $state = 'regular_price';

        // When created
        /** @var Raffle $raffle */
        $raffle = $this->fixture->with($state)->makeOne();

        // Then line price and fee should be:
        $this->assertSame(10.0, $raffle->rules[0]->line_price);
        $this->assertSame(1.0, $raffle->rules[0]->fee);
    }

    /** @test */
    public function stateBets50_NoRulesRelation_ThrowsException(): void
    {
        // Given fixture with
        $this->fixture->with('bets50');

        // Expect exception
        $this->expectException(MissingRelation::class);
        $this->expectExceptionMessage('Missing relations: rules, whitelabel_raffle in model: Models\Raffle');

        // When
        $this->fixture->makeOne();
    }

    /** @test */
    public function stateBets50_NoWlProviderRelation_ThrowsException(): void
    {
        // Given fixture with
        $this->fixture->with('rule', 'bets50');

        // Expect exception
        $this->expectException(MissingRelation::class);
        $this->expectExceptionMessage('Missing relations: rules, whitelabel_raffle in model: Models\Raffle');

        // When
        $this->fixture->makeOne();
    }

    /** @test */
    public function stateBets50_ShouldChangeMaxBetsValueTo50(): void
    {
        // Given fixture with
        $this->fixture->with('basic', 'bets50');

        // When
        /** @var Raffle $raffle */
        $raffle = $this->fixture->makeOne();

        // Then raffle max bet should be 50
        $expected = 50;
        $this->assertSame($expected, $raffle->max_bets);
        $this->assertSame($expected, $raffle->getFirstRule()->max_lines_per_draw);
        $this->assertSame($expected, $raffle->whitelabel_raffle->provider->max_bets);
    }
}
