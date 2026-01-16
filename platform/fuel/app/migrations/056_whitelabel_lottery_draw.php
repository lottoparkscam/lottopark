<?php

namespace Fuel\Migrations;

class Whitelabel_Lottery_Draw
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_lottery_draw',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'lottery_draw_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_lottery_draw_lottery_draw_id_lottery_draw_idfx',
                    'key' => 'lottery_draw_id',
                    'reference' => [
                        'table' => 'lottery_draw',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_lottery_draw_whitelabel_id_whitelabel_idfx',
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

        \DBUtil::create_index('whitelabel_lottery_draw', 'whitelabel_id', 'whitelabel_lottery_draw_whitelabel_id_whitelabel_idfx_idx');
        \DBUtil::create_index('whitelabel_lottery_draw', 'lottery_draw_id', 'whitelabel_lottery_draw_lottery_draw_id_lottery_draw_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_lottery_draw', 'whitelabel_lottery_draw_lottery_draw_id_lottery_draw_idfx');
        \DBUtil::drop_foreign_key('whitelabel_lottery_draw', 'whitelabel_lottery_draw_whitelabel_id_whitelabel_idfx');

        \DBUtil::drop_table('whitelabel_lottery_draw');
    }
}
