<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

final class Rename_Lottery_Types_Multipliers_Table extends \Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::rename_table('lottery_types_multipliers', 'lottery_type_multiplier');
        DBUtil::modify_fields('lottery_prize_data', [
            'lottery_types_multipliers_id' => [
                'type' => 'tinyint',
                'constraint' => 3,
                'null' => true,
                'unsigned' => true,
                'name' => 'lottery_type_multiplier_id',
            ],
        ]);
    }

    protected function down_gracefully(): void
    {
        DBUtil::rename_table('lottery_type_multiplier', 'lottery_types_multipliers');
        DBUtil::modify_fields('lottery_prize_data', [
            'lottery_type_multiplier_id' => [
                'type' => 'tinyint',
                'constraint' => 3,
                'null' => true,
                'unsigned' => true,
                'name' => 'lottery_types_multipliers_id',
            ],
        ]);
    }
}