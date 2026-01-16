<?php

namespace Fuel\Migrations;

class Add_index_to_whitelabel_user_group
{
    public function up()
    {
        \DBUtil::create_index('whitelabel_user_group', ['name', 'whitelabel_id'], 'whitelabel_user_group_name_idx', 'UNIQUE');
    }

    public function down()
    {
        \DBUtil::drop_index('whitelabel_user_group', 'whitelabel_user_group_name_idx');
    }
}
