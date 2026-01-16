<?php

namespace Fuel\Migrations;

class Whitelabel_Ltech_Lock_Columns
{
    public function up()
    {
        \DBUtil::add_fields('whitelabel_ltech', [
            'locked' => ['type' => 'tinyint', 'constraint' => 1, 'default' => 0],
            'can_be_locked' => ['type' => 'tinyint', 'constraint' => 1, 'default' => 1],
        ]);
    }

    public function down()
    {
        \DBUtil::drop_fields('whitelabel_ltech', 'locked');
        \DBUtil::drop_fields('whitelabel_ltech', 'can_be_locked');
    }
}
