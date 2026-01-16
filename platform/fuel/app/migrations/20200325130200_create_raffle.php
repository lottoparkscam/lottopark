<?php

namespace Fuel\Migrations;

class Create_raffle
{
    public function up()
    {
        \DBUtil::create_table(
            'raffle',
            [
                'id' => ['type' => 'tinyint', 'constraint'=> 3, 'auto_increment' => true, 'unsigned' => true],
                'raffle_rule_id' => ['type' => 'tinyint', 'constraint'=> 3, 'unsigned' => true],
                'currency_id' => ['type' => 'tinyint', 'constraint'=> 3, 'unsigned' => true],
                'name' => ['type' => 'varchar', 'constraint'=> 45 ],
                'country' => ['type' => 'varchar', 'constraint'=> 45 ],
                'country_iso' => ['type' => 'varchar', 'constraint'=> 2 ],
                'slug' => ['type' => 'varchar', 'constraint'=> 45 ],
                'is_enabled' => ['type' => 'tinyint', 'constraint' => 1],
                'timezone' => ['type' => 'varchar', 'constraint' => 45 ],
                'main_prize' => ['type' => 'decimal', 'constraint' => [15,2], 'unsigned' => true],
                'last_draw_date' => ['type' => 'datetime', 'null' => true],
                'last_draw_date_utc' => ['type' => 'datetime', 'null' => true],
                'next_draw_date' => ['type' => 'datetime', 'null' => true],
                'next_draw_date_utc' => ['type' => 'datetime', 'null' => true],
                'last_prize_total' => ['type' => 'decimal', 'constraint' => [15,2], 'unsigned' => true],
                'draw_lines_count' => ['type' => 'int', 'unsigned' => true],
                'last_ticket_count' => ['type' => 'int', 'unsigned' => true, 'null' => true],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
            [
                'constraint' => 'raffle_currency_id_idfx',
                'key' => 'currency_id',
                'reference' => [
                    'table' => 'currency',
                    'column' => 'id'
                ]
            ]
        ]
        );

        \DBUtil::create_index('raffle', 'currency_id', 'raffle_currency_id_idfx_idx');
        \DBUtil::create_index('raffle', 'slug', 'raffle_slug_idx');
    }



    public function down()
    {
        \DBUtil::drop_foreign_key('raffle', 'raffle_currency_id_idfx');

        \DBUtil::drop_table('raffle');
    }
}
