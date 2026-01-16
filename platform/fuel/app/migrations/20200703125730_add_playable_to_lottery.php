<?php

namespace Fuel\Migrations;

class Add_playable_to_lottery
{
    public function up()
    {
        \DBUtil::add_fields(
            'lottery',
            ['playable' => ['type' => 'bool', 'null' => false, 'default' => true, 'after' => 'is_enabled']]
        );
    }

    public function down()
    {
        \DBUtil::drop_fields(
            'lottery',
            ['playable']
        );
    }
}
