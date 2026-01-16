<?php

namespace Fuel\Migrations;

class Update_lottery_delay_date_local_date_delay
{
    public function up()
    {
        \DBUtil::modify_fields('lottery_delay', [
            'date_local' => [
                'type' => 'datetime'
            ],
            'date_delay' => [
                'type' => 'datetime'
            ],
        ]);
    }

    public function down()
    {
        \DBUtil::modify_fields('lottery_delay', [
            'date_local' => [
                'type' => 'date'
            ],
            'date_delay' => [
                'type' => 'date'
            ],
        ]);
    }
}