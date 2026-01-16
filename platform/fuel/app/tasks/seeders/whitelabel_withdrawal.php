<?php

namespace Fuel\Tasks\Seeders;

/**
* Whitelabel Withdrawal seeder.
*/
final class Whitelabel_Withdrawal extends Seeder
{

    protected function columnsStaging(): array
    {
        return [];
    }

    protected function rowsStaging(): array
    {
        /* When Docker is used, leave seed to install script */
        return [];
    }

    protected function rowsProduction(): array
    {
        return [];
    }

}