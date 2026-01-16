<?php

namespace Unit\Fixtures\Raffle;

use Models\Whitelabel;
use Models\WhitelabelRaffleTicket as Ticket;
use Models\WhitelabelRaffleTicketLine as TicketLine;
use Test_Unit;
use Tests\Fixtures\Raffle\RaffleTicketLineFixture;

/**
 * @group fixture
 * @covers \Tests\Fixtures\Raffle\RaffleTicketLineFixture
 */
final class RaffleTicketLineFixtureTest extends Test_Unit
{
    private RaffleTicketLineFixture $fixture;

    public function setUp(): void
    {
        parent::setUp();
        $this->fixture = $this->container->get(RaffleTicketLineFixture::class);
    }

    /** @test */
    public function makeOne_BasicStateWithoutDependencies_ShouldCreateThem(): void
    {
        // Given fixture with basic state
        $this->fixture->with('basic');

        // When model created
        /** @var TicketLine $model */
        $model = $this->fixture->makeOne();

        // Then
        $this->assertInstanceOf(Ticket::class, $model->ticket);
        $this->assertInstanceOf(Whitelabel::class, $model->whitelabel);
    }

    /** @test */
    public function makeOne_BasicStateWithDependencies_ShouldNotOverwriteThem(): void
    {
        // Given some WL and Ticket
        $ticket = new Ticket();
        $wl = new Whitelabel();

        // And fixture with provided dependencies + basic status
        $this->fixture->with(
            function (TicketLine $model) use ($wl, $ticket) {
                $model->ticket = $ticket;
                $model->whitelabel = $wl;
            },
            'basic'
        );

        // When model created
        /** @var TicketLine $model */
        $model = $this->fixture->makeOne();

        // Then existing instance should be assigned
        $this->assertSame($ticket, $model->ticket);
        $this->assertSame($wl, $model->whitelabel);
    }
}
