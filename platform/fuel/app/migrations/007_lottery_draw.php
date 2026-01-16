<?php

namespace Fuel\Migrations;

class Lottery_Draw
{
    public function up()
    {
        \DBUtil::create_table(
            'lottery_draw',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'lottery_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'lottery_type_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'date_download' => ['type' => 'datetime'],
                'date_local' => ['type' => 'date'],
                'jackpot' => ['type' => 'decimal', 'constraint' => [12, 8], 'unsigned' => true, 'null' => true, 'default' => null],
                'numbers' => ['type' => 'varchar', 'constraint' => 30, 'null' => true, 'default' => null],
                'bnumbers' => ['type' => 'varchar', 'constraint' => 30, 'null' => true, 'default' => null],
                'total_prize' => ['type' => 'decimal', 'constraint' => [12, 2], 'unsigned' => true],
                'total_winners' => ['type' => 'decimal', 'constraint' => [8, 0], 'unsigned' => true],
                'final_jackpot' => ['type' => 'decimal', 'constraint' => [12, 2], 'unsigned' => true],
                'additional_data' => ['type' => 'varchar', 'constraint' => 300, 'null' => true, 'default' => null]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'lottery_draw_lottery_id_lottery_idfx',
                    'key' => 'lottery_id',
                    'reference' => [
                        'table' => 'lottery',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'lottery_draw_lottery_type_id_lottery_type_idfx',
                    'key' => 'lottery_type_id',
                    'reference' => [
                        'table' => 'lottery_type',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
            ]
        );

        \DBUtil::create_index('lottery_draw', 'lottery_id', 'lottery_draw_lottery_id_lottery_idfx_idx');
        \DBUtil::create_index('lottery_draw', 'date_local', 'lottery_date_local_idx');
        \DBUtil::create_index('lottery_draw', 'jackpot', 'lottery_jackpot_idx');
        // TODO: check if needed
        \DBUtil::create_index('lottery_draw', ['lottery_id', 'date_local'], 'lottery_draw_lottery_id_date_local_idmx');
        \DBUtil::create_index('lottery_draw', 'lottery_type_id', 'lottery_draw_lottery_type_id_lottery_type_idfx_idx');
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('lottery_draw', 'lottery_draw_lottery_type_id_lottery_type_idfx');
        \DBUtil::drop_foreign_key('lottery_draw', 'lottery_draw_lottery_id_lottery_idfx');
        \DBUtil::drop_table('lottery_draw');
    }
}
