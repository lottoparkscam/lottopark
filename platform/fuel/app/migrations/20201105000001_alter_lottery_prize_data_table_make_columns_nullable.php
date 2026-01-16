<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DB;
use Fuel\Core\DBUtil;

class Alter_Lottery_Prize_Data_table_Make_Columns_Nullable extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::modify_fields('lottery_prize_data', [
            'lottery_draw_id' => [
                'type' => 'int',
                'constraint' => 10,
                'unsigned' => true,
                'null' => true,
            ],
            'winners' => [
                'type' => 'decimal',
                'null' => true,
                'constraint' => [10, 0],
                'unsigned' => true,
            ]
        ]);
    }

    /**
     * Revert migration.
     *
     * @return void
     */
    protected function down_gracefully(): void
    {
        DB::query('SET FOREIGN_KEY_CHECKS=0;')->execute();
        DB::update('lottery_prize_data')
            ->set(['lottery_draw_id' => 999])
            ->where('lottery_draw_id', 'IS', null)
            ->execute();
        DB::update('lottery_prize_data')
            ->set(['winners' => 0])
            ->where('winners', 'IS', null)
            ->execute();
        DBUtil::modify_fields('lottery_prize_data', [
            'lottery_draw_id' => [
                'type' => 'int',
                'constraint' => 10,
                'unsigned' => true,
                'null' => false,
            ],
            'winners' => [
                'type' => 'decimal',
                'null' => false,
                'constraint' => [10, 0],
                'unsigned' => true,
            ]
        ]);
    }
}
