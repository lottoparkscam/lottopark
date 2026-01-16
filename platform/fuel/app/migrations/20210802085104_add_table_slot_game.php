<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Add_Table_Slot_Game extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            'slot_game',
            [
                'id' => ['type' => 'bigint', 'unsigned' => true, 'auto_increment' => true],
                'slot_provider_id' => ['type' => 'bigint', 'unsigned' => true],
                'uuid' => ['type' => 'varchar', 'constraint' => 255],
                'is_deleted' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => 0],
                'name' => ['type' => 'varchar', 'constraint' => 100],
                'image' => ['type' => 'varchar', 'constraint' => 255],
                'type' => ['type' => 'varchar', 'constraint' => 50],
                'provider' => ['type' => 'varchar', 'constraint' => 80],
                'technology' => ['type' => 'varchar', 'constraint' => 50],
                'has_demo' => ['type' => 'tinyint', 'default' => 0, 'constraint' => 1, 'unsigned' => true],
                'has_lobby' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true],
                'has_freespins' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true],
                'is_mobile' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true],
                'freespin_valid_until_full_day' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                Helper_Migration::generate_foreign_key('slot_game', 'slot_provider_id')
            ]
        );
        Helper_Migration::generate_unique_key('slot_game', ['slot_provider_id','uuid']);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table('slot_game');
    }
}
