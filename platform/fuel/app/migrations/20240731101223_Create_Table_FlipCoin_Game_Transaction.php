<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Create_Table_FlipCoin_Game_Transaction extends Database_Migration_Graceful
{
    private const TABLE = 'flipcoin_game_transaction';

    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            self::TABLE,
            [
                'id' => ['type' => 'bigint', 'unsigned' => true, 'auto_increment' => true],
                'flipcoin_game_id' => ['type' => 'bigint', 'unsigned' => true],
                'whitelabel_user_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
                'currency_id' => ['type' => 'tinyint', 'unsigned' => true],
                'token' => ['type' => 'varchar', 'constraint' => 24],
                'amount' => ['type' => 'decimal', 'constraint' => [7, 2]],
                'amount_usd' => ['type' => 'double', 'constraint' => [7, 2]],
                'amount_manager' => ['type' => 'double', 'constraint' => [7, 2]],
                'prize' => ['type' => 'decimal', 'constraint' => [7, 2]],
                'prize_usd' => ['type' => 'double', 'constraint' => [7, 2]],
                'prize_manager' => ['type' => 'double', 'constraint' => [7, 2]],
                'type' => ['constraint' => "'win','loss'", 'type' => 'enum', 'null' => true],
                'user_selected_number' => ['type' => 'tinyint', 'constraint' => 2, 'unsigned' => true],
                'system_drawn_number' => ['type' => 'tinyint', 'constraint' => 2, 'unsigned' => true],
                'created_at' => ['type' => 'datetime'],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                Helper_Migration::generate_foreign_key(self::TABLE, 'whitelabel_user_id'),
                Helper_Migration::generate_foreign_key(self::TABLE, 'flipcoin_game_id'),
                Helper_Migration::generate_foreign_key(self::TABLE, 'currency_id')
            ]
        );
        Helper_Migration::generate_unique_key(self::TABLE, ['token']);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table(self::TABLE);
    }
}
