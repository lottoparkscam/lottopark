<?php

namespace Fuel\Tasks\Seeders;

use Fuel\Core\DB;

final class UpdateEurojackpotDrawDate extends Seeder
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
        DB::update('lottery')
            ->value('draw_dates', '[
                "Tue 21:00",
                "Fri 21:00"
            ]')
            ->where('id', '=', 3)
            ->execute();
    }
}