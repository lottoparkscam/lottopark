<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Create_Table_Whitelabel_Slot_Game_Order extends Database_Migration_Graceful
{
    private const TABLE = 'whitelabel_slot_game_order';
    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            self::TABLE,
            [
                'id' => ['type' => 'int', 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'unsigned' => true],
                'order_json' => ['type' => 'json']
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                Helper_Migration::generate_foreign_key(self::TABLE, 'whitelabel_id'),
            ]

        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table(self::TABLE);
    }
}
