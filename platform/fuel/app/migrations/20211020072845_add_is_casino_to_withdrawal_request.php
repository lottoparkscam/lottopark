<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_Is_Casino_To_Withdrawal_Request extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'withdrawal_request',
            [
                'is_casino' => [
                    'type' => 'tinyint',
                    'constraint' => 1,
                    'default' => 0,
                    'unsigned' => true,
                    'after' => 'status'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'withdrawal_request',
            [
                'is_casino',
            ]
        );
    }
}