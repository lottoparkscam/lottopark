<?php

namespace Modules\Account\Reward;

use Models\WhitelabelRaffleTicket;
use Modules\Account\Reward\Strategy\CashPrizeDispatcher;
use Modules\Account\Reward\Strategy\PrizeInKindDispatcher;
use Modules\Account\Reward\Strategy\TicketPrizeDispatcher;
use Services\Shared\AbstractDispatchAble;
use Services_Lcs_Raffle_Ticket_Store_Contract;

/**
 * Class RewardDispatcher
 * Determines what RewardStrategy should be applied and performs it on received Ticket.
 */
class RewardDispatcher extends AbstractDispatchAble
{
    private Services_Lcs_Raffle_Ticket_Store_Contract $storeApi;

    private array $paidOutTicketUuids = [];
    private CashPrizeDispatcher $cashMethod;
    private PrizeInKindDispatcher $prizeMethod;
    private TicketPrizeDispatcher $ticketMethod;
    private TicketCanBeDispatchedSpecification $ticketSpecification;

    public function __construct(
        Services_Lcs_Raffle_Ticket_Store_Contract $storeApi,
        CashPrizeDispatcher $cashMethod,
        PrizeInKindDispatcher $prizeMethod,
        TicketPrizeDispatcher $ticketMethod,
        TicketCanBeDispatchedSpecification $ticketSpecification
    ) {
        $this->storeApi = $storeApi;
        $this->cashMethod = $cashMethod;
        $this->prizeMethod = $prizeMethod;
        $this->ticketMethod = $ticketMethod;
        $this->ticketSpecification = $ticketSpecification;
    }

    /**
     * @param WhitelabelRaffleTicket $ticket
     *        should be with assigned prizes, updated all fields and ready
     *        to be persisted in DB. Here we are working only with final
     *        prize dispatching.
     */
    public function dispatchTicket(WhitelabelRaffleTicket $ticket): void
    {
        $this->verifyTicket($ticket);

        foreach ($ticket->lines as $line) {
            foreach ($this->strategies() as $strategy) {
                $strategy->dispatchPrize($line);
            }
        }

        $this->markAsPaidOut($ticket);
    }

    /**
     * @return RewardDispatchingStrategyContract[]
     */
    private function strategies(): array
    {
        return [$this->cashMethod, $this->prizeMethod, $this->ticketMethod];
    }

    private function markAsPaidOut(WhitelabelRaffleTicket $ticket): void
    {
        $this->enqueue($ticket->raffle->slug);

        if ($ticket->can_be_paid_out) {
            $this->paidOutTicketUuids[] = $ticket->uuid;
            $ticket->is_paid_out = true;
        }
    }

    private function verifyTicket(WhitelabelRaffleTicket $ticket): void
    {
        $this->ticketSpecification->isSatisfiedBy($ticket);
    }

    protected function enqueue(...$args): void
    {
        [$raffleSlug] = $args;
        $this->set_task(function () use (&$raffleSlug) {
            if ($hasUuids = !empty($this->paidOutTicketUuids)) {
                $payload = ['uuids' => $this->paidOutTicketUuids];
                $this->storeApi->request($payload, $raffleSlug);
            }

            if ($this->cashMethod->is_enqueued()) {
                $this->cashMethod->dispatch();
            }
        });
    }
}
