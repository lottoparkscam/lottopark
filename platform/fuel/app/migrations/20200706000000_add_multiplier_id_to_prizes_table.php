<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

class Add_multiplier_id_to_prizes_table
{
    public function up()
    {
        DBUtil::add_fields(
            'lottery_prize_data',
            [
                'lottery_types_multipliers_id' => [
                    'type' => 'tinyint',
                    'constraint' => 3,
                    'null' => true,
                    'unsigned' => true,
                    'after' => 'lottery_type_data_id',
                ]
            ]
        );
        DBUtil::add_foreign_key('lottery_prize_data', [
            'key' => 'lottery_types_multipliers_id',
            'reference' => [
                'table' => 'lottery_types_multipliers',
                'column' => 'id',
            ],
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE'
        ]);
    }

    public function down()
    {
        DBUtil::drop_foreign_key('lottery_prize_data', 'lottery_prize_data_ibfk_1');
        DBUtil::drop_fields('lottery_prize_data', [
            'lottery_types_multipliers_id'
        ]);
    }
}
