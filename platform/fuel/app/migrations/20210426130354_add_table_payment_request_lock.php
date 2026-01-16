<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Add_Table_Payment_Request_Lock extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
    	$tableName = 'payment_request_lock';
        DBUtil::create_table(
            $tableName,
            [
                'id' => ['type' => 'int', 'constraint' => 4, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 4, 'unsigned' => true],
                'whitelabel_user_id' => ['type' => 'int', 'constraint' => 4, 'unsigned' => true],
				'payment_method_id' => ['type' => 'int', 'constraint' => 4, 'unsigned' => true],
				'requests_count' => ['type' => 'int', 'constraint' => 4, 'unsigned' => true],
                'first_request_date' => ['type' => 'datetime']
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
			[
				Helper_Migration::generate_foreign_key($tableName, 'whitelabel_id'),
				Helper_Migration::generate_foreign_key($tableName, 'whitelabel_user_id'),
				Helper_Migration::generate_foreign_key($tableName, 'payment_method_id'),
			]
        );

		DBUtil::create_index(
			$tableName,
			['whitelabel_user_id', 'payment_method_id'],
			"{$tableName}__whitelabel_user_id__payment_method_id__idx"
		);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table('send_payment_request_lock');
    }
}