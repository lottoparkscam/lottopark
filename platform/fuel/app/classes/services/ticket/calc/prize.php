<?php

use Models\WhitelabelRaffleTicket;

/**
 * Class Services_Ticket_Calc_Amount
 * Calculates prizes in user currency.
 */
class Services_Ticket_Calc_Prize extends Services_Ticket_Calc_Abstract
{
    public function calculate(WhitelabelRaffleTicket $ticket): void
    {
        $this->verifyTicket($ticket);

        $whitelabel_currency_code = $this->get_whitelabel_currency_code($ticket);

        if (empty($ticket->prize_local)) { # float
            return;
        }

        foreach ($ticket->lines as $line) {
            if (empty($line->raffle_prize)) { # relation
                continue;
            }

            $per_user_in_lottery_currency = $line->raffle_prize->per_user;
            $per_user_in_user_currency = $this->currency_calc->convert_to_any(
                $per_user_in_lottery_currency,
                $ticket->raffle->currency->code,
                $ticket->user->currency->code
            );

            /** @var object $line */
            $line->prize = $per_user_in_user_currency;
            $ticket->prize += $line->prize;

            $line->prize_usd = $this->convert_to_usd($per_user_in_user_currency, $ticket);
            $ticket->prize_usd += $line->prize_usd;

            $line->prize_manager = $this->convert_to_manager($per_user_in_user_currency, $ticket);
            $ticket->prize_manager += $line->prize_manager;

            $line->prize_local= $per_user_in_lottery_currency;
        }
    }
}
