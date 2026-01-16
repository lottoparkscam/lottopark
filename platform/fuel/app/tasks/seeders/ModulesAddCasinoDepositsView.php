<?php

namespace Fuel\Tasks\Seeders;

final class ModulesAddCasinoDepositsView extends Seeder
{
    protected function rowsStaging(): array
    {
        return [
            'module' => [
                ['casino-deposits-view'],
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
