<?php

namespace Fuel\Migrations;

class Create_raffle_draw
{
    public function up()
    {
        \DBUtil::create_table(
        'raffle_draw',
        [
                'id' => ['type' => 'int', 'auto_increment' => true, 'unsigned' => true],
                'raffle_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'raffle_rule_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'draw_no' => ['type' => 'int', 'unsigned' => true],
                'date' => ['type' => 'datetime'],
                'numbers' => ['type' => 'longtext'],
                'is_calculated' => ['type' => 'tinyint', 'constraint' => 1],
                'sale_num' => ['type' => 'decimal', 'constraint' => [15,2], 'unsigned' => true, 'null' => true],
                'prize_total' => ['type' => 'decimal', 'constraint' => [15,2], 'unsigned' => true, 'null' => true],
                'lines_won_count' => ['type' => 'int', 'unsigned' => true, 'null' => true],
                'ticket_count' => ['type' => 'int', 'unsigned' => true, 'null' => true]
            ],
        ['id'],
        true,
        false,
        'utf8mb4_unicode_ci',
        [
                [
                    'constraint' => 'raffle_draw_raffle_id_idfx',
                    'key' => 'raffle_id',
                    'reference' => [
                        'table' => 'raffle',
                        'column' =>  'id'
                    ]
                ],
                [
                    'constraint' => 'raffle_draw_raffle_rule_id_idfx',
                    'key' => 'raffle_rule_id',
                    'reference' => [
                        'table' => 'raffle_rule',
                        'column' =>  'id'
                    ]
                ],
                [
                    'constraint' => 'raffle_draw_currency_id_idfx',
                    'key' => 'currency_id',
                    'reference' => [
                        'table' => 'currency',
                        'column' =>  'id'
                    ]
                ]
            ]
    );

        \DBUtil::create_index('raffle_draw', 'raffle_id', 'raffle_draw_raffle_id_idfx');
        \DBUtil::create_index('raffle_draw', 'raffle_rule_id', 'raffle_draw_raffle_rule_id_idfx');
        \DBUtil::create_index('raffle_draw', 'currency_id', 'raffle_draw_currency_id_idfx');
        \DBUtil::create_index('raffle_draw', 'draw_no', 'raffle_draw_draw_no_idx');
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('raffle_draw', 'raffle_draw_raffle_id_idfx');
        \DBUtil::drop_foreign_key('raffle_draw', 'raffle_draw_raffle_rule_id_idfx');
        \DBUtil::drop_foreign_key('raffle_draw', 'raffle_draw_currency_id_idfx');

        \DBUtil::drop_table('raffle_draw');
    }
}
