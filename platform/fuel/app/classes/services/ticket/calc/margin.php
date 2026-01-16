<?php

use Models\WhitelabelRaffleTicket;

/**
 * Class Services_Ticket_Calc_Margin
 * Calculates margin/cost/income in currencies of ticket.
 */
class Services_Ticket_Calc_Margin extends Services_Ticket_Calc_Abstract
{
    private Services_Ticket_Calc_Wrappers_Cost $cost_calc;

    public function __construct(Services_Currency_Calc $currency_calc, Services_Ticket_Calc_Wrappers_Cost $cost_calc)
    {
        parent::__construct($currency_calc);
        $this->cost_calc = $cost_calc;
    }

    public function calculate(WhitelabelRaffleTicket $ticket, bool $is_bonus = false): void
    {
        $this->verifyTicket($ticket);

        $cost_local = $this->cost_calc->calculate_raffle_cost($ticket); # line_cost + fee irl
        $ticket->cost_local = $cost_local;
        $cost = $this->currency_calc->convert_to_any($cost_local, $ticket->raffle->currency->code, $ticket->user->currency->code);

        $ticket->cost = $cost;
        $cost_usd = $this->convert_to_usd($cost, $ticket);
        $cost_manager = $this->convert_to_manager($cost, $ticket);

        $ticket->cost_usd = $cost_usd;
        $ticket->cost_local = $cost_local;
        $ticket->cost_manager = $cost_manager;

        if ($is_bonus) {
            $this->calculate_bonus($ticket);
        } else {
            $this->calculate_regular($ticket);
        }

        $this->calculate_margin($ticket);
    }

    private function calculate_regular(WhitelabelRaffleTicket $ticket): void
    {
        $amount = $ticket->amount;

        $ticket->income = $amount - $ticket->cost;
        $ticket->income_usd = $this->convert_to_usd($amount, $ticket) - $ticket->cost_usd;
        $ticket->income_local = $this->convert_to_local($amount, $ticket) - $ticket->cost_local;
        $ticket->income_manager = $this->convert_to_manager($amount, $ticket) - $ticket->cost_manager;
    }

    private function calculate_bonus(WhitelabelRaffleTicket $ticket): void
    {
        /** @var object $ticket */
        $ticket->bonus_cost = $ticket->cost;
        $ticket->bonus_cost_usd = $ticket->cost_usd;
        $ticket->bonus_cost_local = $ticket->cost_local;
        $ticket->bonus_cost_manager = $ticket->cost_manager;

        $ticket->income = $this->revert($ticket->cost);
        $ticket->income_usd = $this->revert($ticket->cost_usd);
        $ticket->income_local = $this->revert($ticket->cost_local);
        $ticket->income_manager = $this->revert($ticket->cost_manager);
    }

    private function calculate_margin(WhitelabelRaffleTicket  $ticket): void
    {
        $whitelabel_raffle = $ticket->raffle->whitelabel_raffle;

        if (!$whitelabel_raffle->is_margin_calculation_enabled) {
            $ticket->margin = 0;
            return;
        }

        $ticket->margin_value = $ticket->whitelabel->margin;
        $margin_as_percent = $ticket->margin_value / 100;

        $is_paid_with_bonus_balance = $ticket->bonus_amount > 0;

        if ($is_paid_with_bonus_balance) {
            $ticket->margin = $margin_as_percent * $ticket->cost;
            $ticket->margin_usd = $margin_as_percent * $ticket->cost_usd;
            $ticket->margin_local = $margin_as_percent * $ticket->cost_local;
            $ticket->margin_manager = $margin_as_percent * $ticket->cost_manager;
            return;
        }

        $ticket->margin = $margin_as_percent * $ticket->income;

        if ($ticket->margin <= 0) {
            $ticket->margin = 0;
            return;
        }

        $ticket->margin_usd = $margin_as_percent * $ticket->income_usd;
        $ticket->margin_local = $margin_as_percent * $ticket->income_local;
        $ticket->margin_manager = $margin_as_percent * $ticket->income_manager;
    }
}
