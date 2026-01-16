<?php

namespace Fuel\Migrations;

class Add_show_ok_in_welcome_popup_to_whitelabel
{
    public function up()
    {
        \DBUtil::add_fields(
            'whitelabel',
            ['show_ok_in_welcome_popup' => ['type' => 'bool', 'default' => true, 'after' => 'show_powered_by']]
        );
    }

    public function down()
    {
        \DBUtil::drop_fields(
            'whitelabel',
            ['show_ok_in_welcome_popup']
        );
    }
}
