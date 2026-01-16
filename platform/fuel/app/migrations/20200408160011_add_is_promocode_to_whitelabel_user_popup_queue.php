<?php

namespace Fuel\Migrations;

class Add_is_promocode_to_whitelabel_user_popup_queue
{
    public function up()
    {
        \DBUtil::add_fields(
            'whitelabel_user_popup_queue',
            ['is_promocode' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => 0]]
        );
    }

    public function down()
    {
        \DBUtil::drop_fields(
            'whitelabel_user_popup_queue',
            ['is_promocode']
        );
    }
}
