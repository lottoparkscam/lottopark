<?php

namespace Fuel\Tasks\Seeders;

final class ModulesAddAcceptanceRateReportView extends Seeder
{
    protected function rowsStaging(): array
    {
        return [
            'module' => [
                ['acceptance-rate-report-view'],
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
