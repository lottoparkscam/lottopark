<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Add_Table_Whitelabel_Slot_Provider extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        $tableName = 'whitelabel_slot_provider';
        DBUtil::create_table(
            $tableName,
            [
                'id' => ['type' => 'bigint', 'unsigned' => true, 'auto_increment' => true],
                'slot_provider_id' => ['type' => 'bigint', 'unsigned' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
                'is_enabled' => ['type' => 'tinyint', 'constraint' => 1, 'default' => 0],
                'max_monthly_money_around_usd' => ['type' => 'double', 'constraint' => [9,2], 'default' => 50000.0],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                Helper_Migration::generate_foreign_key('whitelabel_slot_provider', 'slot_provider_id'),
                Helper_Migration::generate_foreign_key('whitelabel_slot_provider', 'whitelabel_id')
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table('whitelabel_slot_provider');
    }
}
