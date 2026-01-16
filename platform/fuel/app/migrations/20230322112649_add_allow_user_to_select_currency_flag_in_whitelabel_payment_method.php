<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_Allow_User_To_Select_Currency_Flag_In_Whitelabel_Payment_Method extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'whitelabel_payment_method',
            [
                'allow_user_to_select_currency' => [
                    'type' => 'boolean',
                    'default' => false,
                    'after' => 'only_deposit',
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'whitelabel_payment_method',
            [
                'allow_user_to_select_currency',
            ]
        );
    }
}