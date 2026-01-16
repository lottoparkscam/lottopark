<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_Whitelabel_Withdrawal_Show_Flags extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'whitelabel_withdrawal',
            [
                'show' => [
                    'type' => 'tinyint',
                    'constraint' => 1,
                    'unsigned' => true,
                    'default' => 1,
                    'after' => 'withdrawal_id',
                ],
                'show_casino' => [
                    'type' => 'tinyint',
                    'constraint' => 1,
                    'unsigned' => true,
                    'default' => 1,
                    'after' => 'show',
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'whitelabel_withdrawal',
            [
                'show',
                'show_casino',
            ]
        );
    }
}
