<?php

namespace Unit\Fixtures\Raffle;

use Models\Raffle;
use Models\RaffleRule;
use Models\RaffleRuleTier;
use Test_Unit;
use Tests\Fixtures\Raffle\RaffleFixture;
use Tests\Fixtures\Raffle\RaffleRuleFixture;

/**
 * @group fixture
 * @covers \Tests\Fixtures\Raffle\RaffleRuleFixture
 */
final class RaffleRuleFixtureTest extends Test_Unit
{
    private RaffleRuleFixture $fixture;
    private RaffleFixture $raffleFixture;

    public function setUp(): void
    {
        parent::setUp();
        $this->fixture = $this->container->get(RaffleRuleFixture::class);
        $this->raffleFixture = $this->container->get(RaffleFixture::class);
    }

    /** @test */
    public function makeOne_FeeShouldBeNotGreaterThanHalfOfLinePrice(): RaffleRule
    {
        // When makeOne called
        /** @var RaffleRule $model */
        $model = $this->fixture->makeOne();

        // Then
        $expected = $model->line_price / 2;
        $this->assertLessThanOrEqual($expected, $model->fee);

        return $model;
    }

    /**
     * @test
     * @depends makeOne_FeeShouldBeNotGreaterThanHalfOfLinePrice
     */
    public function makeOne_RangesToShouldNotBeGreaterThanMaxLinesPerDraw(RaffleRule $model): void
    {
        $expected = $model->max_lines_per_draw;
        [$from, $to] = $model->ranges[0];
        $this->assertLessThan($to, $from);
        $this->assertLessThanOrEqual($expected, $to);
    }

    /**
     * @test
     * @depends makeOne_FeeShouldBeNotGreaterThanHalfOfLinePrice
     */
    public function makeOne_MaxLinesPerDraw_ShowBeDimensional(RaffleRule $model): void
    {
        $this->assertSame(0, $model->max_lines_per_draw % 10);
    }

    /** @test */
    public function makeOne_GgWorld_ShouldContainsValidData(): void
    {
        // Given fixture with GGWORLD state
        $this->fixture->with($this->fixture::GGWORLD);

        // When makeOne is called
        /** @var RaffleRule $actual */
        $actual = $this->fixture->makeOne();

        // Then created model should contain raffle rule valid data
        $expectedLinePrice = 10;
        $expectedFee = 0.5;
        $expectedMaxLinesPerDraw = 1000;
        $expectedRanges = [[1, 1000]];
        $this->assertEquals($expectedLinePrice, $actual->line_price);
        $this->assertEquals($expectedFee, $actual->fee);
        $this->assertEquals($expectedMaxLinesPerDraw, $actual->max_lines_per_draw);
        $this->assertSame($expectedRanges, $actual->ranges);
    }

    /** @test */
    public function with_TiersStateAsStringWithBrackets_WillCreateRandomTiers(): void
    {
        // Given factory with tiers state
        $this->fixture->with('tiers[10]');

        // When makeOne called
        /** @var RaffleRule $rule */
        $rule = $this->fixture->makeOne();

        // Then tier relation should be an array of tier models
        $this->assertIsArray($rule->tiers);
        foreach ($rule->tiers as $tier) {
            $this->assertInstanceOf(RaffleRuleTier::class, $tier);
        }
    }

    /** @test */
    public function fromState_GGWorldRaffle_ShouldCreateValidTiers(): void
    {
        // Given raffle with ggworld raffle
        /** @var Raffle $raffle */
        $raffle = $this->raffleFixture->with($this->fixture::GGWORLD)->makeOne();
        $raffle->slug = 'gg-world-raffle';

        // And fixture with GGWORLD state
        $this->fixture->stateFrom($raffle);

        // When makeOne is called
        /** @var RaffleRule $rule */
        $rule = $this->fixture->makeOne();

        // Then tiers should match gg world ones
        $this->assertCount(2, $rule->tiers);

        $first = $rule->tiers[0];
        $this->assertSame('raffle-closed:1', $first->slug);
        $this->assertSame([1], $first->matches);

        $second = $rule->tiers[1];
        $this->assertSame('raffle-closed:2_25', $second->slug);
        $this->assertSame([[2, 25]], $second->matches);
    }
}
