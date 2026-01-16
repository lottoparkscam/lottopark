<?php

namespace Fuel\Migrations;

class Add_bonus_amount_fields_to_multi_draw
{
    public function up()
    {
        \DBUtil::add_fields('multi_draw', [
            'bonus_amount' => [
                'type' => 'decimal',
                'constraint' => [15,2],
                'default' => 0.00,
                'after' => 'amount_manager'
            ],
             'bonus_amount_usd' => [
                'type' => 'decimal',
                'constraint' => [15,2],
                'default' => 0.00,
                'after' => 'bonus_amount'
            ],
            'bonus_amount_manager' => [
                'type' => 'decimal',
                'constraint' => [15,2],
                'default' => 0.00,
                'after' => 'bonus_amount_usd'
            ],
        ]);
    }
    
    public function down()
    {
        \DBUtil::drop_fields('multi_draw', [
            'bonus_amount',
            'bonus_amount_usd',
            'bonus_amount_manager'
        ]);
    }
}
