<?php

namespace Fuel\Tasks\Seeders;

use Fuel\Core\DB;

final class TurnOffGGWorldRaffle extends Seeder
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
            ->value('is_enabled', 0)
            ->where('slug', '=', 'gg-world-raffle')
            ->execute();

        DB::update('whitelabel_raffle')
            ->value('is_enabled', 0)
            ->where('raffle_id', '=', 8)
            ->execute();

    }
}
