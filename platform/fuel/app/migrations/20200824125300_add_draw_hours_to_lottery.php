<?php

namespace Fuel\Migrations;

class Add_draw_hours_to_lottery
{
    public function up()
    {
        \DBUtil::add_fields('lottery', [
            'draw_hours' => [
                'type' => 'json', 
                'null' => true, 
                'after' => 'draw_hour_local'
            ]
        ]);
    }

    public function down()
    {
        \DBUtil::drop_fields('lottery', [
            'draw_hours'
        ]);
    }
}
