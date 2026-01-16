<?php

namespace Fuel\Migrations;

class Create_admin_user_table_columns
{
    public function up()
    {
        \DBUtil::create_table(
            'admin_user_table_columns',
            [
            'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'admin_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
            'slug' => ['type' => 'text', 'null' => true]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'key' => 'admin_id',
                    'reference' => [
                        'table' => 'admin_users',
                        'column' => 'id'
                    ],
                ]
            ]
        );
    }

    public function down()
    {
        \DBUtil::drop_table('admin_user_table_columns');
    }
}
