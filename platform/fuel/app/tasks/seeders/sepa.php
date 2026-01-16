<?php

namespace Fuel\Tasks\Seeders;

final class Sepa extends Seeder
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
                [29, 'SEPA Cyber']
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
                [29, 'SEPA Cyber']
            ],
        ];
    }
}
