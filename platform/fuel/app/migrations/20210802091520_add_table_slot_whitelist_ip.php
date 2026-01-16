<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Add_Table_Slot_Whitelist_Ip extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            'slot_whitelist_ip',
            [
                'id' => ['type' => 'bigint', 'unsigned' => true, 'auto_increment' => true],
                'slot_provider_id' => ['type' => 'bigint', 'unsigned' => true],
                'ip' => ['type' => 'varchar', 'constraint' => 15]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                Helper_Migration::generate_foreign_key('slot_whitelist_ip', 'slot_provider_id'),
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table('slot_whitelist_ip');
    }
}
