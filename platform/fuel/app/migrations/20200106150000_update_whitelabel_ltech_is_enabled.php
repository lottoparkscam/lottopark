<?php

namespace Fuel\Migrations;

class Update_Whitelabel_Ltech_Is_Enabled
{
    public function up()
    {
        \DBUtil::add_fields('whitelabel_ltech', [
            'is_enabled' => ['type' => 'tinyint', 'constraint' => 1, 'default' => 1],
        ]);
    }

    public function down()
    {
        \DBUtil::drop_fields('whitelabel_ltech', 'is_enabled');
    }
}
