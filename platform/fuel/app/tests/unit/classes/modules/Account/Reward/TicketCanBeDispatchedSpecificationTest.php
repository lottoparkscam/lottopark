<?php

namespace Unit\Modules\Account\Reward;

use Helpers_General;
use InvalidArgumentException;
use Models\WhitelabelRaffleTicket;
use Modules\Account\Reward\TicketCanBeDispatchedSpecification;
use Test_Unit;

class TicketCanBeDispatchedSpecificationTest extends Test_Unit
{
    private WhitelabelRaffleTicket $ticket;
    private TicketCanBeDispatchedSpecification $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->ticket = $this->get_ticket();
        $this->ticket->status = Helpers_General::TICKET_STATUS_WIN;
        $this->ticket->is_paid_out = false;

        $this->service = new TicketCanBeDispatchedSpecification();
    }

    /** @test */
    public function isSatisfiedBy__valid_ticket__returns_true(): void
    {
        // Given
        $ticket = $this->ticket;

        // When & Then
        $this->assertTrue($this->service->isSatisfiedBy($ticket));
    }

    /** @test */
    public function isSatisfiedBy__status_is_not_pending__throws_invalid_argument_exception(): void
    {
        // Except
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ticket status can not be pending');

        // Given
        $ticket = $this->ticket;
        $ticket->status = Helpers_General::TICKET_STATUS_PENDING;

        // When
        $this->service->isSatisfiedBy($ticket);
    }

    /** @test */
    public function isSatisfiedBy__ticket_is_already_paid_out__throws_invalid_argument_exception(): void
    {
        // Except
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Ticket #%d is already paid out', $this->ticket->id));

        // Given
        $ticket = $this->ticket;
        $ticket->is_paid_out = true;

        // When
        $this->service->isSatisfiedBy($ticket);
    }

    /** @test */
    public function isSatisfiedBy__ticket_has_not_currency__throws_invalid_argument_exception(): void
    {
        // Except
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing currency');

        // Given
        $ticket = $this->ticket;
        unset($ticket->currency);

        // When
        $this->service->isSatisfiedBy($ticket);
    }

    /** @test */
    public function isSatisfiedBy__ticket_has_not_user__throws_invalid_argument_exception(): void
    {
        // Except
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing user');

        // Given
        $ticket = $this->ticket;
        unset($ticket->user);

        // When
        $this->service->isSatisfiedBy($ticket);
    }

    /** @test */
    public function isSatisfiedBy__ticket_has_not_user_currency__throws_invalid_argument_exception(): void
    {
        // Except
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing user currency');

        // Given
        $ticket = $this->ticket;
        unset($ticket->user->currency);

        // When
        $this->service->isSatisfiedBy($ticket);
    }
}
