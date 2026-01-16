<?php

namespace Fuel\Migrations;

class Add_is_selectable_by_user_to_whitelabel_user_group
{
    public function up()
    {
        \DBUtil::add_fields(
            'whitelabel_user_group',
            ['is_selectable_by_user' => ['type' => 'bool', 'null' => false, 'default' => false]]
        );
    }

    public function down()
    {
        \DBUtil::drop_fields(
            'whitelabel_user_group',
            ['is_selectable_by_user']
        );
    }
}
