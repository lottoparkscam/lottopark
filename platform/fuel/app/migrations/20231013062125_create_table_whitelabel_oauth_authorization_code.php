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
final class Create_Table_Whitelabel_Oauth_Authorization_Code extends Database_Migration_Graceful
{
    private const TABLE = 'whitelabel_oauth_authorization_code';

    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            self::TABLE,
            [
                'authorization_code' => ['type' => 'varchar', 'constraint' => 40],
                'client_id' => ['type' => 'varchar', 'constraint' => 80],
                'user_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'redirect_uri' => ['type' => 'varchar', 'constraint' => 2000, 'null' => true],
                'expires' => ['type' => 'timestamp'],
                'scope' => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
                'id_token' => ['type' => 'varchar', 'constraint' => 1000, 'null' => true],
                'code_challenge' => ['type' => 'varchar', 'constraint' => 1000, 'null' => true],
                'code_challenge_method' => ['type' => 'varchar', 'constraint' => 20, 'null' => true],
            ],
            ['authorization_code'],
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
