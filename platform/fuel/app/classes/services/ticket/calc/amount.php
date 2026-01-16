<?php

use Models\WhitelabelRaffleTicket;

/**
 * Class Services_Ticket_Calc_Amount
 * Calculates amounts in currencies of ticket and it's lines.
 */
class Services_Ticket_Calc_Amount extends Services_Ticket_Calc_Abstract
{
    public function calculate(WhitelabelRaffleTicket $ticket): void
    {
        $this->verifyTicket($ticket);
        $ticket->amount = 0;
        foreach ($ticket->lines as $line) {
            if (!$line->amount) {
                continue;
            }

            $line->amount_usd = $this->convert_to_usd($line->amount, $ticket);
            $ticket->amount_usd += $line->amount_usd;

            $line->amount_local = $this->convert_to_local($line->amount, $ticket);
            $ticket->amount_local += $line->amount_local;

            $line->amount_manager = $this->convert_to_manager($line->amount, $ticket);
            $ticket->amount_manager += $line->amount_manager;

            # @see https://ggintsoftware.slack.com/archives/GALAKBCBZ/p1600259611100400?thread_ts=1598958928.131000&cid=GALAKBCBZ
            # paid by balance, so payment currency = user currency
            $line->amount_payment = $line->amount;
            $ticket->amount += $line->amount;
            $ticket->amount_payment += $line->amount_payment;
        }
    }

    public function calculate_bonus(WhitelabelRaffleTicket $ticket): void
    {
        $this->verifyTicket($ticket);
        foreach ($ticket->lines as $line) {
            if (!$line->bonus_amount) {
                continue;
            }

            $line->bonus_amount_usd = $this->convert_to_usd($line->bonus_amount, $ticket);
            $ticket->bonus_amount_usd += $line->bonus_amount_usd;

            $line->bonus_amount_local = $this->convert_to_local($line->bonus_amount, $ticket);
            $ticket->bonus_amount_local += $line->bonus_amount_local;

            $line->bonus_amount_manager = $this->convert_to_manager($line->bonus_amount, $ticket);
            $ticket->bonus_amount_manager += $line->bonus_amount_manager;

            # @see https://ggintsoftware.slack.com/archives/GALAKBCBZ/p1600259611100400?thread_ts=1598958928.131000&cid=GALAKBCBZ
            # paid by balance, so payment currency = user currency
            $line->bonus_amount_payment = $line->bonus_amount;
            $ticket->bonus_amount += $line->bonus_amount;
            $ticket->bonus_amount_payment += $line->bonus_amount_payment;
        }
    }
}
