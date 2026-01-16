<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Create_Table_Social_Type extends Database_Migration_Graceful
{
    private const TABLE = 'social_type';

    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            self::TABLE,
            [
                'id' => ['type' => 'int', 'unsigned' => true, 'auto_increment' => true],
                'type' => ['constraint' => "'facebook'", 'type' => 'enum'],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
        );
        Helper_Migration::generate_unique_key(self::TABLE, ['type']);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table(self::TABLE);
    }
}
