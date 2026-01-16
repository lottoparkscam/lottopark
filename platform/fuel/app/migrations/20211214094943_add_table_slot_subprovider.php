<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Add_Table_Slot_Subprovider extends Database_Migration_Graceful
{
    private const TABLE_NAME = 'slot_subprovider';
    private const INDEX_UNIQUE = ['name'];

    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            self::TABLE_NAME,
            [
                'id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'name' => ['type' => 'varchar', 'constraint' => 80, 'unique' => true]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci'
        );

        Helper_Migration::generate_unique_key(self::TABLE_NAME, self::INDEX_UNIQUE);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table(self::TABLE_NAME);
    }
}
