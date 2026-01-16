<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_Published_At_Timestamp_To_Whitelabel_Transaction extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'whitelabel_transaction',
            [
                'published_at_timestamp' => [
                    'type' => 'varchar',
                    'constraint' => 256,
                    'default' => null,
                    'after' => 'date_confirmed',
                    'null' => true,
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'whitelabel_transaction',
            [
                'published_at_timestamp',
            ]
        );
    }
}
