<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Modify_Max_Daily_Bonus_Change_Per_User_In_Whitelabel_Table extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::modify_fields(
            'whitelabel',
            [
                'max_daily_balance_change_per_user' => [
                    'type' => 'decimal',
                    'constraint' => [7, 2],
                    'default' => 0,
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::modify_fields(
            'whitelabel',
            [
                'max_daily_balance_change_per_user' => [
                    'type' => 'decimal',
                    'constraint' => [5, 2],
                    'default' => 0,
                ],
            ]
        );
    }
}