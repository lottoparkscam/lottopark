<?php

namespace Fuel\Migrations;

class Update_user_draw_notification_lottery_draw_date
{
    public function up()
    {
        \DBUtil::modify_fields('user_draw_notification', [
            'lottery_draw_date' => [
                'type' => 'datetime',
                'null' => true
            ],
        ]);
    }

    public function down()
    {
        \DBUtil::modify_fields('user_draw_notification', [
            'lottery_draw_date' => [
                'type' => 'date',
                'null' => true
            ],
        ]);
    }
}


