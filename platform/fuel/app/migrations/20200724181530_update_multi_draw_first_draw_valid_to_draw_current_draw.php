<?php

namespace Fuel\Migrations;

class Update_multi_draw_first_draw_valid_to_draw_current_draw
{
    public function up()
    {
        \DBUtil::modify_fields('multi_draw', [
            'first_draw' => [
                'type' => 'datetime'
            ],
            'valid_to_draw' => [
                'type' => 'datetime'
            ],
            'current_draw' => [
                'type' => 'datetime',
                'null' => true
            ],
        ]);
    }

    public function down()
    {
        \DBUtil::modify_fields('multi_draw', [
            'first_draw' => [
                'type' => 'date'
            ],
            'valid_to_draw' => [
                'type' => 'date'
            ],
            'current_draw' => [
                'type' => 'date',
                'null' => true
            ],
        ]);
    }
}


