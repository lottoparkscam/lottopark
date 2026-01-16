<?php

namespace Fuel\Tasks\Seeders;

/**
 * Description of custom
 *
 */
final class Custom extends Seeder
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
                [27, 'Custom']
            ],
        ];
    }
}
