<?php

namespace Fuel\Tasks\Seeders;

/**
 * Bonus seeder.
 */
final class Referafriend_Bonus extends Seeder
{
    protected function columnsStaging(): array
    {
        return [
            'bonus' => ['id', 'name']
        ];
    }

    protected function rowsStaging(): array
    {
        return [
            'bonus' => [
                [2, 'Refer a friend']
            ]
        ];
    }
}
