<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Remove_Cloudflare_Credentials_Table extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::drop_foreign_key('whitelabel', Helper_Migration::generate_foreign_key('whitelabel', 'cloudflare_credentials_id')['constraint']);
        DBUtil::drop_fields(
            'whitelabel',
            [
                'cloudflare_credentials_id'
            ]
        );
        DBUtil::drop_table('cloudflare_credentials');
    }

    protected function down_gracefully(): void
    {
        DBUtil::create_table(
            'cloudflare_credentials',
            [
                'id' => ['type' => 'int', 'unsigned' => true, 'auto_increment' => true],
                'auth_email' => ['type' => 'varchar', 'constraint' => 254],
                'auth_key' => ['type' => 'varchar', 'constraint' => 254]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci'
        );

        DBUtil::add_fields(
            'whitelabel',
            [
                'cloudflare_credentials_id' => [
                    'type' => 'int',
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'language_id',
                ],
            ]
        );

        DBUtil::add_foreign_key('whitelabel', Helper_Migration::generate_foreign_key('whitelabel', 'cloudflare_credentials_id'));
    }
}
