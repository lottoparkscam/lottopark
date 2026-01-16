<?php

namespace Fuel\Migrations;

class Create_modules
{
	public function up()
	{
		\DBUtil::create_table('modules', [
			'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
			'name' => ['type' => 'varchar', 'constraint' => 50],
        ],
		['id']);
	}

	public function down()
	{
		\DBUtil::drop_table('modules');
	}
}