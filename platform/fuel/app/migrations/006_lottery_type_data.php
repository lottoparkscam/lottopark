<?php

namespace Fuel\Migrations;

class Lottery_Type_Data
{
    public function up()
    {
        \DBUtil::create_table(
            'lottery_type_data',
            [
                'id' => ['type' => 'smallint', 'constraint' => 5, 'unsigned' => true, 'auto_increment' => true],
                'lottery_type_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'match_n' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'match_b' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'additional_data' => ['type' => 'varchar', 'constraint' => 300, 'null' => true, 'default' => null],
                'prize' => ['type' => 'varchar', 'constraint' => 25],
                'odds' => ['type' => 'decimal', 'constraint' => [11, 2], 'unsigned' => true],
                'type' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'estimated' => ['type' => 'decimal', 'constraint' => [10, 2], 'unsigned' => true],
                'is_jackpot' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'ltd_lt_id_lt_idfx',
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

        \DBUtil::create_index('lottery_type_data', 'lottery_type_id', 'ltd_lt_id_lt_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('lottery_type_data', 'ltd_lt_id_lt_idfx');
        \DBUtil::drop_table('lottery_type_data');
    }
}
