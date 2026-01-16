<?php

namespace Fuel\Tasks\Seeders;

final class ModulesAddCasinoWithdrawalsView extends Seeder
{
    protected function rowsStaging(): array
    {
        return [
            'module' => [
                ['casino-withdrawals-view'],
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
