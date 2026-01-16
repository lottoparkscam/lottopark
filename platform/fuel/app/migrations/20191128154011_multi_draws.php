<?php

namespace Fuel\Migrations;

class Multi_Draws
{
    public function up()
    {
        // Whitelabel Multi Draw Option table
        \DBUtil::create_table(
            'whitelabel_multi_draw_option',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'tickets' => ['type' => 'tinyint', 'constraint' => 2, 'unsigned' => true],
                'discount' => ['type' => 'decimal', 'constraint' => [4, 2], 'unsigned' => true, 'null' => true, 'default' => null]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_multi_draw_option_whitelabel_id_whitelabel_a',
                    'key' => 'whitelabel_id',
                    'reference' => [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
            ]
        );

        \DBUtil::create_index('whitelabel_multi_draw_option', 'whitelabel_id', 'whitelabel_multi_draw_option_whitelabel_id_whitelabel_ax');


        // Whitelabel Multi Draw Lottery table
        \DBUtil::create_table(
            'whitelabel_multi_draw_lottery',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'lottery_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_multi_draw_lottery_whitelabel_id_whitelabel_a',
                    'key' => 'whitelabel_id',
                    'reference' => [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_multi_draw_lottery_lottery_id_lottery_a',
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

        \DBUtil::create_index('whitelabel_multi_draw_lottery', 'whitelabel_id', 'whitelabel_multi_draw_lottery_whitelabel_id_whitelabel_ax');
        \DBUtil::create_index('whitelabel_multi_draw_lottery', 'lottery_id', 'whitelabel_multi_draw_lottery_lottery_id_lottery_ax');

        // Add multi_draw_id
        \DBUtil::add_fields('whitelabel_user_ticket', [
            'multi_draw_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'after' => 'whitelabel_transaction_id']
        ]);

        \DBUtil::add_foreign_key('whitelabel_user_ticket', [
            'constraint' => 'whitelabel_user_ticket_multi_draw_id_multi_drawa',
            'key' => 'multi_draw_id',
            'reference' => [
                'table' => 'multi_draw',
                'column' => 'id',
            ],
            'on_update' => 'NO ACTION',
            'on_delete' => 'CASCADE'
        ]);

        \DBUtil::create_index('whitelabel_user_ticket', 'multi_draw_id', 'whitelabel_user_ticket_multi_draw_id_multi_drawax');
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_multi_draw_option', 'whitelabel_multi_draw_option_whitelabel_id_whitelabel_a');
        \DBUtil::drop_foreign_key('whitelabel_multi_draw_lottery', 'whitelabel_multi_draw_lottery_whitelabel_id_whitelabel_a');
        \DBUtil::drop_foreign_key('whitelabel_multi_draw_lottery', 'whitelabel_multi_draw_lottery_lottery_id_lottery_a');
        \DBUtil::drop_foreign_key('whitelabel_user_ticket', 'whitelabel_user_ticket_multi_draw_id_multi_drawa');

        \DBUtil::drop_table('whitelabel_multi_draw_option');
        \DBUtil::drop_table('whitelabel_multi_draw_lottery');
        \DBUtil::drop_fields('whitelabel_user_ticket', 'multi_draw_id');
    }
}
