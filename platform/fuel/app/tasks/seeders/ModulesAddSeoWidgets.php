<?php

namespace Fuel\Tasks\Seeders;

use Helpers\CrmModuleHelper;

final class ModulesAddSeoWidgets extends Seeder
{
    protected function rowsStaging(): array
    {
        return [
            'module' => [
                [CrmModuleHelper::MODULE_SEO_WIDGETS_GENERATOR],
            ]
        ];
    }

    protected function columnsStaging(): array
    {
        return [
            'module' => ['name'],
        ];
    }
}
