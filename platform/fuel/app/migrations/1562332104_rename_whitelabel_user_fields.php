<?php

namespace Fuel\Migrations;

class Rename_Whitelabel_User_Fields
{
    public function up()
    {
        \DBUtil::modify_fields('whitelabel_user', [
            'first_deposit_amount' => ['name' => 'first_deposit_amount_manager', 'constraint' => [9, 2], 'type' => 'decimal', 'unsigned' => true, 'null' => true, 'after' => 'balance'],
            'total_deposit' => ['name' => 'total_deposit_manager', 'constraint' => [9, 2], 'type' => 'decimal', 'unsigned' => true, 'null' => true, 'after' => 'first_deposit_amount_manager'],
            'total_withdrawal' => ['name' => 'total_withdrawal_manager', 'constraint' => [9, 2], 'type' => 'decimal', 'unsigned' => true, 'null' => true, 'after' => 'total_deposit_manager'],
            'total_purchases' => ['name' => 'total_purchases_manager', 'constraint' => [9, 2], 'type' => 'decimal', 'unsigned' => true, 'null' => true, 'after' => 'total_withdrawal_manager'],
            'total_net_income' => ['name' => 'total_net_income_manager', 'constraint' => [9, 2], 'type' => 'decimal', 'unsigned' => true, 'null' => true, 'after' => 'total_purchases_manager'],
            'last_deposit_amount' => ['name' => 'last_deposit_amount_manager', 'constraint' => [9, 2], 'type' => 'decimal', 'unsigned' => true, 'null' => true, 'after' => 'last_deposit_date'],
            'net_winnings' => ['name' => 'net_winnings_manager', 'constraint' => [15, 2], 'type' => 'decimal', 'unsigned' => true, 'null' => true, 'after' => 'last_deposit_amount_manager'],
            'pnl' => ['name' => 'pnl_manager', 'constraint' => [15, 2], 'type' => 'decimal', 'null' => true, 'after' => 'sale_status'],
        ]);
    }

    public function down()
    {
        \DBUtil::modify_fields('whitelabel_user', [
            'first_deposit_amount_manager' => ['name' => 'first_deposit_amount', 'constraint' => [9, 2], 'type' => 'decimal', 'unsigned' => true, 'null' => true, 'after' => 'balance'],
            'total_deposit_manager' => ['name' => 'total_deposit', 'constraint' => [9, 2], 'type' => 'decimal', 'unsigned' => true, 'null' => true, 'after' => 'first_deposit_amount'],
            'total_withdrawal_manager' => ['name' => 'total_withdrawal', 'constraint' => [9, 2], 'type' => 'decimal', 'unsigned' => true, 'null' => true, 'after' => 'total_deposit'],
            'total_purchases_manager' => ['name' => 'total_purchases', 'constraint' => [9, 2], 'type' => 'decimal', 'unsigned' => true, 'null' => true, 'after' => 'total_withdrawal'],
            'total_net_income_manager' => ['name' => 'total_net_income', 'constraint' => [9, 2], 'type' => 'decimal', 'unsigned' => true, 'null' => true, 'after' => 'total_purchases'],
            'last_deposit_amount_manager' => ['name' => 'last_deposit_amount', 'constraint' => [9, 2], 'type' => 'decimal', 'unsigned' => true, 'null' => true, 'after' => 'last_deposit_date'],
            'net_winnings_manager' => ['name' => 'net_winnings', 'constraint' => [15, 2], 'type' => 'decimal', 'unsigned' => true, 'null' => true, 'after' => 'last_deposit_amount'],
            'pnl_manager' => ['name' => 'pnl', 'constraint' => [15, 2], 'type' => 'decimal', 'null' => true, 'after' => 'sale_status'],
        ]);
    }
}
