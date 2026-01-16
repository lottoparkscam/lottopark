<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Add_Table_Slot_Log extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            'slot_log',
            [
                'id' => ['type' => 'bigint', 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_slot_provider_id' => ['type' => 'bigint', 'unsigned' => true],
                'slot_game_id' => ['type' => 'bigint', 'unsigned' => true, 'null' => true],
                'whitelabel_user_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
                'action' => ['constraint' => "'init','balance','bet','win','refund','rollback'", 'type' => 'enum'],
                'is_error' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => false],
                'request' => ['type' => 'json'],
                'response' => ['type' => 'json'],
                'created_at' => ['type' => 'datetime']
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                Helper_Migration::generate_foreign_key('slot_log', 'whitelabel_slot_provider_id'),
                Helper_Migration::generate_foreign_key('slot_log', 'slot_game_id'),
                Helper_Migration::generate_foreign_key('slot_log', 'whitelabel_user_id')
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table('slot_log');
    }
}
