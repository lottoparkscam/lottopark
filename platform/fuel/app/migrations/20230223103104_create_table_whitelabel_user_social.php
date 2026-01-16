<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Create_Table_Whitelabel_User_Social extends Database_Migration_Graceful
{
    private const TABLE = 'whitelabel_user_social';
    private const COLUMNS = ['is_confirmed', 'whitelabel_social_api_id'];

    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            self::TABLE,
            [
                'id' => ['type' => 'int', 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_social_api_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'whitelabel_user_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'social_user_id' => ['type' => 'varchar', 'constraint' => 50],
                'is_confirmed' => ['type' => 'boolean', 'default' => false],
                'activation_hash' => ['type' => 'varchar', 'constraint' => 128, 'null' => true],
                'last_hash_sent_at' => ['type' => 'datetime', 'null' => true],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                Helper_Migration::generate_foreign_key(self::TABLE, 'whitelabel_social_api_id'),
                Helper_Migration::generate_foreign_key(self::TABLE, 'whitelabel_user_id'),
            ]
        );
        Helper_Migration::generate_unique_key(self::TABLE, ['whitelabel_user_id', 'whitelabel_social_api_id']);
        Helper_Migration::generateIndexKey(self::TABLE, self::COLUMNS);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table(self::TABLE);
    }
}
