<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Create_Table_Whitelabel_Aff_Casino_Group extends Database_Migration_Graceful
{
    private const TABLE = 'whitelabel_aff_casino_group';

    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            self::TABLE,
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'name' => ['type' => 'varchar', 'constraint' => 40],
                'commission_percentage_value_for_tier_1' => [
                    'type' => 'decimal',
                    'constraint' => [5, 2],
                    'unsigned' => true,
                ],
                'commission_percentage_value_for_tier_2' => [
                    'type' => 'decimal',
                    'constraint' => [5, 2],
                    'unsigned' => true,
                ],
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
