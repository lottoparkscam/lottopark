<?php

namespace Fuel\Tasks\Seeders;

final class PayOp extends Seeder
{

    protected function columnsStaging(): array
    {
        return [
            'payment_method' => ['id', 'name'],
            'payment_method_supported_currency' => ['payment_method_id', 'code'],
        ];
    }

    protected function rowsStaging(): array
    {
        return [
            'payment_method' => [
                [34, 'PayOp']
            ],
            'payment_method_supported_currency' => [
                [34, 'USD'],
                [34, 'UAH'],
                [34, 'RUB'],
                [34, 'AUD'],
                [34, 'CAD'],
                [34, 'CHF'],
                [34, 'GBP'],
                [34, 'EUR'],
            ],
        ];
    }
}
