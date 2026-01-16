<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

final class Add_Max_Daily_Bonus_Change_Per_User_Field_To_Whitelabel_Table extends \Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'whitelabel', [
                'max_daily_balance_change_per_user' => [
                    'type' => 'decimal',
                    'constraint' => [5, 2],
                    'default' => 0,
                    'after' => 'user_balance_change_limit'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'whitelabel', [
                'max_daily_balance_change_per_user',
            ]
        );
    }
}