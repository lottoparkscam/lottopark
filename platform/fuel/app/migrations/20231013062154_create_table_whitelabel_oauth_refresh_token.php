<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

/**
 * The migration in accordance with the OAuth2\Storage requirements.
 *
 * @see https://bshaffer.github.io/oauth2-server-php-docs/storage/custom/
 */
final class Create_Table_Whitelabel_Oauth_Refresh_Token extends Database_Migration_Graceful
{
    private const TABLE = 'whitelabel_oauth_refresh_token';

    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            self::TABLE,
            [
                'refresh_token' => ['type' => 'varchar', 'constraint' => 40],
                'client_id' => ['type' => 'varchar', 'constraint' => 80],
                'user_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'expires' => ['type' => 'timestamp'],
                'scope' => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            ],
            ['refresh_token'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                Helper_Migration::generate_foreign_key(self::TABLE, 'user_id', destinationTable: 'whitelabel_user'),
            ]
        );

        Helper_Migration::generateIndexKey(self::TABLE, ['scope', 'expires']);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table(self::TABLE);
    }
}
