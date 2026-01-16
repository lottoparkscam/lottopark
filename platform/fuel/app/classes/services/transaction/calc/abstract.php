<?php

use Webmozart\Assert\Assert;
use Models\WhitelabelTransaction;

/**
 * Class Services_Transaction_Calc_Abstract
 */
abstract class Services_Transaction_Calc_Abstract
{
    protected Services_Currency_Calc $currency_calc;

    public function __construct(Services_Currency_Calc $currency_calc)
    {
        $this->currency_calc = $currency_calc;
    }

    abstract public function calculate(WhitelabelTransaction $transaction): void;

    protected function verifyTransaction(WhitelabelTransaction $transaction): void
    {
        Assert::notEmpty($transaction->whitelabel, 'Whitelabel relation can not be empty.');
        Assert::notEmpty($transaction->user, 'User relation can not be empty.');
    }

    protected function get_whitelabel_currency_code(WhitelabelTransaction $transaction): string
    {
        return $transaction->whitelabel->currency->code;
    }

    protected function convert_to_usd(float $value, WhitelabelTransaction $transaction): float
    {
        return $this->currency_calc->convert_to_any($value, $transaction->currency->code, 'USD');
    }

    protected function convert_to_manager(float $value, WhitelabelTransaction $transaction): float
    {
        return $this->currency_calc->convert_to_any($value, $transaction->currency->code, $this->get_whitelabel_currency_code($transaction));
    }

    protected function revert(float $value): float
    {
        return $this->currency_calc->revert_value($value);
    }
}
