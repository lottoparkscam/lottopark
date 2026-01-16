<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

final class Add_Display_Deposit_Button_To_Whitelabel_Table extends \Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'whitelabel', [
                'display_deposit_button' => [
                    'type' => 'boolean', 
                    'default' => true, 
                    'after' => 'can_user_login_via_site'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'whitelabel', [
                'display_deposit_button',
            ]
        );
    }
}