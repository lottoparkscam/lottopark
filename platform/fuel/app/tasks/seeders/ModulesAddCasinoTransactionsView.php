<?php

namespace Fuel\Tasks\Seeders;

final class ModulesAddCasinoTransactionsView extends Seeder
{
    protected function rowsStaging(): array
    {
        return [
            'module' => [
                ['casino-transactions-view'],
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
