<?php

namespace Fuel\Migrations;

class Whitelabel_Plugin_Log
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_plugin_log',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_plugin_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'date' => ['type' => 'datetime'],
                'type' => ['type' => 'tinyint', 'constraint' => 3],
                'message' => ['type' => 'text'],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_plugin_log_ibfk_1',
                    'key' => 'whitelabel_plugin_id',
                    'reference' => [
                        'table' => 'whitelabel_plugin',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ]
            ]
        );

        \DBUtil::create_index('whitelabel_plugin_log', 'whitelabel_plugin_id', 'whitelabel_plugin_id');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_plugin_log', 'whitelabel_plugin_log_ibfk_1');

        \DBUtil::drop_table('whitelabel_plugin_log');
    }
}
