<?php

namespace Fuel\Migrations;

class Add_refer_bonus_used_to_whitelabel_user
{
    public function up()
    {
        \DBUtil::add_fields('whitelabel_user', [
            'refer_bonus_used' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => 0]
        ]);
    }

    public function down()
    {
        \DBUtil::drop_fields('whitelabel_user', [
            'refer_bonus_used'
        ]);
    }
}
