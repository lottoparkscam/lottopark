<?php

namespace Fuel\Tasks\Seeders;

final class ModulesAddCasinoReportsView extends Seeder
{
    protected function rowsStaging(): array
    {
        return [
            'module' => [
                ['casino-reports-view'],
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
