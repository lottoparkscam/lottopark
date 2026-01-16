<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Add_Table_Synchronizer_Log extends Database_Migration_Graceful
{
    private $tableName = 'synchronizer_log';

    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            $this->tableName,
            [
                'id' => ['type' => 'int', 'constraint' => 3, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'whitelabel_transaction_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'message' => ['type' => 'varchar', 'constraint' => 255],
                'type' => ['constraint' => "'info', 'success', 'warning', 'error'", 'type' => 'enum'],
                'created_at' => ['type' => 'datetime']
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                Helper_Migration::generate_foreign_key($this->tableName, 'whitelabel_id'),
                Helper_Migration::generate_foreign_key($this->tableName, 'whitelabel_transaction_id'),
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table($this->tableName);
    }
}
