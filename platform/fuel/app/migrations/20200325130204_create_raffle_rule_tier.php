<?php

namespace Fuel\Migrations;

class Create_raffle_rule_tier
{
    public function up()
    {
        \DBUtil::create_table(
            'raffle_rule_tier',
            [
                'id' => ['type' => 'tinyint', 'constraint' => 3, 'auto_increment' => true, 'unsigned' => true],
                'raffle_rule_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'slug' => ['type' => 'varchar', 'constraint' => 45],
                'matches' => ['type' => 'json'],
                'prize_type' => ['type' => 'tinyint', 'constraint' => 1],
                'prize_fund_percent' => ['type' => 'decimal', 'constraint' => [4, 2], 'unsigned' => true],
                'odds' => ['type' => 'decimal', 'constraint' => [13, 2], 'unsigned' => true, 'null' => true],
                'prize' => ['type' => 'decimal', 'constraint' => [13, 2], 'unsigned' => true],
                'is_main_prize' => ['type' => 'tinyint', 'constraint' => 1],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'raffle_rule_tier_raffle_rule_id_idfx',
                    'key' => 'raffle_rule_id',
                    'reference' => [
                        'table' => 'raffle_rule',
                        'column' =>  'id'
                    ]
                ],
                [
                    'constraint' => 'raffle_rule_tier_currency_id_idfx',
                    'key' => 'currency_id',
                    'reference' => [
                        'table' => 'currency',
                        'column' =>  'id'
                    ]
                ]
            ]
        );

        \DBUtil::create_index('raffle_rule_tier', 'raffle_rule_id', 'raffle_rule_tier_raffle_rule_id_idfx_idx');
        \DBUtil::create_index('raffle_rule_tier', 'currency_id', 'raffle_rule_tier_currency_id_idfx_idx');
        \DBUtil::create_index('raffle_rule_tier', 'slug', 'raffle_rule_tier_slug_idx');
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('raffle_rule_tier', 'raffle_rule_tier_raffle_rule_id_idfx');
        \DBUtil::drop_foreign_key('raffle_rule_tier', 'raffle_rule_tier_currency_id_idfx');

        \DBUtil::drop_table('raffle_rule_tier');
    }
}
