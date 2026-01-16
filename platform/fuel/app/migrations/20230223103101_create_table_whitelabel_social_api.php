<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Create_Table_Whitelabel_Social_Api extends Database_Migration_Graceful
{
    private const TABLE = 'whitelabel_social_api';
    private const COLUMNS = ['is_enabled', 'social_type_id'];

    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            self::TABLE,
            [
                'id' => ['type' => 'int', 'unsigned' => true, 'auto_increment' => true],
                'social_type_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'app_id' => ['type' => 'varchar', 'constraint' => 50],
                'secret' => ['type' => 'varchar', 'constraint' => 50],
                'is_enabled' => ['type' => 'boolean', 'default' => false],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                Helper_Migration::generate_foreign_key(self::TABLE, 'social_type_id'),
                Helper_Migration::generate_foreign_key(self::TABLE, 'whitelabel_id'),
            ],
        );
        Helper_Migration::generate_unique_key(self::TABLE, ['whitelabel_id', 'social_type_id']);
        Helper_Migration::generateIndexKey(self::TABLE, self::COLUMNS);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table(self::TABLE);
    }
}
