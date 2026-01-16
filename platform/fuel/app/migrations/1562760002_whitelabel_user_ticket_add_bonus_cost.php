<?php

namespace Fuel\Migrations;

/**
 * Description of Whitelabel_User_Ticket_Add_Bonus_Cost
 *
 */
final class Whitelabel_User_Ticket_Add_Bonus_Cost
{
    /**
     *
     */
    public function up()
    {
        \DBUtil::add_fields('whitelabel_user_ticket', [
            'bonus_cost_local' => [
                'type' => 'decimal',
                'constraint' => [15,2],
                'default' => 0.00,
                'after' => 'margin_manager'
            ],
             'bonus_cost_usd' => [
                'type' => 'decimal',
                'constraint' => [15,2],
                'default' => 0.00,
                'after' => 'bonus_cost_local'
            ],
            'bonus_cost' => [
                'type' => 'decimal',
                'constraint' => [15,2],
                'default' => 0.00,
                'after' => 'bonus_cost_usd'
            ],
            'bonus_cost_manager' => [
                'type' => 'decimal',
                'constraint' => [15,2],
                'default' => 0.00,
                'after' => 'bonus_cost'
            ],
        ]);
    }
    
    /**
     *
     */
    public function down()
    {
        \DBUtil::drop_fields('whitelabel_user_ticket', 'bonus_cost_local');
        \DBUtil::drop_fields('whitelabel_user_ticket', 'bonus_cost_usd');
        \DBUtil::drop_fields('whitelabel_user_ticket', 'bonus_cost');
        \DBUtil::drop_fields('whitelabel_user_ticket', 'bonus_cost_manager');
    }
}
