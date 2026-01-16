<?php

use Webmozart\Assert\Assert;
use Models\WhitelabelRaffleTicket;

abstract class Services_Ticket_Calc_Abstract
{
    protected Services_Currency_Calc $currency_calc;

    public function __construct(Services_Currency_Calc $currency_calc)
    {
        $this->currency_calc = $currency_calc;
    }

    abstract public function calculate(WhitelabelRaffleTicket $ticket): void;

    protected function verifyTicket(WhitelabelRaffleTicket $ticket): void
    {
        Assert::notEmpty($ticket->lines, 'Ticket lines relation can not be empty.');
        Assert::notEmpty($ticket->whitelabel, 'Whitelabel relation can not be empty.');
        Assert::notEmpty($ticket->rule, 'Rule relation can not be empty.');
        Assert::notEmpty($ticket->user, 'User relation can not be empty.');
    }

    protected function get_whitelabel_currency_code(WhitelabelRaffleTicket $ticket): string
    {
        return $ticket->whitelabel->currency->code;
    }

    protected function convert_to_usd(float $value, WhitelabelRaffleTicket $ticket): float
    {
        return $this->currency_calc->convert_to_any($value, $ticket->user->currency->code, 'USD');
    }

    protected function convert_to_local(float $value, WhitelabelRaffleTicket $ticket): float
    {
        return $this->currency_calc->convert_to_any($value, $ticket->user->currency->code, $ticket->raffle->currency->code);
    }

    protected function convert_to_manager(float $value, WhitelabelRaffleTicket $ticket): float
    {
        return $this->currency_calc->convert_to_any($value, $ticket->user->currency->code, $this->get_whitelabel_currency_code($ticket));
    }

    protected function revert(float $value): float
    {
        return $this->currency_calc->revert_value($value);
    }
}
