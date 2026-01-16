<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

final class Add_Balance_Change_Before_Conversion_To_Whitelabel_User_Balance_Change_Log extends \Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'whitelabel_user_balance_log', [
                'balance_change_before_conversion' => [
                    'type' => 'decimal',
                    'constraint' => [9, 2],
                    'default' => 0.00,
                    'after' => 'balance_change_currency_code'
                ],
                'balance_change_before_conversion_currency_code' => [
                    'type' => 'varchar',
                    'constraint' => 3,
                    'null' => true,
                    'after' => 'balance_change_before_conversion'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'whitelabel_user_change_log', [
                'balance_change_before_conversion',
                'balance_change_before_conversion_currency_code'
            ]
        );
    }
}