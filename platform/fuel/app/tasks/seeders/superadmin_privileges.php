<?php

namespace Fuel\Tasks\Seeders;

require_once(APPPATH . "/tasks/update_superadmin_privileges.php");

/**
 * Fix super-admin privileges seeder (runs task internally).
 */
final class Superadmin_Privileges extends Seeder
{

    protected function columnsStaging(): array
    {
        return [];
    }

    protected function rowsStaging(): array
    {
        $update_privileges_task = new \Fuel\Tasks\Update_Superadmin_Privileges();
        $update_privileges_task->default_super();

        return[];
    }
}
