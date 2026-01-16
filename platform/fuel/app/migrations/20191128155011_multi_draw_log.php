<?php

namespace Fuel\Migrations;

class Multi_Draw_Log
{
    public function up()
    {
        \DBUtil::create_table(
            'multi_draw_log',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'multi_draw_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'date' => ['type' => 'datetime'],
                'type' => ['type' => 'tinyint', 'constraint' => 4, 'unsigned' => true],
                'message' => ['type' => 'text'],
                'data' => ['type' => 'mediumtext', 'null' => true],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'multi_draw_multi_draw_id_multi_draw',
                    'key' => 'multi_draw_id',
                    'reference' => [
                        'table' => 'multi_draw',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
            ]
        );

        \DBUtil::create_index('multi_draw_log', 'multi_draw_id', 'multi_draw_multi_draw_id_multi_draw');
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('multi_draw_log', 'multi_draw_multi_draw_id_multi_draw');

        \DBUtil::drop_table('multi_draw_log');
    }
}
