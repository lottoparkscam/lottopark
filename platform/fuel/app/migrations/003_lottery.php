<?php

namespace Fuel\Migrations;

class Lottery
{
    public function up()
    {
        \DBUtil::create_table(
            'lottery',
            [
                'id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'auto_increment' => true],
                'source_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'name' => ['type' => 'varchar', 'constraint' => 40],
                'shortname' => ['type' => 'varchar', 'constraint' => 10, 'null' => true, 'default' => null],
                'country' => ['type' => 'varchar', 'constraint' => 15],
                'country_iso' => ['type' => 'varchar', 'constraint' => 2, 'null' => true, 'default' => null],
                'slug' => ['type' => 'varchar', 'constraint' => 40],
                'is_enabled' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true],
                'timezone' => ['type' => 'varchar', 'constraint' => 40],
                'draw_days' => ['type' => 'varchar', 'constraint' => 15],
                'draw_hour_local' => ['type' => 'time'],
                'current_jackpot' => ['type' => 'decimal', 'constraint' => [12,8], 'unsigned' => true, 'null' => true, 'default' => null],
                'current_jackpot_usd' => ['type' => 'decimal', 'constraint' => [12,8], 'unsigned' => true, 'null' => true, 'default' => null],
                'draw_jackpot_set' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'last_date_local' => ['type' => 'date', 'null' => true, 'default' => null],
                'next_date_local' => ['type' => 'date', 'null' => true, 'default' => null],
                'next_date_utc' => ['type' => 'datetime', 'null' => true, 'default' => null],
                'last_numbers' => ['type' => 'varchar', 'constraint' => 30, 'null' => true, 'default' => null],
                'last_bnumbers' => ['type' => 'varchar', 'constraint' => 30, 'null' => true, 'default' => null],
                'last_total_prize' => ['type' => 'decimal', 'constraint' => [12,2], 'unsigned' => true],
                'last_total_winners' => ['type' => 'decimal', 'constraint' => [8,0], 'unsigned' => true],
                'last_jackpot_prize' => ['type' => 'decimal', 'constraint' => [12,2], 'unsigned' => true],
                'last_update' => ['type' => 'datetime'],
                'price' => ['type' => 'decimal', 'constraint' => [5,2], 'unsigned' => true],
                'estimated_updated' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => 1],
                'additional_data' => ['type' => 'varchar', 'constraint' => 300, 'null' => true, 'default' => null]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'lottery_currency_id_currency_idfx',
                    'key' => 'currency_id',
                    'reference' => [
                        'table' => 'currency',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'NO ACTION'
                ],
                [
                    'constraint' => 'lottery_source_id_lottery_source_idfx',
                    'key' => 'source_id',
                    'reference' => [
                        'table' => 'lottery_source',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'NO ACTION',
                ]
            ]
        );

        \DBUtil::create_index('lottery', 'current_jackpot', 'lottery_current_jackpot_idx');
        \DBUtil::create_index('lottery', 'is_enabled', 'lottery_is_enabled_idx');
        \DBUtil::create_index('lottery', 'source_id', 'lottery_source_id_lottery_source_idfx_idx');
        \DBUtil::create_index('lottery', ['is_enabled', 'current_jackpot'], 'lottery_is_enabled_current_jackpot_idmx');
        \DBUtil::create_index('lottery', 'currency_id', 'lottery_currency_id_currency_idfx_idx');
        \DBUtil::create_index('lottery', 'current_jackpot_usd', 'lottery_current_jackpot_usd_idx');
        \DBUtil::create_index('lottery', ['is_enabled', 'current_jackpot_usd'], 'lottery_is_enabled_current_jackpot_usd_idmx');
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('lottery', 'lottery_source_id_lottery_source_idfx');
        \DBUtil::drop_foreign_key('lottery', 'lottery_currency_id_currency_idfx');
        \DBUtil::drop_table('lottery');
    }
}
