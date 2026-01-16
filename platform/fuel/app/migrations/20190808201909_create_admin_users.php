<?php

namespace Fuel\Migrations;

class Create_admin_users
{
    public function up()
    {
        \DBUtil::create_table(
            'admin_users',
            [
            'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'username' => ['type' => 'varchar', 'constraint' => 45],
            'name' => ['type' => 'varchar', 'constraint' => 45],
            'surname' => ['type' => 'varchar', 'constraint' => 45],
            'email' => ['type' => 'varchar', 'constraint' => 254],
            'language_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
            'timezone' => ['type' => 'varchar', 'constraint' => 40],
            'salt' => ['type' => 'varchar', 'constraint' => 128],
			'hash' => ['type' => 'varchar', 'constraint' => 128],
			'role_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
				[
					'key' => 'role_id',
					'reference' => [
						'table' => 'admin_user_roles',
						'column' => 'id'
                    ],
                ],
				[
					'key' => 'language_id',
					'reference' => [
						'table' => 'language',
						'column' => 'id'
                    ],
                ],
            ]
        );
    }

    public function down()
    {
        \DBUtil::drop_table('admin_users');
    }
}