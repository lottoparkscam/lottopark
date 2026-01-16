<?php

use Models\WhitelabelRaffleTicket;
use Models\WhitelabelTransaction;

/**
 * Class Services_Transaction_Calc_Amount
 * Calculates amounts in currencies of transaction.
 */
class Services_Transaction_Calc_Amount extends Services_Transaction_Calc_Abstract
{
    public function calculate(WhitelabelTransaction $transaction, WhitelabelRaffleTicket $ticket = null): void
    {
        $this->verifyTransaction($transaction);
		foreach ($ticket->lines as $line) {
			if (!$line->amount) {
				continue;
			}
			$transaction->amount_usd += $line->amount_usd;
			$transaction->amount_manager += $line->amount_manager;
			$transaction->amount += $line->amount;
			$transaction->amount_payment += $line->amount_payment;
		}
    }

    public function calculate_bonus(WhitelabelTransaction $transaction, WhitelabelRaffleTicket $ticket): void
    {
        $this->verifyTransaction($transaction);
		foreach ($ticket->lines as $line) {
			if (!$line->bonus_amount) {
				continue;
			}

			$transaction->bonus_amount_usd += $line->bonus_amount_usd;
			$transaction->bonus_amount_manager += $line->bonus_amount_manager;
            $transaction->bonus_amount += $line->bonus_amount;
			$transaction->bonus_amount_payment += $line->bonus_amount_payment;
		}
    }
}
