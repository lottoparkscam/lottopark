<?php

namespace Modules\Account\Balance;

use Models\WhitelabelRaffleTicket;

/**
 * Charges user account from his regular balance.
 */
class BonusBalance extends AbstractBalance implements BalanceContract
{
    public const COLUMN_NAME = 'bonus_balance';

    public function source(): string
    {
        return self::COLUMN_NAME;
    }

    public function getTicketAmountToPayInUserCurrency(WhitelabelRaffleTicket $ticket): float
    {
        return $ticket->transaction->bonus_amount;
    }
}
