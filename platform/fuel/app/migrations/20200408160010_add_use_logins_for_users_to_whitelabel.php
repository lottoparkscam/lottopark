<?php

namespace Fuel\Migrations;

class Add_use_logins_for_users_to_whitelabel
{
    public function up()
    {
        \DBUtil::add_fields(
            'whitelabel',
            ['use_logins_for_users' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => 0]]
        );
    }

    public function down()
    {
        \DBUtil::drop_fields(
            'whitelabel',
            ['use_logins_for_users']
        );
    }
}
