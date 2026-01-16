<?php

namespace Fuel\Migrations;

class Lottery_Provider
{
    public function up()
    {
        \DBUtil::create_table(
            'lottery_provider',
            [
                'id' => ['type' => 'smallint', 'constraint' => 5, 'unsigned' => true, 'auto_increment' => true],
                'lottery_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'provider' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'min_bets' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'max_bets' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'multiplier' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'closing_time' => ['type' => 'time'],
                'timezone' => ['type' => 'varchar', 'constraint' => 40],
                'offset' => ['type' => 'tinyint', 'constraint' => 4],
                'tax' => ['type' => 'decimal', 'constraint' => [5, 2], 'default' => '0.00'],
                'tax_min' => ['type' => 'decimal', 'constraint' => [8, 2], 'default' => '0.00'],
                'data' => ['type' => 'text', 'null' => true],
                'fee' => ['type' => 'decimal', 'constraint' => [4, 2], 'unsigned' => true, 'null' => true, 'default' => null]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'lottery_provider_lottery_id_lottery_idfx',
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

        \DBUtil::create_index('lottery_provider', 'lottery_id', 'lottery_provider_lottery_id_lottery_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('lottery_provider', 'lottery_provider_lottery_id_lottery_idfx');
        \DBUtil::drop_table('lottery_provider');
    }
}
