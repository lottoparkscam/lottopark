<?php

namespace Fuel\Tasks\Seeders;

use Fuel\Core\DB;
use Helpers\CrmModuleHelper;

final class UpdateUsersEditModuleName extends Seeder
{
    protected function columnsStaging(): array
    {
        return [];
    }

    protected function rowsStaging(): array
    {
        return [];
    }

    public function execute(): void
    {
        DB::update('module')
            ->value('name', CrmModuleHelper::MODULE_USERS_EDIT_ACCOUNT_PERSONAL_DATA)
            ->where('name', '=', Modules::MODULE_USERS_EDIT)
            ->execute();
    }
}
