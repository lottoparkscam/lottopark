<?php

namespace Fuel\Tasks\Seeders;

final class CreditCardSandbox extends Seeder
{

    protected function columnsStaging(): array
    {
        return [
            'payment_method' => ['id', 'name'],
        ];
    }

    protected function rowsStaging(): array
    {
        return [
            'payment_method' => [
                [24, 'CreditCardSandbox']
            ],
        ];
    }

    protected function columnsProduction(): array
    {
        return [
            'payment_method' => ['id', 'name'],
        ];
    }

    protected function rowsProduction(): array
    {
        return [
            'payment_method' => [
                [24, 'CreditCardSandbox']
            ],
        ];
    }
}
