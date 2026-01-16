<?php

namespace Fuel\Migrations;

class Lottery_Prize_Data
{
    public function up()
    {
        \DBUtil::create_table(
            'lottery_prize_data',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'lottery_draw_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'lottery_type_data_id' => ['type' => 'smallint', 'constraint' => 5, 'unsigned' => true],
                'winners' => ['type' => 'decimal', 'constraint' => [10, 0], 'unsigned' => true],
                'prizes' => ['type' => 'decimal', 'constraint' => [12, 2], 'unsigned' => true]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'lottery_prize_data_lottery_draw_id_lottery_draw_idfx',
                    'key' => 'lottery_draw_id',
                    'reference' => [
                        'table' => 'lottery_draw',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'lottery_prize_data_lottery_type_data_id_lottery_type_data_idfx',
                    'key' => 'lottery_type_data_id',
                    'reference' => [
                        'table' => 'lottery_type_data',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
            ]
        );

        \DBUtil::create_index('lottery_prize_data', 'lottery_type_data_id', 'lottery_prize_data_lottery_type_data_id_lottery_type_data_i_idx');
        \DBUtil::create_index('lottery_prize_data', 'lottery_draw_id', 'lottery_prize_data_lottery_draw_id_lottery_draw_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('lottery_prize_data', 'lottery_prize_data_lottery_type_data_id_lottery_type_data_idfx');
        \DBUtil::drop_foreign_key('lottery_prize_data', 'lottery_prize_data_lottery_draw_id_lottery_draw_idfx');
        \DBUtil::drop_table('lottery_prize_data');
    }
}
