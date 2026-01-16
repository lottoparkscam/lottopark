<?php

namespace Fuel\Migrations;

class Create_raffle_rule
{
    public function up()
    {
        \DBUtil::create_table(
            'raffle_rule',
            [
                'id' => ['type' => 'tinyint', 'constraint' => 3, 'auto_increment' => true, 'unsigned' => true],
                'raffle_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true],
                'currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'line_price' => ['type' => 'decimal', 'constraint' => [9, 2], 'unsigned' => true],
                'fee' => ['type' => 'decimal', 'constraint' => [9, 2], 'unsigned' => true],
                'max_lines_per_draw' => ['type' => 'integer', 'constraint' => 3, 'unsigned' => true],
                'ranges' => ['type' => 'json']
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'raffle_rule_raffle_id_idfx',
                    'key' => 'raffle_id',
                    'reference' => [
                        'table' => 'raffle',
                        'column' =>  'id'
                    ]
                ],
                [
                    'constraint' => 'raffle_rule_currency_id_idfx',
                    'key' => 'currency_id',
                    'reference' => [
                        'table' => 'currency',
                        'column' =>  'id'
                    ]
                ],
            ]
        );

        \DBUtil::create_index('raffle_rule', 'raffle_id', 'raffle_rule_raffle_id_idfx_idx');
        \DBUtil::create_index('raffle_rule', 'currency_id', 'raffle_rule_currency_id_idfx_idx');

        \DBUtil::add_foreign_key('raffle', [
            'constraint' => 'raffle_raffle_rule_id_idfx',
            'key' => 'raffle_rule_id',
            'reference' => [
                'table' => 'raffle_rule',
                'column' =>  'id'
            ]
        ]);
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('raffle_rule', 'raffle_rule_raffle_id_idfx');
        \DBUtil::drop_foreign_key('raffle_rule', 'raffle_rule_currency_id_idfx');

        \DBUtil::drop_foreign_key('raffle', 'raffle_raffle_rule_id_idfx');
        \DBUtil::drop_table('raffle_rule');
    }
}
