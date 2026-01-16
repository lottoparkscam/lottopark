<?php

namespace Unit\Fixtures\Raffle;

use Models\RaffleRuleTier;
use Test_Unit;
use Tests\Fixtures\Raffle\RaffleRuleTierFixture;

/**
 * @group fixture
 * @covers \Tests\Fixtures\Raffle\RaffleRuleTierFixture
 */
final class RaffleRuleTierFixtureTest extends Test_Unit
{
    private RaffleRuleTierFixture $fixture;

    public function setUp(): void
    {
        parent::setUp();
        $this->fixture = $this->container->get(RaffleRuleTierFixture::class);
    }

    /** @test */
    public function makeOne_CreatesRandomInstance_ValidMatches(): void
    {
        // When Given tier
        /** @var RaffleRuleTier $tier */
        $tier = $this->fixture->makeOne();

        // Then tier range 0 index value is less than 1
        $this->assertLessThan($tier->matches[0][1], $tier->matches[0][0]);
    }
}
