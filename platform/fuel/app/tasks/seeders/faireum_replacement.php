<?php

namespace Fuel\Tasks\Seeders;

use Fuel\Core\DB;

final class Faireum_Replacement extends Seeder
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
            ->value('slug', 'faireum-raffle-old')
            ->value('is_enabled', false)
            ->where('slug', '=', 'faireum-raffle')
            ->execute();

        DB::update('raffle')
            ->value('slug', 'faireum-raffle')
            ->value('is_enabled', true)
            ->value('is_sell_limitation_enabled', false)
            ->where('slug', '=', 'faireum-raffle-new')
            ->execute();
    }
}
