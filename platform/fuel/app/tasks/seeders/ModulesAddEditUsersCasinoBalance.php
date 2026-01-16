<?php

namespace Fuel\Tasks\Seeders;

final class ModulesAddEditUsersCasinoBalance extends Seeder
{
    protected function rowsStaging(): array
    {
        return [
            'module' => [
                ['users-balance-casino-edit'],
                ['users-manual-deposit-casino-add'],
            ]
        ];
    }

    protected function columnsStaging(): array
    {
        return [
            'module' => ['name']
        ];
    }
}
