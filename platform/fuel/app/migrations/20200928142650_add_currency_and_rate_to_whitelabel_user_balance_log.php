<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;

final class Add_Currency_And_Rate_To_Whitelabel_User_Balance_Log extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        \DBUtil::add_fields(
            'whitelabel_user_balance_log',
            [
                'balance_change_currency_code' => [
                    'type' => 'varchar',
                    'constraint' => 3,
                    'null' => true,
                    'default' => null,
                    'after' => 'balance_change'
                ],
                'balance_change_import' => [
                    'type' => 'decimal',
                    'constraint' => [9,2],
                    'default' => 0.00,
                    'after' => 'balance_change_currency_code'
                ],
                'balance_change_import_currency_code' => [
                    'type' => 'varchar',
                    'constraint' => 3,
                    'null' => true,
                    'default' => null,
                    'after' => 'balance_change_import'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        \DBUtil::drop_fields(
            'whitelabel_user_balance_log',
            [
                'balance_change_currency_code',
                'balance_change_import',
                'balance_change_import_currency_code'
            ]
        );
    }
}
