<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Create_Table_Mini_Game_User_Promo_Code extends Database_Migration_Graceful
{
    private const TABLE = 'mini_game_user_promo_code';

    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            self::TABLE,
            [
                'id' => ['type' => 'bigint', 'unsigned' => true, 'auto_increment' => true],
                'mini_game_promo_code_id' => ['type' => 'bigint', 'constraint' => 10, 'unsigned' => true],
                'mini_game_id' => ['type' => 'bigint', 'constraint' => 10, 'unsigned' => true],
                'whitelabel_user_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'used_free_spin_count' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'default' => 0],
                'has_used_all_spins' => ['type' => 'tinyint', 'constraint' => 1, 'default' => false],
                'created_at' => ['type' => 'datetime'],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                Helper_Migration::generate_foreign_key(self::TABLE, 'mini_game_promo_code_id'),
                Helper_Migration::generate_foreign_key(self::TABLE, 'mini_game_id'),
                Helper_Migration::generate_foreign_key(self::TABLE, 'whitelabel_user_id'),
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table(self::TABLE);
    }
}
