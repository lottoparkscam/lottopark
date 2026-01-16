<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Alter_Id_In_Crm_Log_Table extends Database_Migration_Graceful
{
	protected function up_gracefully(): void
	{
		DBUtil::modify_fields('crm_log', [
			'id' => [
				'type' => 'int',
				'constraint' => 10,
				'unsigned' => true,
				'auto_increment' => true
			]
		]);
	}

	protected function down_gracefully(): void
	{
		DBUtil::modify_fields('crm_log', [
			'id' => [
				'type' => 'tinyint',
				'constraint' => 3,
				'unsigned' => true,
				'auto_increment' => true
			]
		]);
	}
}