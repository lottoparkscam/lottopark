<?php

namespace Fuel\Migrations;

class Update_Lottery_Table
{
    public function up()
    {
        \DBUtil::add_fields('lottery', [
            'is_multidraw_enabled' => ['type' => 'tinyint', 'constraint' => 1, 'default' => 0, 'unsigned' => true]
        ]);
    }

    public function down()
    {
        \DBUtil::drop_fields('lottery', 'is_multidraw_enabled');
    }
}
