<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Create_Table_Ltech_Manual_Draw extends Database_Migration_Graceful
{
    private const TABLE = 'ltech_manual_draw';

    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            self::TABLE,
            [
                'id' => ['type' => 'int', 'unsigned' => true, 'auto_increment' => true],
                'lottery_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'is_processed' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
                'next_draw_date' => ['type' => 'date'],
                'current_draw_date' => ['type' => 'datetime'],
                'current_draw_date_utc' => ['type' => 'datetime'],
                'normal_numbers' => ['type' => 'json'],
                'bonus_numbers' => ['type' => 'json', 'null' => true],
                'additional_number' => ['type' => 'int', 'null' => true],
                'next_jackpot' => ['type' => 'varchar', 'constraint' => 50],
                'prizes' => ['type' => 'json'],
                'winners' => ['type' => 'json'],
                'currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'created_at' => ['type' => 'datetime'],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                Helper_Migration::generate_foreign_key(self::TABLE, 'lottery_id'),
                Helper_Migration::generate_foreign_key(self::TABLE, 'currency_id'),
            ],
        );

        Helper_Migration::generate_unique_key(self::TABLE, ['lottery_id', 'current_draw_date']);
        Helper_Migration::generateIndexKey(self::TABLE, ['lottery_id', 'is_processed', 'current_draw_date']);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table(self::TABLE);
    }
}
