<?php

namespace Fuel\Migrations;

class Create_raffle_prize
{
    public function up()
    {
        \DBUtil::create_table(
            'raffle_prize',
            [
                'id' => ['type' => 'int', 'auto_increment' => true, 'unsigned' => true],
                'raffle_draw_id' => ['type' => 'int', 'unsigned' => true],
                'raffle_rule_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'raffle_rule_tier_id' => ['type' => 'tinyint', 'constraint'=> 3, 'unsigned' => true],
                'currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'lines_won_count' => ['type' => 'int', 'unsigned' => true],
                'total' => ['type' => 'decimal', 'constraint' => [15,2], 'unsigned' => true],
                'per_user' => ['type' => 'decimal', 'constraint' => [15,2], 'unsigned' => true],
            ],
            ['id'],
            true,
            false,
            null,
            [
                [
                    'constraint' => 'raffle_prize_raffle_draw_id_idfx',
                    'key' => 'raffle_draw_id',
                    'reference' => [
                        'table' => 'raffle_draw',
                        'column' => 'id'
                    ]
                ],
                [
                    'constraint' => 'raffle_prize_raffle_rule_id_idfx',
                    'key' => 'raffle_rule_id',
                    'reference' => [
                        'table' => 'raffle_rule',
                        'column' => 'id'
                    ]
                ],
                [
                    'constraint' => 'raffle_prize_raffle_rule_tier_id_idfx',
                    'key' => 'raffle_rule_tier_id',
                    'reference' => [
                        'table' => 'raffle_rule_tier',
                        'column' => 'id'
                    ]
                ],
                [
                    'constraint' => 'raffle_prize_currency_id_idfx',
                    'key' => 'currency_id',
                    'reference' => [
                        'table' => 'currency',
                        'column' => 'id'
                    ]
                ]
            ]
        );

        \DBUtil::create_index('raffle_prize', 'raffle_draw_id', 'raffle_prize_raffle_draw_id_idfx_idx');
        \DBUtil::create_index('raffle_prize', 'raffle_rule_id', 'raffle_prize_raffle_rule_id_idfx_idx');
        \DBUtil::create_index('raffle_prize', 'raffle_rule_tier_id', 'raffle_prize_raffle_rule_tier_id_idfx_idx');
        \DBUtil::create_index('raffle_prize', 'currency_id', 'raffle_prize_currency_id_idfx_idx');
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('raffle_prize', 'raffle_prize_raffle_draw_id_idfx');
        \DBUtil::drop_foreign_key('raffle_prize', 'raffle_prize_raffle_rule_id_idfx');
        \DBUtil::drop_foreign_key('raffle_prize', 'raffle_prize_raffle_rule_tier_id_idfx');
        \DBUtil::drop_foreign_key('raffle_prize', 'raffle_prize_currency_id_idfx');

        \DBUtil::drop_table('raffle_prize');
    }
}
