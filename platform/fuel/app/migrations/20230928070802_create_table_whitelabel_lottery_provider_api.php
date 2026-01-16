<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Create_Table_Whitelabel_Lottery_Provider_Api extends Database_Migration_Graceful
{
    private const TABLE = 'whitelabel_lottery_provider_api';

    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            self::TABLE,
            [
                'id' => ['type' => 'int', 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'api_key' => ['type' => 'varchar', 'constraint' => 50],
                'api_secret' => ['type' => 'varchar', 'constraint' => 50],
                'scan_confirm_url' => ['type' => 'varchar', 'constraint' => 254],
                'is_enabled' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
                'created_at' => ['type' => 'datetime'],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                Helper_Migration::generate_foreign_key(self::TABLE, 'whitelabel_id'),
            ],
        );

        Helper_Migration::generateIndexKey(self::TABLE, ['whitelabel_id', 'is_enabled']);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table(self::TABLE);
    }
}
