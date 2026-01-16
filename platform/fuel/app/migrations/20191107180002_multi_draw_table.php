<?php

namespace Fuel\Migrations;

class Multi_Draw_Table
{
    public function up()
    {
        // Whitelabel Multi Draw Option table
        \DBUtil::create_table(
            'multi_draw',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'token' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'whitelabel_user_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'lottery_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'tickets' => ['type' => 'tinyint', 'constraint' => 2, 'unsigned' => true],
                'first_draw' => ['type' => 'date'],
                'valid_to_draw' => ['type' => 'date'],
                'current_draw' => ['type' => 'date'],
                'currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'amount' => ['type' => 'decimal', 'constraint' => [9,2], 'unsigned' => true],
                'amount_usd' => ['type' => 'decimal', 'constraint' => [10, 2], 'unsigned' => true],
                'amount_manager' => ['type' => 'decimal', 'constraint' => [15, 2], 'unsigned' => true, 'default' => '0.00'],
                'is_finished' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => 0],
                'is_cancelled' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => 0],
                'date' => ['type' => 'datetime']
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'multi_draw_whitelabel_id_whitelabel_idfx',
                    'key' => 'whitelabel_id',
                    'reference' => [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'multi_draw_currency_id_currency_idfx',
                    'key' => 'currency_id',
                    'reference' => [
                        'table' => 'currency',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'multi_draw_whitelabel_user_id_whitelabel_user_idfx',
                    'key' => 'whitelabel_user_id',
                    'reference' => [
                        'table' => 'whitelabel_user',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'multi_draw_lottery_id_lottery_idfx',
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

        \DBUtil::create_index('multi_draw', 'whitelabel_id', 'multi_draw_whitelabel_id_whitelabel_idfx');
        \DBUtil::create_index('multi_draw', 'whitelabel_user_id', 'multi_draw_whitelabel_user_id_whitelabel_user_idfx');
        \DBUtil::create_index('multi_draw', 'lottery_id', 'multi_draw_lottery_id_lottery_idfx');
        \DBUtil::create_index('multi_draw', 'currency_id', 'multi_draw_currency_id_currency_idfx');
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('multi_draw', 'multi_draw_whitelabel_id_whitelabel_idfx');
        \DBUtil::drop_foreign_key('multi_draw', 'multi_draw_whitelabel_user_id_whitelabel_user_idfx');
        \DBUtil::drop_foreign_key('multi_draw', 'multi_draw_lottery_id_lottery_idfx');
        \DBUtil::drop_foreign_key('multi_draw', 'multi_draw_currency_id_currency_idfx');
        \DBUtil::drop_table('multi_draw');
    }
}
