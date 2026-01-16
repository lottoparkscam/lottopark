<?php

namespace Fuel\Tasks\Seeders;

final class Modules_Add_Logs_View extends Seeder
{
    protected function rowsDevelopment(): array
    {
        return [
            'module' => [
                ['logs-view'],
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
                ['logs-view'],
            ]
        ];
    }
}
