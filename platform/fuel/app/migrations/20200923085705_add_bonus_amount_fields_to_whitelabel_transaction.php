<?php

namespace Fuel\Migrations;

class Add_bonus_amount_fields_to_whitelabel_transaction
{
    public function up()
    {
        \DBUtil::add_fields('whitelabel_transaction', [
            'bonus_amount_payment' => [
                'type' => 'decimal',
                'constraint' => [15,2],
                'default' => 0.00,
                'after' => 'margin_manager'
            ],
             'bonus_amount_usd' => [
                'type' => 'decimal',
                'constraint' => [15,2],
                'default' => 0.00,
                'after' => 'bonus_amount_payment'
            ],
            'bonus_amount' => [
                'type' => 'decimal',
                'constraint' => [15,2],
                'default' => 0.00,
                'after' => 'bonus_amount_usd'
            ],
            'bonus_amount_manager' => [
                'type' => 'decimal',
                'constraint' => [15,2],
                'default' => 0.00,
                'after' => 'bonus_amount'
            ],
        ]);
    }
    
    public function down()
    {
        \DBUtil::drop_fields('whitelabel_transaction', [
            'bonus_amount_payment',
            'bonus_amount_usd',
            'bonus_amount',
            'bonus_amount_manager'
        ]);
    }
}
