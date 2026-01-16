<?php

namespace Modules\Account\Balance;

use Models\{
    WhitelabelRaffleTicket,
    WhitelabelUserBonus
};

class WelcomeBonusBalance extends BonusBalance
{
    public WhitelabelUserBonus $bonus;

    public function getTicketAmountToPayInUserCurrency(WhitelabelRaffleTicket $ticket): float
    {
        return 0.00;
    }
}
