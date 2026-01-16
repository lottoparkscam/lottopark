<?php

namespace Feature\Fixtures\Raffle;

use Exception;
use Helpers_General;
use Models\Raffle as Raffle;
use Models\WhitelabelRaffleTicket;
use Test_Feature;
use Tests\Fixtures\Raffle\RaffleFixture;
use Tests\Fixtures\Raffle\RaffleTicketFixture;

/**
 * @group fixture
 * @covers \Tests\Fixtures\Raffle\RaffleTicketFixture
 */
final class RaffleTicketFixtureTest extends Test_Feature
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
    public function generateTickets_NoParamsGiven_CreatesPoolWIthPendingStatus(): void
    {
        // Given basic raffle
        /** @var Raffle $raffle */
        $raffle = $this->raffleFixture->with('basic', 'bets50')->makeOne();

        // When generate tickets is called without any params
        $tickets = $this->fixture->generateTickets($raffle);
        $this->fixture->persist(...$tickets);

        // Then full raffle pool should be generated with pending status
        /** @var WhitelabelRaffleTicket[] $freshTickets */
        $freshTickets = WhitelabelRaffleTicket::query()->where(['id', '=', $raffle->id])->get();
        $actualLinesCount = 0;
        foreach ($freshTickets as $ticket) {
            $this->assertSame(Helpers_General::TICKET_STATUS_PENDING, $ticket->status);
            $actualLinesCount += count($ticket->lines);
        }

        $this->assertSame($raffle->max_bets, $actualLinesCount);
    }

    /** @test */
    public function generateTicket_TicketsCountProvided_ThrowsException(): void
    {
        // Given basic raffle
        /** @var Raffle $raffle */
        $raffle = $this->raffleFixture->with('basic', 'bets50')->makeOne();

        // Then exception with information should be thrown
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('There is no ticket count mechanism implemented yet.');

        // When generate tickets with concrete count requested
        $ticketsCount = 10;
        $linesCount = 100;
        $this->fixture->generateTickets($raffle, 'pending', $ticketsCount, $linesCount);
    }
}
