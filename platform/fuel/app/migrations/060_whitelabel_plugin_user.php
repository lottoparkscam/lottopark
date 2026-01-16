<?php

namespace Fuel\Migrations;

class Whitelabel_Plugin_User
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_plugin_user',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_user_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'whitelabel_plugin_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'data' => ['type' => 'varchar', 'constraint' => 300, 'null' => true, 'default' => null],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_plugin_user_ibfk_1',
                    'key' => 'whitelabel_user_id',
                    'reference' => [
                        'table' => 'whitelabel_user',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_plugin_user_ibfk_2',
                    'key' => 'whitelabel_plugin_id',
                    'reference' => [
                        'table' => 'whitelabel_plugin',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
            ]
        );

        \DBUtil::create_index('whitelabel_plugin_user', 'whitelabel_user_id', 'whitelabel_user_id');
        \DBUtil::create_index('whitelabel_plugin_user', 'whitelabel_plugin_id', 'whitelabel_plugin_id');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_plugin_user', 'whitelabel_plugin_user_ibfk_1');
        \DBUtil::drop_foreign_key('whitelabel_plugin_user', 'whitelabel_plugin_user_ibfk_2');

        \DBUtil::drop_table('whitelabel_plugin_user');
    }
}
