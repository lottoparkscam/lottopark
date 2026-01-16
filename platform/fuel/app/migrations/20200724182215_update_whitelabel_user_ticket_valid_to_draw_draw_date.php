<?php

namespace Fuel\Migrations;

class Update_whitelabel_user_ticket_valid_to_draw_draw_date
{
    public function up()
    {
        \DBUtil::modify_fields('whitelabel_user_ticket', [
            'valid_to_draw' => [
                'type' => 'datetime'
            ],
            'draw_date' => [
                'type' => 'datetime',
                'null' => true, 
                'default' => null
            ],
        ]);
    }

    public function down()
    {
        \DBUtil::modify_fields('whitelabel_user_ticket', [
            'valid_to_draw' => [
                'type' => 'date'
            ],
            'draw_date' => [
                'type' => 'date',
                'null' => true, 
                'default' => null
            ],
        ]);
    }
}


