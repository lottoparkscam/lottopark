<?php

namespace Unit\Fixtures\Raffle;

use Helpers_General;
use InvalidArgumentException;
use Models\Raffle as Raffle;
use Models\WhitelabelRaffleTicket as Ticket;
use Test_Unit;
use Tests\Fixtures\Raffle\RaffleFixture;
use Tests\Fixtures\Raffle\RaffleTicketFixture;

/**
 * @group fixture
 * @covers \Tests\Fixtures\Raffle\RaffleTicketFixture
 */
final class RaffleTicketFixtureTest extends Test_Unit
{
    private RaffleTicketFixture $fixture;
    private RaffleFixture $raffleFixture;

    public function setUp(): void
    {
        parent::setUp();
        $this->fixture = $this->container->get(RaffleTicketFixture::class);
        $this->raffleFixture = $this->container->get(RaffleFixture::class);
    }

    /** @test */
    public function forRaffle_ShouldUseGivenRaffle(): void
    {
        // Given basic raffle
        /** @var Raffle $raffle */
        $raffle = $this->raffleFixture->with('basic')->makeOne();

        // Add ticket fixture related with given raffle
        $this->fixture->forRaffle($raffle);

        // When created many models
        /** @var Ticket[] $models */
        $models = $this->fixture->with('basic')->makeMany([], 5);

        // Then all created entries should contain the same raffle and raffle currency
        foreach ($models as $ticket) {
            $this->assertSame($raffle, $ticket->raffle);
            $this->assertSame($raffle->currency, $ticket->currency);
        }
    }

    /** @test */
    public function generateTickets_TicketsCountIsGreaterThanLinesCount_ThrowsException(): void
    {
        // Given basic raffle
        /** @var Raffle $raffle */
        $raffle = $this->raffleFixture->makeOne();

        // Except invalid argument exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tickets count must be less than lines count');

        // When generate tickets is called with tickets greater than lines count
        $this->fixture->generateTickets(
            $raffle,
            $this->fixture::PENDING,
            100,
            10
        );
    }

    /** @test */
    public function generateTickets_TicketStatusNotSupported_ThrowsException(): void
    {
        // Given basic raffle
        /** @var Raffle $raffle */
        $raffle = $this->raffleFixture->makeOne();

        // Except invalid argument exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected one of: "pending", "won", "lost". Got: "some state"');

        // When generate tickets is called with not supported state
        $this->fixture->generateTickets($raffle, 'some state');
    }

    /** @test */
    public function generateTickets_NoParamsGiven_CreatesPoolWIthPendingStatus(): void
    {
        // Given basic raffle
        /** @var Raffle $raffle */
        $raffle = $this->raffleFixture->with('basic')->makeOne();

        $expectedLinesCount = $raffle->max_bets;

        // When generate tickets is called without any params
        $tickets = $this->fixture->generateTickets($raffle);

        // Then full raffle pool should be generated with pending status
        $actualLinesCount = 0;
        foreach ($tickets as $ticket) {
            $this->assertSame(Helpers_General::TICKET_STATUS_PENDING, $ticket->status);
            $actualLinesCount += count($ticket->lines);
        }
    }
}
