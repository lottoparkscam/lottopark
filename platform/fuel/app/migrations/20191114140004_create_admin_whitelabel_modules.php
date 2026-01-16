<?php

namespace Fuel\Migrations;

class Create_admin_whitelabel_modules
{
    public function up()
    {
        \DBUtil::create_table(
            'admin_whitelabel_modules',
            [
            'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'admin_user_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
            'module_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
            'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'key' => 'admin_user_id',
                    'reference' => [
                        'table' => 'admin_users',
                        'column' => 'id'
                    ],
                ],
                [
                    'key' => 'module_id',
                    'reference' => [
                        'table' => 'modules',
                        'column' => 'id'
                    ],
                ],
                [
                    'key' => 'whitelabel_id',
                    'reference' => [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ],
                ]
            ]
        );
    }

    public function down()
    {
        \DBUtil::drop_table('admin_whitelabel_modules');
    }
}
