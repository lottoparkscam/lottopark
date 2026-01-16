<?php

namespace Fuel\Migrations;

class Create_whitelabel_user_whitelabel_user_group
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_user_whitelabel_user_group',
            [
            'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'whitelabel_user_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
            'whitelabel_user_group_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true]
        ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
            [
                'key' => 'whitelabel_user_id',
                'reference' => [
                    'table' => 'whitelabel_user',
                    'column' => 'id'
                ],
                'on_update' => 'NO ACTION',
                'on_delete' => 'CASCADE'
            ],
            [
                'key' => 'whitelabel_user_group_id',
                'reference' => [
                    'table' => 'whitelabel_user_group',
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
        \DBUtil::drop_table('whitelabel_user_whitelabel_user_group');
    }
}
