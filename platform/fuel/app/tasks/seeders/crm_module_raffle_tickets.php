<?php

namespace Fuel\Tasks\Seeders;

final class Crm_Module_Raffle_Tickets extends Seeder
{
    protected function columnsStaging(): array
    {
        return [
            'module' => ['name']
        ];
    }

    protected function rowsStaging(): array
    {
        return [
            'module' => [
                ['raffle-tickets-view']
            ]
        ];
    }
}
