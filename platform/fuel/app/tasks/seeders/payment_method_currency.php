<?php

namespace Fuel\Tasks\Seeders;

final class Payment_Method_Currency extends Seeder
{
    protected function columnsStaging(): array
    {
        return [
            'payment_method_currency' => ['id', 'whitelabel_payment_method_id', 'currency_id', 'min_purchase']
        ];
    }

    protected function rowsStaging(): array
    {
        return [
            'payment_method_currency' => [
                [1, 1, 1, '1.00'],
            ]
        ];
    }
}
