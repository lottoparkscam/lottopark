<?php

namespace Fuel\Tasks\Seeders;

final class ModulesAddDepositsView extends Seeder
{
    protected function rowsStaging(): array
    {
        return [
            'module' => [
                ['deposits-view'],
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
