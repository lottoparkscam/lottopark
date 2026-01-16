<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

class Alter_whitelabel_raffle_ticket_missing_ticket_columns_drop_unused
{
    public function up()
    {
        DBUtil::drop_fields('whitelabel_raffle_ticket', [
            'prize_net_local',
            'prize_net_usd',
            'prize_net',
            'prize_net_manager',
            'prize_jackpot',
            'prize_quickpick',
        ]);

        DBUtil::add_fields('whitelabel_raffle_ticket', [
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

            'bonus_cost_local'   => ['type' => 'decimal', 'constraint' => [9, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'cost_manager'],
            'bonus_cost_usd'     => ['type' => 'decimal', 'constraint' => [9, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'bonus_cost_local'],
            'bonus_cost'         => ['type' => 'decimal', 'constraint' => [9, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'bonus_cost_usd'],
            'bonus_cost_manager' => ['type' => 'decimal', 'constraint' => [15, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'bonus_cost'],

            'margin_value'   => ['type' => 'decimal', 'constraint' => [5, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'bonus_cost_manager'],
            'margin_local'   => ['type' => 'decimal', 'constraint' => [9, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'margin_value'],
            'margin_usd'     => ['type' => 'decimal', 'constraint' => [9, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'margin_local'],
            'margin'         => ['type' => 'decimal', 'constraint' => [9, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'margin_usd'],
            'margin_manager' => ['type' => 'decimal', 'constraint' => [15, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'margin'],

            'income_local' => ['type' => 'decimal', 'constraint' => [9,2], 'default' => 0.00, 'after' => 'margin_manager'],
            'income_usd' => ['type' => 'decimal', 'constraint' => [9,2], 'default' => 0.00, 'after' => 'income_local'],
            'income' => ['type' => 'decimal', 'constraint' => [9,2], 'default' => 0.00, 'after' => 'income_usd'],
            'income_value' => ['type' => 'decimal', 'constraint' => [5,2], 'unsigned' => true, 'default' => 0.00, 'after' => 'income'],
            'income_manager' => ['type' => 'decimal', 'constraint' => [15,2], 'default' => 0.00, 'after' => 'income_value'],
            'income_type' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'after' => 'income_manager'],
        ]);
    }

    public function down()
    {
        DBUtil::add_fields('whitelabel_raffle_ticket', [
            'prize_net_local'   => ['type' => 'decimal', 'constraint' => [12, 2], 'unsigned' => true, 'default' => 0.00],
            'prize_net_usd'     => ['type' => 'decimal', 'constraint' => [12, 2], 'unsigned' => true, 'default' => 0.00],
            'prize_net'         => ['type' => 'decimal', 'constraint' => [12, 2], 'unsigned' => true, 'default' => 0.00],
            'prize_net_manager' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => 0.00],
            'prize_jackpot'     => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 0.00],
            'prize_quickpick'   => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 0.00],
        ]);

        DBUtil::drop_fields('whitelabel_raffle_ticket', [
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

            'bonus_cost_local',
            'bonus_cost_usd',
            'bonus_cost_manager',
            'bonus_cost',

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
