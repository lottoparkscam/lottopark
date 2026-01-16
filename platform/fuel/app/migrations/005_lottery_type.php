<?php

namespace Fuel\Migrations;

class Lottery_Type
{
    public function up()
    {
        \DBUtil::create_table(
            'lottery_type',
            [
                'id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'auto_increment' => true],
                'lottery_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'odds' => ['type' => 'decimal', 'constraint' => [4, 2], 'unsigned' => true],
                'ncount' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'bcount' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'nrange' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'brange' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'bextra' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'date_start' => ['type' => 'date', 'null' => true, 'default' => null],
                'def_insured_tiers' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'additional_data' => ['type' => 'varchar', 'constraint' => 300, 'null' => true, 'default' => null]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'lottery_type_lottery_id_lottery_idfx',
                    'key' => 'lottery_id',
                    'reference' => [
                        'table' => 'lottery',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
            ]
        );

        \DBUtil::create_index('lottery_type', 'lottery_id', 'lottery_type_lottery_id_lottery_idfx_idx');
        \DBUtil::create_index('lottery_type', ['lottery_id', 'date_start'], 'lottery_type_lottery_id_date_start_idmx');
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('lottery_type', 'lottery_type_lottery_id_lottery_idfx');
        \DBUtil::drop_table('lottery_type');
    }
}