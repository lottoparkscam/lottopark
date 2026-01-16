<?php

namespace Fuel\Tasks\Seeders;

/**
 * Bonus seeder.
 */
final class Bonus extends Seeder
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
                [1, 'Welcome bonus']
            ]
        ];
    }
}
