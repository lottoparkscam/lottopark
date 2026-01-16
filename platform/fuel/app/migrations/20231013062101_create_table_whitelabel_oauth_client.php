<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DB;
use Fuel\Core\DBUtil;
use Helper_Migration;

/**
 * The migration in accordance with the OAuth2\Storage requirements.
 *
 * @see https://bshaffer.github.io/oauth2-server-php-docs/storage/custom/
 */
final class Create_Table_Whitelabel_Oauth_Client extends Database_Migration_Graceful
{
    private const TABLE = 'whitelabel_oauth_client';

    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            self::TABLE,
            [
                'client_id' => ['type' => 'varchar', 'constraint' => 80],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'name' => ['type' => 'varchar', 'constraint' => 50],
                'domain' => ['type' => 'varchar', 'constraint' => 200],
                'autologin_uri' => ['type' => 'varchar', 'constraint' => 2000, 'null' => true],
                'client_secret' => ['type' => 'varchar', 'constraint' => 80, 'null' => true],
                'redirect_uri' => ['type' => 'varchar', 'constraint' => 2000, 'null' => true],
                'scope' => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
                'grant_types' => ['type' => 'varchar', 'constraint' => 80, 'null' => true],
                'created_at' => ['type' => 'datetime', 'null' => true, 'default' => DB::expr('CURRENT_TIMESTAMP')]
            ],
            ['client_id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                Helper_Migration::generate_foreign_key(self::TABLE, 'whitelabel_id'),
            ]
        );

        Helper_Migration::generateIndexKey(self::TABLE, ['scope', 'created_at']);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table(self::TABLE);
    }
}
