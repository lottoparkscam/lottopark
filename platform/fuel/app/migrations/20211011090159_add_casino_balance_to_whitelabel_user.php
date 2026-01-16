<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_Casino_Balance_To_Whitelabel_User extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields('whitelabel_user', [
            'casino_balance' => [
                'type' => 'decimal',
                'constraint' => [9,2],
                'unsigned' => true,
                'default' => 0.00,
                'after' => 'bonus_balance'
            ]
        ]);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields('whitelabel_user', [
            'casino_balance'
        ]);
    }
}