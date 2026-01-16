<?php

namespace Fuel\Tasks\Seeders;

use Fuel\Core\DB;
use Services\ScanService;

final class TurnOnGGWorldScan extends Seeder
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
        DB::update('whitelabel_lottery')
            ->value('model', 2)
            ->where('lottery_id', 'IN', ScanService::IDS_OF_GG_WORLD_LOTTERIES_WITH_ENABLED_SCAN)
            ->execute();

        DB::update('lottery')
            ->value('scans_enabled', true)
            ->where('id', 'IN', ScanService::IDS_OF_GG_WORLD_LOTTERIES_WITH_ENABLED_SCAN)
            ->execute();
    }
}
