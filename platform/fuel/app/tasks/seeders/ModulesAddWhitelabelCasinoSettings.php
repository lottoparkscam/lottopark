<?php

namespace Fuel\Tasks\Seeders;

use Helpers\CrmModuleHelper;

final class ModulesAddWhitelabelCasinoSettings extends Seeder
{
    protected function rowsStaging(): array
    {
        return [
            'module' => [
                [CrmModuleHelper::MODULE_WHITELABEL_CASINO_SETTINGS],
                [CrmModuleHelper::MODULE_WHITELABEL_CASINO_SETTINGS_GAME_ORDER]
            ]
        ];
    }

    protected function columnsStaging(): array
    {
        return [
            'module' => ['name'],
            'module' => ['name'],
        ];
    }
}
