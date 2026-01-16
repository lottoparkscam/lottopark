<?php

namespace Fuel\Migrations;

class Lottery_Group
{
    public function up()
    {
        \DBUtil::create_table(
            'lottery_group',
            [
                'id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'auto_increment' => true],
                'lottery_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'group_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'lottery_group_l_id_l_idfx_idx',
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
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('lottery_group', 'lottery_group_l_id_l_idfx_idx');

        \DBUtil::drop_table('lottery_group');
    }
}
