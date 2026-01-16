<?php

use Models\Raffle;
use Fuel\Core\Date;
use Models\Whitelabel;
use Models\WhitelabelUser;
use Models\WhitelabelTransaction;
use Models\WhitelabelRaffleTicket;
use Models\WhitelabelRaffleTicketLine;
use Modules\Account\Balance\BalanceContract;

/**
 * Class Services_Raffle_Factory_Ticket
 * Creates ticket from LCS response data and triggers calc services to assign amount values etc.
 */
class Services_Raffle_Factory_Ticket
{
    private Services_Currency_Calc $currency_calc;
    private Services_Raffle_Token_Transaction_Resolver $transaction_token_resolver;
    private Services_Ticket_Calc_Amount $ticket_amount_calc;
    private Services_Transaction_Calc_Amount $transaction_amount_calc;
    private Whitelabel $whitelabel_dao;
    private Services_Ticket_Calc_Margin $ticket_margin_calc;
    private Services_Transaction_Calc_Margin $transaction_margin_calc;

    public function __construct(
        Services_Currency_Calc $currency_calc,
        Services_Ticket_Calc_Amount $ticket_amount_calc,
        Services_Transaction_Calc_Amount $transaction_amount_calc,
        Whitelabel $whitelabel,
        Services_Ticket_Calc_Margin $margin_calc,
        Services_Transaction_Calc_Margin $transaction_margin_calc,
        Services_Raffle_Token_Transaction_Resolver $transaction_token_resolver
    ) {
        $this->currency_calc = $currency_calc;
        $this->transaction_token_resolver = $transaction_token_resolver;
        $this->ticket_amount_calc = $ticket_amount_calc;
        $this->transaction_amount_calc = $transaction_amount_calc;
        $this->whitelabel_dao = $whitelabel;
        $this->ticket_margin_calc = $margin_calc;
        $this->transaction_margin_calc = $transaction_margin_calc;
    }

    public function create_from_lcs_ticket_data(int $whitelabel_id, array $lcs_ticket, Raffle $raffle, WhitelabelUser $user, array $ticket_numbers, BalanceContract $balance): WhitelabelRaffleTicket
    {
        $payment_method_type = (string)$balance;
        # lines
        $lines = [];

        $user_line_price = $this->currency_calc->convert_to_any(
            $raffle->getFirstRule()->line_price + $raffle->getFirstRule()->fee,
            $raffle->getFirstRule()->currency->code,
            $user->currency->code
        );

        foreach ($ticket_numbers as $ticket_number) {
            $line = new WhitelabelRaffleTicketLine();
            $line->number = $ticket_number;
            # TODO: this code must be refactored, it's breaking sol-I-d & dry principle (full description below)
            switch ($payment_method_type) {
                case Helpers_General::PAYMENT_TYPE_BALANCE:
                    $line->amount = $user_line_price;
                break;
                case Helpers_General::PAYMENT_TYPE_BONUS_BALANCE:
                    $line->bonus_amount = $user_line_price;
                break;
            }
            $line->whitelabel_id = $whitelabel_id;
            $lines[] = $line;
        }

        if (!$balance->isWelcomeBonus()) {
            # transaction
            $transaction = new WhitelabelTransaction();

            $transaction->currency_id = $user->currency_id;
            $transaction->currency = $user->currency;
            $transaction->payment_currency_id = $user->currency_id;
            $transaction->payment_method_type = $payment_method_type;

            # todo: verify why we have dupes in regular ticket, raffle tickets and transactions (income, manager etc)
            # https://ggintsoftware.slack.com/archives/GALAKBCBZ/p1600161898002800

            $transaction->status = Helpers_General::STATUS_TRANSACTION_APPROVED;
            $transaction->whitelabel_id = $whitelabel_id;
            $transaction->date = Date::forge(null, $user->timezone)->format('mysql');
            $transaction->token = $this->transaction_token_resolver->issue($whitelabel_id);
            $transaction->type = Helpers_General::TYPE_TRANSACTION_PURCHASE;
            $transaction->whitelabel_user_id = $user->id;
            $transaction->date_confirmed = Date::forge()->format('mysql');
        }

        # ticket
        $ticket = new WhitelabelRaffleTicket($lcs_ticket);
        $ticket->whitelabel_user_id = $user->id;
        $ticket->whitelabel_id = $whitelabel_id;
        $ticket->currency = $user->currency;
        $ticket->currency_id = $user->currency->id;
        $ticket->raffle_id = $raffle->id;
        $ticket->raffle_rule_id = $raffle->getFirstRule()->id ?? $raffle->raffle_rule_id;

        $ticket->user = $user;
        $ticket->whitelabel_user_id = $user->id;

        $ticket->whitelabel = $this->get_whitelabel($whitelabel_id);
        $ticket->whitelabel_id = $ticket->whitelabel->id;

        if (isset($transaction)) {
            $ticket->transaction = $transaction;
            $transaction->whitelabel_raffle_ticket = $ticket;
        }

        $ticket->lines = $lines;
        $ticket->line_count = count($lines);

        # TODO: this code must be refactored, it's breaking sol-I-d & dry principle.
        # when we add another balance method then we have to add another switch case
        # these lines are duplicated in many places (in ticket amount calc, which
        # can be abstract). The real diff is only name of the field.

        switch ($payment_method_type) {
            case Helpers_General::PAYMENT_TYPE_BALANCE:
                $this->ticket_amount_calc->calculate($ticket);
                $this->ticket_margin_calc->calculate($ticket);

                if (isset($transaction)) {
                    $transaction->cost = $ticket->cost;
                    $transaction->amount = 0;

                    $this->transaction_amount_calc->calculate($transaction, $ticket);
                    $this->transaction_margin_calc->calculate($transaction);
                }

            break;
            case Helpers_General::PAYMENT_TYPE_BONUS_BALANCE:
            case Helpers_General::PAYMENT_TYPE_WELCOME_BONUS_BALANCE:
                $ticket->amount = 0;
                $this->ticket_amount_calc->calculate_bonus($ticket);
                $this->ticket_margin_calc->calculate($ticket, true);

                if (isset($transaction)) {
                    $transaction->cost = $ticket->cost;
                    $transaction->bonus_amount = 0;
                    $this->transaction_amount_calc->calculate_bonus($transaction, $ticket);
                    $this->transaction_margin_calc->calculate($transaction, true);
                }
            break;
        }

        return $ticket;
    }

    private function get_whitelabel(int $whitelabel_id): Whitelabel
    {
        return $this->whitelabel_dao->get_by_id($whitelabel_id, ['currency']);
    }
}
