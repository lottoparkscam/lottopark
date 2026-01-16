<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Add_Table_Slot_Provider extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            'slot_provider',
            [
                'id' => ['type' => 'bigint', 'unsigned' => true, 'auto_increment' => true],
                'slug' => ['type' => 'varchar', 'constraint' => 100],
                'api_url' => ['type' => 'varchar', 'constraint' => 255],
                'init_game_path' => ['type' => 'varchar', 'constraint' => 100],
                'init_demo_game_path' => ['type' => 'varchar', 'constraint' => 100],
                'api_credentials' => ['type' => 'json'],
                'game_list_path' => ['type' => 'varchar', 'constraint' => 255, 'null' => true]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci'
        );
        Helper_Migration::generate_unique_key('slot_provider', ['slug']);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table('slot_provider');
    }
}
