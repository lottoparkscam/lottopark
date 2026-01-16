<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Add_Table_Slot_Open_Game extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        $tableName = 'slot_open_game';
        DBUtil::create_table(
            $tableName,
            [
                'id' => ['type' => 'bigint', 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_slot_provider_id' => ['type' => 'bigint', 'unsigned' => true],
                'slot_game_id' => ['type' => 'bigint', 'unsigned' => true],
                'whitelabel_user_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
                'currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'session_id' => ['type' => 'bigint', 'constraint' => 17, 'unsigned' => true],
                'created_at' => ['type' => 'datetime'],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                Helper_Migration::generate_foreign_key('slot_open_game', 'whitelabel_slot_provider_id'),
                Helper_Migration::generate_foreign_key('slot_open_game', 'slot_game_id'),
                Helper_Migration::generate_foreign_key('slot_open_game', 'whitelabel_user_id'),
                Helper_Migration::generate_foreign_key('slot_open_game', 'currency_id'),
            ]
        );

        Helper_Migration::generate_unique_key('slot_open_game', ['whitelabel_slot_provider_id', 'session_id']);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table('slot_open_game');
    }
}
