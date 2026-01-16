<?php

namespace Fuel\Migrations;

class Add_login_to_whitelabel_user
{
    public function up()
    {
        \DBUtil::add_fields(
            'whitelabel_user',
            ['login' => ['type' => 'varchar', 'constraint' => 100, 'null' => true]]
        );

        \DBUtil::create_index('whitelabel_user', 'login', 'whitelabel_user_login_idx', 'UNIQUE');
    }

    public function down()
    {
        \DBUtil::drop_fields(
            'whitelabel_user',
            ['login']
        );
    }
}
