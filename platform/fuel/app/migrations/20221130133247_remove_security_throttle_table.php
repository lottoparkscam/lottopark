<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Remove_Security_Throttle_Table extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::truncate_table('security_throttle');
        DBUtil::drop_table('security_throttle');
    }

    protected function down_gracefully(): void
    {
        DBUtil::create_table('security_throttle', [
            'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'ip'         => ['type' => 'varchar', 'constraint' => 45],
            'resource'   => ['type' => 'varchar', 'constraint' => 150],
            'created_at' => ['type' => 'datetime'],
        ], ['id']);
    }
}
