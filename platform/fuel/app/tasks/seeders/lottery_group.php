<?php

namespace Fuel\Tasks\Seeders;

/**
 * Lottery Group seeder.
 */
final class Lottery_Group extends Seeder
{
    use \Without_Foreign_Key_Checks;

    protected function columnsStaging(): array
    {
        return [
            'lottery_group' => ['id', 'lottery_id', 'group_id']
        ];
    }
    protected function rowsStaging(): array
    {
        return [
            'lottery_group' => [
                ['1', '17', '1'],
                ['2', '19', '1'],
                ['3', '20', '1']
            ]
        ];
    }
}
