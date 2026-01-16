<?php

namespace Fuel\Migrations;

class Whitelabel_Plugin
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_plugin',
            [
                'id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'plugin' => ['type' => 'varchar', 'constraint' => 100],
                'is_enabled' => ['type' => 'tinyint', 'constraint' => 1, 'null' => true, 'default' => 0],
                'options' => ['type' => 'varchar', 'constraint' => 300, 'null' => true, 'default' => null],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_plugin_ibfk_1',
                    'key' => 'whitelabel_id',
                    'reference' => [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ]
            ]
        );

        \DBUtil::create_index('whitelabel_plugin', 'whitelabel_id', 'whitelabel_id');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_plugin', 'whitelabel_plugin_ibfk_1');

        \DBUtil::drop_table('whitelabel_plugin');
    }
}
