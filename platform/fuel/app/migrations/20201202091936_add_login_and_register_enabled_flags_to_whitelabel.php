<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

final class Add_Login_And_Register_Enabled_Flags_To_Whitelabel extends \Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields('whitelabel', [
            'can_user_register_via_site' => [
                'type'          => 'tinyint',
                'constraint'    => 1,
                'unsigned'      => true,
                'default'       => true,
                'after'         => 'can_user_select_group_while_register'
            ],
            'can_user_login_via_site' => [
                'type'          => 'tinyint',
                'constraint'    => 1,
                'unsigned'      => true,
                'default'       => true,
                'after'         => 'can_user_register_via_site'
            ]
        ]);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields('whitelabel', [
            'can_user_register_via_site',
            'can_user_login_via_site'
        ]);
    }
}