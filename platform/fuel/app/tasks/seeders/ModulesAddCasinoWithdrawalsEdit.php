<?php

namespace Fuel\Tasks\Seeders;

final class ModulesAddCasinoWithdrawalsEdit extends Seeder
{
    protected function rowsStaging(): array
    {
        return [
            'module' => [
                ['casino-withdrawals-edit'],
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
