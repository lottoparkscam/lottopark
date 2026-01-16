<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Add_Table_Whitelabel_Aff_Slot_Commission extends Database_Migration_Graceful
{
    private const TABLE = 'whitelabel_aff_slot_commission';

    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            self::TABLE,
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_aff_id' => ['type' => 'int', 'unsigned' => true],
                'whitelabel_user_id' => ['type' => 'int', 'unsigned' => true],
                'tier' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true],
                'daily_commission_usd' => ['type' => 'decimal', 'constraint' => [7, 2]],
                'ggr_usd' => ['type' => 'decimal', 'constraint' => [7, 2]],
                'created_at' => ['type' => 'date'],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'key' => 'whitelabel_aff_id',
                    'reference' => [
                        'table' => 'whitelabel_aff',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'key' => 'whitelabel_user_id',
                    'reference' => [
                        'table' => 'whitelabel_user',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ]
            ]
        );

        Helper_Migration::generate_unique_key(self::TABLE, [
            'whitelabel_aff_id',
            'tier',
            'whitelabel_user_id',
            'created_at'
        ]);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table(self::TABLE);
    }
}
