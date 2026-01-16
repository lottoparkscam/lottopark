<?php

namespace Fuel\Tasks\Seeders;

final class Modules_Add_Edit_Bonus_Balance extends Seeder
{
    protected function rowsDevelopment(): array
    {
        return [
            'module' => [
                ['users-bonus-balance-manual-deposit-add'],
                ['users-bonus-balance-edit']
            ]
        ];
    }

    protected function columnsStaging(): array
    {
        return [
            'module' => ['name']
        ];
    }

    protected function rowsStaging(): array
    {
        return [
            'module' => [
                ['users-bonus-balance-manual-deposit-add'],
                ['users-bonus-balance-edit']
            ]
        ];
    }
}
