<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Create_Table_FlipCoin_Game extends Database_Migration_Graceful
{
    private const TABLE = 'flipcoin_game';

    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            self::TABLE,
            [
                'id' => ['type' => 'bigint', 'unsigned' => true, 'auto_increment' => true],
                'slug' => ['type' => 'varchar', 'constraint' => 255],
                'name' => ['type' => 'varchar', 'constraint' => 100],
                'draw_range_start' => ['type' => 'int', 'unsigned' => true],
                'draw_range_end' => ['type' => 'int', 'unsigned' => true],
                'num_draws' => ['type' => 'int', 'unsigned' => true],
                'multiplier' => ['type' => 'decimal', 'constraint' => [5, 2]],
                'available_bets' => ['type' => 'json', 'null' => false],
                'is_enabled' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => 0],
                'is_deleted' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => 0],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
        );
        Helper_Migration::generate_unique_key(self::TABLE, ['slug', 'name']);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table(self::TABLE);
    }
}
