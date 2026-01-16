<?php

namespace Fuel\Migrations;

class Create_admin_user_roles
{
	public function up()
	{
		\DBUtil::create_table('admin_user_roles', [
			'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
			'role' => ['type' => 'varchar', 'constraint' => 45],
        ],
		['id'],
            true,
            false,
            'utf8mb4_unicode_ci'
		);
	}

	public function down()
	{
		\DBUtil::drop_table('admin_user_roles');
	}
}