<?php

namespace Fuel\Migrations;

class Update_Multi_Draw_Table_Current_Draw
{
    public function up()
    {
        \DBUtil::modify_fields('multi_draw', [
            'current_draw' => ['type' => 'date', 'null' => true]
        ]);

        \DBUtil::add_fields('multi_draw', [
            'discount' => ['type' => 'decimal', 'constraint' => [4, 2], 'unsigned' => true, 'null' => true, 'default' => null]
        ]);
    }

    public function down()
    {
        \DBUtil::drop_fields('multi_draw', 'discount');
    }
}
