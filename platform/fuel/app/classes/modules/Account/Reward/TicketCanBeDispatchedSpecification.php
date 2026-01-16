<?php

namespace Modules\Account\Reward;

use Helpers_General;
use InvalidArgumentException;
use Models\WhitelabelRaffleTicket;
use Webmozart\Assert\Assert;

/**
 * Class RewardDispatchableSpecification
 * Determines given ticket is still available to be dispatched.
 */
class TicketCanBeDispatchedSpecification
{
    /**
     * @param WhitelabelRaffleTicket $ticket
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public function isSatisfiedBy(WhitelabelRaffleTicket $ticket): bool
    {
        Assert::notSame($ticket->status, Helpers_General::TICKET_STATUS_PENDING, 'Ticket status can not be pending');
        Assert::isEmpty($ticket->is_paid_out, sprintf('Ticket #%d is already paid out', $ticket->id));
        Assert::notEmpty($ticket->currency, 'Missing currency');
        Assert::notEmpty($ticket->user, 'Missing user');
        Assert::notEmpty($ticket->user->currency, 'Missing user currency');

        return true;
    }
}
