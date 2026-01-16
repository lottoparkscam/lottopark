<?php

namespace Fuel\Tasks\Seeders;

use Fuel\Core\DB;

final class UpdateEurojackpotRules extends Seeder
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
        DB::update('lottery_type')
            ->value('brange', 12)
            ->where('id', '=', 3)
            ->execute();
    }
}