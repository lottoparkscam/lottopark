<?php

namespace Fuel\Tasks\Seeders;

/**
 * Description of TruevoCC
 */
final class TruevoCC extends Seeder
{

    protected function columnsStaging(): array
    {
        return [
            'payment_method' => ['id', 'name'],
            'payment_method_supported_currency' => ['payment_method_id', 'code', 'iso_code'],
        ];
    }

    protected function rowsStaging(): array
    {
        return [
            'payment_method' => [
                [25, 'TruevoCC']
            ],
            'payment_method_supported_currency' => [
                [25, 'EUR', '978'],
                [25, 'GBP', '826'],
                [25, 'USD', '840'],
                [25, 'KRW', '410'],
                [25, 'RUB', '643'],
            ],
        ];
    }

    protected function columnsProduction(): array
    {
        return [
            'payment_method' => ['id', 'name'],
            'payment_method_supported_currency' => ['payment_method_id', 'code', 'iso_code'],
        ];
    }

    protected function rowsProduction(): array
    {
        return [
            'payment_method' => [
                [25, 'TruevoCC']
            ],
            'payment_method_supported_currency' => [
                [25, 'EUR', '978'],
                [25, 'GBP', '826'],
                [25, 'USD', '840'],
                [25, 'KRW', '410'],
                [25, 'RUB', '643'],
            ],
        ];
    }
}
