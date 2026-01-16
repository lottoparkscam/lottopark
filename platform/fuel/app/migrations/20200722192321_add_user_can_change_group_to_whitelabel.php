<?php

namespace Fuel\Migrations;

class Add_user_can_change_group_to_whitelabel
{
    public function up()
    {
        \DBUtil::add_fields('whitelabel', [
            'user_can_change_group' => ['type' => 'bool', 'null' => false, 'default' => false]
        ]);
    }

    public function down()
    {
        \DBUtil::drop_fields('whitelabel', [
            'user_can_change_group'
        ]);
    }
}
