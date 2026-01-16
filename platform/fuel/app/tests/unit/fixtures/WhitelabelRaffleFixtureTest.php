<?php

namespace Unit\Fixtures;

use Models\Raffle;
use Models\WhitelabelRaffle;
use Test_Unit;
use Tests\Fixtures\Raffle\WhitelabelRaffleFixture;

/**
 * @group fixture
 * @covers \Tests\Fixtures\Raffle\WhitelabelRaffleFixture
 */
final class WhitelabelRaffleFixtureTest extends Test_Unit
{
    private WhitelabelRaffleFixture $fixture;

    public function setUp(): void
    {
        parent::setUp();
        $this->fixture = $this->container->get(WhitelabelRaffleFixture::class);
    }

    /** @test */
    public function makeOne_BasicStateProvidedWithRaffle_RaffleIsUsedInProvider(): void
    {
        // Given raffle
        $raffle = new Raffle(['id' => 1]);

        // Given fixture with basic state and raffle context specified
        $this->fixture->withRaffle($raffle);
        $this->fixture->with('basic');

        // When makeOne called
        /** @var WhitelabelRaffle $model */
        $model = $this->fixture->makeOne();

        // Then new entry provider > raffle should be the same instance as provided one
        $this->assertSame($raffle, $model->provider->raffle);
    }
}
