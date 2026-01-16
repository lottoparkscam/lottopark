<?php

namespace Fuel\Tasks\Seeders;

/**
* Whitelabel Payment Method seeder.
*/
final class Whitelabel_Payment_Method extends Seeder
{
    protected function columnsStaging(): array
    {
        return [
            'whitelabel_payment_method' => ['id', 'whitelabel_id', 'payment_method_id', 'language_id', 'name', 'show', 'data', 'order', 'cost_percent', 'cost_fixed', 'cost_currency_id', 'payment_currency_id']
        ];
    }

    protected function rowsStaging(): array
    {
        return [
            'whitelabel_payment_method' => [
                [1, 1, 1, 1, 'Test payment', 1, serialize([]), 1, '0.00', '10.00', 5, 2],
            ]
        ];
    }

    protected function rowsProduction(): array
    {
        return [];
    }
}
