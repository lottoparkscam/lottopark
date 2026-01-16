<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Create_Table_Mini_Game_Promo_Code extends Database_Migration_Graceful
{
    private const TABLE = 'mini_game_promo_code';

    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            self::TABLE,
            [
                'id' => ['type' => 'bigint', 'unsigned' => true, 'auto_increment' => true],
                'code' => ['type' => 'varchar', 'constraint' => 20],
                'mini_game_id' => ['type' => 'bigint', 'constraint' => 10, 'unsigned' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'free_spin_count' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'free_spin_value' => ['type' => 'double', 'constraint' => [7, 2]],
                'usage_limit' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => 1],
                'user_usage_limit' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => 1],
                'date_start' => ['type' => 'datetime', 'null' => true, 'default' => null],
                'date_end' => ['type' => 'datetime', 'null' => true, 'default' => null],
                'is_active' => ['type' => 'tinyint', 'constraint' => 1, 'default' => true],
                'created_at' => ['type' => 'datetime'],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                Helper_Migration::generate_foreign_key(self::TABLE, 'mini_game_id'),
                Helper_Migration::generate_foreign_key(self::TABLE, 'whitelabel_id'),
            ]
        );
        Helper_Migration::generate_unique_key(self::TABLE, ['code']);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table(self::TABLE);
    }
}
