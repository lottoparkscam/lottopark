<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

class Alter_whitelabel_raffle_ticket_line_missing_ticket_columns
{
    public function up()
    {
        DBUtil::add_fields('whitelabel_raffle_ticket_line', [
            'amount'   => ['type' => 'decimal', 'constraint' => [9, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'status'],
            'amount_local'   => ['type' => 'decimal', 'constraint' => [9, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'amount'],
            'amount_usd'     => ['type' => 'decimal', 'constraint' => [9, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'amount_local'],
            'amount_payment' => ['type' => 'decimal', 'constraint' => [15, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'amount_usd'],
            'amount_manager' => ['type' => 'decimal', 'constraint' => [15, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'amount_payment'],
            
            'bonus_amount'   => ['type' => 'decimal', 'constraint' => [9, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'amount_manager'],
            'bonus_amount_local'   => ['type' => 'decimal', 'constraint' => [9, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'bonus_amount'],
            'bonus_amount_usd'     => ['type' => 'decimal', 'constraint' => [9, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'bonus_amount_local'],
            'bonus_amount_payment' => ['type' => 'decimal', 'constraint' => [15, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'bonus_amount_usd'],
            'bonus_amount_manager' => ['type' => 'decimal', 'constraint' => [15, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'bonus_amount_payment'],

            'cost_local'   => ['type' => 'decimal', 'constraint' => [9, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'bonus_amount_manager'],
            'cost_usd'     => ['type' => 'decimal', 'constraint' => [9, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'cost_local'],
            'cost'         => ['type' => 'decimal', 'constraint' => [9, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'cost_usd'],
            'cost_manager' => ['type' => 'decimal', 'constraint' => [15, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'cost'],

            'margin_value'   => ['type' => 'decimal', 'constraint' => [5, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'cost_manager'],
            'margin_local'   => ['type' => 'decimal', 'constraint' => [9, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'margin_value'],
            'margin_usd'     => ['type' => 'decimal', 'constraint' => [9, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'margin_local'],
            'margin'         => ['type' => 'decimal', 'constraint' => [9, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'margin_usd'],
            'margin_manager' => ['type' => 'decimal', 'constraint' => [15, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'margin'],

            'income_local' => ['type' => 'decimal', 'constraint' => [9,2], 'default' => 0.00, 'after' => 'margin_manager'],
            'income_usd' => ['type' => 'decimal', 'constraint' => [9,2], 'default' => 0.00, 'after' => 'income_local'],
            'income' => ['type' => 'decimal', 'constraint' => [9,2], 'default' => 0.00, 'after' => 'income_usd'],
            'income_value' => ['type' => 'decimal', 'constraint' => [5,2], 'default' => 0.00, 'unsigned' => true, 'after' => 'income'],
            'income_manager' => ['type' => 'decimal', 'constraint' => [15,2], 'default' => 0.00, 'after' => 'income_value'],
            'income_type' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'after' => 'income_manager'],
        ]);
    }

    public function down()
    {
        DBUtil::drop_fields('whitelabel_raffle_ticket_line', [
            'amount_local',
            'amount_usd',
            'amount_payment',
            'amount_manager',
            
            'bonus_amount_local',
            'bonus_amount_usd',
            'bonus_amount_payment',
            'bonus_amount_manager',

            'cost_local',
            'cost_usd',
            'cost_manager',
            'cost',

            'margin_value',
            'margin_local',
            'margin_usd',
            'margin',
            'margin_manager',

            'income_local',
            'income_usd',
            'income',
            'income_value',
            'income_manager',
            'income_type',
        ]);
    }
}
