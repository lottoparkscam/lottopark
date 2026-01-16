<?php

namespace Fuel\Tasks\Seeders;

require_once(APPPATH . "/tasks/superadmin.php");


final class Superadmin_Seeder extends Seeder
{
    protected function columnsStaging(): array
    {
        return [];
    }

    protected function rowsStaging(): array
    {
        $superadmin = new \Fuel\Tasks\Superadmin();
        $superadmin->run();

        return [];
    }
}
