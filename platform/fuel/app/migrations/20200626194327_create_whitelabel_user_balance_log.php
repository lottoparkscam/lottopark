<?php

namespace Fuel\Migrations;

class Create_whitelabel_user_balance_log
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_user_balance_log',
            [
            'id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'auto_increment' => true],
            'whitelabel_user_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
            'created_at' => ['type' => 'datetime'],
            'session_datetime' => ['type' => 'datetime'],
            'message' => ['type' => 'text'],
            'level' => ['type' => 'tinyint', 'unsigned' => true],
            'is_bonus' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => 0],
            'balance_change' => ['type' => 'decimal', 'constraint' => [9,2]]
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
                    ]
                ],
            ]
        );
    }

    public function down()
    {
        \DBUtil::drop_table('whitelabel_user_balance_log');
    }
}
