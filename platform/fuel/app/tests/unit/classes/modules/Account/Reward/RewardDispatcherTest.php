<?php

namespace Unit\Modules\Account\Reward;

use Helpers_General;
use Models\WhitelabelRaffleTicketLine;
use Modules\Account\Reward\PrizeType;
use Modules\Account\Reward\RewardDispatcher;
use Modules\Account\Reward\Strategy\CashPrizeDispatcher;
use Modules\Account\Reward\Strategy\PrizeInKindDispatcher;
use Modules\Account\Reward\Strategy\TicketPrizeDispatcher;
use Modules\Account\Reward\TicketCanBeDispatchedSpecification;
use Services_Lcs_Raffle_Ticket_Store_Contract;
use Test_Unit;

class RewardDispatcherTest extends Test_Unit
{
    private Services_Lcs_Raffle_Ticket_Store_Contract $store_api;
    private CashPrizeDispatcher $cash_method;
    private PrizeInKindDispatcher $prize_method;
    private TicketPrizeDispatcher $ticket_method;
    private TicketCanBeDispatchedSpecification $ticket_specification;
    private RewardDispatcher $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->store_api = $this->createMock(Services_Lcs_Raffle_Ticket_Store_Contract::class);
        $this->cash_method = $this->createMock(CashPrizeDispatcher::class);
        $this->prize_method = $this->createMock(PrizeInKindDispatcher::class);
        $this->ticket_method = $this->createMock(TicketPrizeDispatcher::class);
        $this->ticket_specification = $this->createMock(TicketCanBeDispatchedSpecification::class);

        $this->service = new RewardDispatcher(
            $this->store_api,
            $this->cash_method,
            $this->prize_method,
            $this->ticket_method,
            $this->ticket_specification
        );
    }

    /** @test */
    public function dispatch_ticket__valid_cash_type_ticket__apply_through_all_strategies(): void
    {
        // Given
        $ticket = $this->get_ticket();
        $ticket->status = Helpers_General::TICKET_STATUS_WIN;
        $ticket->is_paid_out = false;
        $lines_count = count($ticket->lines);

        # lcs request
        $payload['uuids'] = [$ticket->uuid];
        $raffle_slug = $ticket->raffle->slug;
        $raffle_type = 'closed';

        $this->assertNotEmpty($ticket->lines);

        $this->ticket_specification
            ->expects($this->once())
            ->method('isSatisfiedBy')
            ->with($ticket)
            ->willReturn(true);

        # dispatchable
        $this->cash_method
            ->expects($this->once())
            ->method('is_enqueued')
            ->willReturn(true);

        $this->cash_method
            ->expects($this->once())
            ->method('dispatch');


        $this->cash_method
            ->expects($this->exactly($lines_count))
            ->method('dispatchPrize')
            ->withConsecutive(
                [$ticket->lines[0]],
                [$ticket->lines[1]],
                [$ticket->lines[2]],
                [$ticket->lines[3]],
                [$ticket->lines[4]],
            );

        $this->prize_method
            ->expects($this->exactly($lines_count))
            ->method('dispatchPrize')
            ->withConsecutive(
                [$ticket->lines[0]],
                [$ticket->lines[1]],
                [$ticket->lines[2]],
                [$ticket->lines[3]],
                [$ticket->lines[4]],
            );

        $this->ticket_method
            ->expects($this->exactly($lines_count))
            ->method('dispatchPrize')
            ->withConsecutive(
                [$ticket->lines[0]],
                [$ticket->lines[1]],
                [$ticket->lines[2]],
                [$ticket->lines[3]],
                [$ticket->lines[4]],
            );

        $this->store_api
            ->expects($this->once())
            ->method('request')
            ->with($payload, $raffle_slug, $raffle_type);

        // When
        $this->service->dispatchTicket($ticket);
        $this->service->dispatch();

        // Then
        $this->assertTrue($ticket->is_paid_out);
    }

    /** @test */
    public function dispatch_ticket__valid_ticket__does_not_dispatchPrize(): void
    {
        // Given
        $ticket = $this->get_ticket();
        $ticket->status = Helpers_General::TICKET_STATUS_WIN;
        $ticket->is_paid_out = false;

        # dispatchable
        $this->cash_method
            ->expects($this->once())
            ->method('is_enqueued')
            ->willReturn(false);

        // When
        $this->service->dispatchTicket($ticket);
        $this->service->dispatch();

        // Then
        $this->cash_method
            ->expects($this->never())
            ->method('dispatch');
    }

    /** @test */
    public function dispatch_ticket__enqueue_has_no_uuids__not_calls_lcs(): void
    {
        // Given
        $ticket = $this->get_ticket();
        $ticket->status = Helpers_General::TICKET_STATUS_WIN;
        $ticket->is_paid_out = false;

        $line = $this->createMock(WhitelabelRaffleTicketLine::class);
        $line->
            expects($this->once())
            ->method('prizeType')
            ->willReturn(PrizeType::TICKET());

        $ticket->lines = [$line];

        # dispatchable
        $this->store_api
            ->expects($this->never())
            ->method('request');

        // When
        $this->service->dispatchTicket($ticket);
        $this->service->dispatch();
    }
}
