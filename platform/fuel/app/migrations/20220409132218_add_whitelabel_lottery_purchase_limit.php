<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Add_Whitelabel_Lottery_Purchase_Limit extends Database_Migration_Graceful
{
    private string $tableName = 'whitelabel_lottery_purchase_limit';

    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            $this->tableName,
            [
                'id' => ['type' => 'bigint', 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_user_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'whitelabel_lottery_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'counter' => ['type' => 'smallint', 'unsigned' => true],
                'created_at' => ['type' => 'datetime'],
                'updated_at' => ['type' => 'datetime'],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                Helper_Migration::generate_foreign_key($this->tableName, 'whitelabel_user_id'),
                Helper_Migration::generate_foreign_key($this->tableName, 'whitelabel_lottery_id'),
            ]
        );
        Helper_Migration::generate_unique_key($this->tableName, ['whitelabel_user_id','whitelabel_lottery_id']);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table($this->tableName);
    }
}
