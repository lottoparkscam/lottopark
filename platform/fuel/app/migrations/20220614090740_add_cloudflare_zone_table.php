<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Add_Cloudflare_Zone_Table extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            'cloudflare_zone',
            [
                'id' => ['type' => 'int', 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'identifier' => ['type' => 'varchar', 'constraint' => 254],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                Helper_Migration::generate_foreign_key('whitelabel', 'whitelabel_id')
            ]
        );

        Helper_Migration::generate_unique_key('cloudflare_zone', ['whitelabel_id']);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table('cloudflare_zone');
    }
}