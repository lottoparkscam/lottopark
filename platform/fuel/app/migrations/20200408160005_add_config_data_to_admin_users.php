<?php

namespace Fuel\Migrations;

class Add_config_data_to_admin_users
{
    public function up()
    {
        \DBUtil::add_fields(
            'admin_users',
            ['config_data' => ['type' => 'text', 'null' => true]]
        );
    }

    public function down()
    {
        \DBUtil::drop_fields('admin_users', [
            'config_data'
        ]);
    }
}
