<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

class Add_Can_User_Select_Group_While_Register_To_Whitelabel_Table extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'whitelabel',
            [
                'can_user_select_group_while_register' => [
                    'type' => 'bool',
                    'after' => 'user_can_change_group',
                    'default' => false
                ]
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'whitelabel',
            [
                'can_user_select_group_while_register'
            ]
        );
    }
}