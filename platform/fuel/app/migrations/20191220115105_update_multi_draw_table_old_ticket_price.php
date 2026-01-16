<?php

namespace Fuel\Migrations;

class Update_Multi_Draw_Table_Old_Ticket_Price
{
    public function up()
    {
        \DBUtil::add_fields('multi_draw', [
            'old_ticket_price' => ['type' => 'decimal', 'constraint' => [9,2], 'unsigned' => true],
        ]);
    }

    public function down()
    {
        \DBUtil::drop_fields('multi_draw', 'old_ticket_price');
    }
}
