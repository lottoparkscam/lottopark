<?php

namespace Fuel\Migrations;

class Update_Whitelabel_Lottery_Multi_draw
{
    public function up()
    {
        \DBUtil::add_fields('whitelabel_lottery', [
            'is_multidraw_enabled' => ['type' => 'tinyint', 'constraint' => 1, 'default' => 0, 'unsigned' => true]
        ]);
    }

    public function down()
    {
        \DBUtil::drop_fields('whitelabel_lottery', 'is_multidraw_enabled');
    }
}
