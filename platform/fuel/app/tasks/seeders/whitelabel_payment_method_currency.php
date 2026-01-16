<?php

namespace Fuel\Tasks\Seeders;

/**
* Whitelabel Payment Method Currency seeder.
*/
final class Whitelabel_Payment_Method_Currency extends Seeder
{

    protected function columnsStaging(): array
    {
        return [
            'whitelabel_payment_method_currency' => ['id', 'whitelabel_payment_method_id', 'currency_id', 'is_zero_decimal', 'min_purchase', 'is_default', 'is_enabled']
        ];
    }

    protected function rowsStaging(): array
    {
        return [
            'whitelabel_payment_method_currency' => [
                [1, 1, 2, 0, '1.00', 1, 1],
            ]
        ];
    }

    protected function rowsProduction(): array
    {
        return [];
    }

}
