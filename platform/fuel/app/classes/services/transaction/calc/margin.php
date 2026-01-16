<?php

use Models\WhitelabelTransaction;

/**
 * Class Services_Transaction_Calc_Margin
 * Calculates margin/cost/income in currencies of transaction.
 * Only for raffle tickets!
 */
class Services_Transaction_Calc_Margin extends Services_Transaction_Calc_Abstract
{
    public function __construct(Services_Currency_Calc $currency_calc)
    {
        parent::__construct($currency_calc);
    }

    public function calculate(WhitelabelTransaction $transaction, bool $is_bonus = false): void
    {
        $this->verifyTransaction($transaction);

        $cost = $transaction->cost;
        $transaction->cost_usd = $this->convert_to_usd($cost, $transaction);
        $transaction->cost_manager = $this->convert_to_manager($cost, $transaction);

        if ($is_bonus) {
            $this->calculate_bonus($transaction);
        } else {
            $this->calculate_regular($transaction);
        }

        $this->calculate_margin($transaction);
    }

    private function calculate_regular(WhitelabelTransaction $transaction): void
    {
        $amount = $transaction->amount;

        $transaction->income = $amount - $transaction->cost;
        $transaction->income_usd = $this->convert_to_usd($amount, $transaction) - $transaction->cost_usd;
        $transaction->income_manager = $this->convert_to_manager($amount, $transaction) - $transaction->cost_manager;
    }

    private function calculate_bonus(WhitelabelTransaction $transaction): void
    {
        $transaction->income = $this->revert($transaction->cost);
        $transaction->income_usd = $this->revert($transaction->cost_usd);
        $transaction->income_manager = $this->revert($transaction->cost_manager);
    }

    private function calculate_margin(WhitelabelTransaction  $transaction): void
    {
        $whitelabel_raffle = $transaction->whitelabel_raffle_ticket->raffle->whitelabel_raffle;

        if (!$whitelabel_raffle->is_margin_calculation_enabled) {
            $transaction->margin = 0;
            return;
        }

        /** @var object $transaction */
        $transaction->margin_value = $transaction->whitelabel->margin;
        $margin_as_percent = $transaction->margin_value / 100;

        $is_paid_with_bonus_balance = $transaction->bonus_amount > 0;

        if ($is_paid_with_bonus_balance) {
            $transaction->margin = $margin_as_percent * $transaction->cost;
            $transaction->margin_usd = $margin_as_percent * $transaction->cost_usd;
            $transaction->margin_manager = $margin_as_percent * $transaction->cost_manager;
            return;
        }

        $transaction->margin = $margin_as_percent * $transaction->income;

        if ($transaction->margin <= 0) {
            $transaction->margin = 0;
            return;
        }

        $transaction->margin_usd = $margin_as_percent * $transaction->income_usd;
        $transaction->margin_manager = $margin_as_percent * $transaction->income_manager;
    }
}
