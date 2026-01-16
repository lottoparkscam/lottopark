<?php

namespace Fuel\Migrations;

class Create_whitelabel_user_balance_change_limit_log
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_user_balance_change_limit_log',
            [
                'id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'created_at' => ['type' => 'datetime'],
                'value' => ['type' => 'decimal', 'constraint' => [9,2]]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'key' => 'whitelabel_id',
                    'reference' =>
                    [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ]
                ]
            ]
        );
    }

    public function down()
    {
        \DBUtil::drop_table('whitelabel_user_balance_change_limit_log');
    }
}
