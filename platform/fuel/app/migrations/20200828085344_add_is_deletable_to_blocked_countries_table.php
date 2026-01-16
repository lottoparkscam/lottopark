<?php

namespace Fuel\Migrations;

class Add_Is_Deletable_To_Blocked_Countries_Table
{
    public function up()
    {
        \DBUtil::add_fields(
            'whitelabel_blocked_country',
            ['is_deletable' => ['type' => 'bool', 'default' => true]]
        );
    }

    public function down()
    {
        \DBUtil::drop_fields(
            'whitelabel_blocked_country',
            ['is_deletable']
        );
    }
}
