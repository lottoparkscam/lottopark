<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Add_Table_Whitelabel_Slot_Provider_Subprovider extends Database_Migration_Graceful
{
    private const TABLE_NAME = 'whitelabel_slot_provider_subprovider';
    private const INDEX_UNIQUE = ['whitelabel_slot_provider_id', 'slot_subprovider_id'];

    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            self::TABLE_NAME,
            [
                'id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_slot_provider_id' => ['type' => 'bigint', 'unsigned' => true],
                'slot_subprovider_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
                'is_enabled' => ['type' => 'tinyint', 'default' => 1]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                Helper_Migration::generate_foreign_key(self::TABLE_NAME, 'whitelabel_slot_provider_id'),
                Helper_Migration::generate_foreign_key(self::TABLE_NAME, 'slot_subprovider_id')
            ]
        );

        Helper_Migration::generate_unique_key(self::TABLE_NAME, self::INDEX_UNIQUE);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table(self::TABLE_NAME);
    }
}
