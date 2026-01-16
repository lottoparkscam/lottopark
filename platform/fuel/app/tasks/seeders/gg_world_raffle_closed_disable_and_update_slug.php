<?php

namespace Fuel\Tasks\Seeders;

use Fuel\Core\DB;

final class GG_World_Raffle_Closed_Disable_And_Update_Slug extends Seeder
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
        DB::update('raffle')
            ->value('slug', 'gg-world-raffle-old')
            ->value('is_enabled', false)
            ->where('slug', '=', 'gg-world-raffle')
            ->execute();
    }
}
