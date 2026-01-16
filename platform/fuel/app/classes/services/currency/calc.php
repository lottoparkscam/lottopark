<?php

use Models\Currency;

/**
 * Class Services_Currency_Calc
 * Wrapper around currency helper.
 */
class Services_Currency_Calc
{
    private Helpers_Currency $currency_helper;
    private Currency $currency_dao;

    private array $currency_cache = [];

    public function __construct(Helpers_Currency $currency_helper, Currency $currency)
    {
        $this->currency_helper = $currency_helper;
        $this->currency_dao = $currency;
    }

    public function convert_to_any(float $amount, string $from_currency_code, string $to_currency_code): float
    {
        return (float)$this->currency_helper::convert_to_any((string)$amount, $from_currency_code, $to_currency_code);
    }

    public function convert_to_user_currency(float $amount, string $from_currency_code, array $user): array
    {
        $user_currency = $this->get_currency($user['currency_id']);
        return [
            'amount' => (float)$this->currency_helper::convert_to_any((string)$amount, $from_currency_code, $user_currency->code),
            'currency' => $user_currency->code
        ];
    }

    private function get_currency(int $currency_id): Currency
    {
        if (!isset($this->currency_cache[$currency_id])) {
            $this->currency_cache[$currency_id] = $this->currency_dao->get_by_id($currency_id);
        }
        return $this->currency_cache[$currency_id];
    }

    public function revert_value(float $value): float
    {
        return (-1 * $value);
    }
}
