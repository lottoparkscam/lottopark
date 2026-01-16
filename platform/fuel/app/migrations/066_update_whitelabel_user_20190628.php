<?php

namespace Fuel\Migrations;

class Update_Whitelabel_User_20190628
{
    public function up()
    {
        \DBUtil::add_fields('whitelabel_user', [
            'system_type' => ['constraint' => 100, 'type' => 'varchar', 'null' => true, 'after' => 'last_active'],
            'browser_type' => ['constraint' => 100, 'type' => 'varchar', 'null' => true, 'after' => 'system_type'],

            'first_deposit_amount' => ['constraint' => [9, 2], 'type' => 'decimal', 'unsigned' => true, 'null' => true, 'after' => 'balance'],
            'total_deposit' => ['constraint' => [9, 2], 'type' => 'decimal', 'unsigned' => true, 'null' => true, 'after' => 'first_deposit_amount'],
            'total_withdrawal' => ['constraint' => [9, 2], 'type' => 'decimal', 'unsigned' => true, 'null' => true, 'after' => 'total_deposit'],
            'total_purchases' => ['constraint' => [9, 2], 'type' => 'decimal', 'unsigned' => true, 'null' => true, 'after' => 'total_withdrawal'],
            'total_net_income' => ['constraint' => [9, 2], 'type' => 'decimal', 'unsigned' => true, 'null' => true, 'after' => 'total_purchases'],
            'last_deposit_date' => ['type' => 'datetime', 'null' => true, 'after' => 'total_net_income'],
            'last_deposit_amount' => ['constraint' => [9, 2], 'type' => 'decimal', 'unsigned' => true, 'null' => true, 'after' => 'last_deposit_date'],
            'net_winnings' => ['constraint' => [9, 2], 'type' => 'decimal', 'unsigned' => true, 'null' => true, 'after' => 'last_deposit_amount'],
            'sale_status' => ['constraint' => 1, 'type' => 'TINYINT', 'default' => 0, 'after' => 'net_winnings'],
            'pnl' => ['constraint' => [9, 2], 'type' => 'decimal', 'null' => true, 'after' => 'sale_status'],
        ]);
    }

    public function down()
    {
        \DBUtil::drop_fields('whitelabel_user', 'system_type');
        \DBUtil::drop_fields('whitelabel_user', 'browser_type');

        \DBUtil::drop_fields('whitelabel_user', 'first_deposit_amount');
        \DBUtil::drop_fields('whitelabel_user', 'total_deposit');
        \DBUtil::drop_fields('whitelabel_user', 'total_withdrawal');
        \DBUtil::drop_fields('whitelabel_user', 'total_purchases');
        \DBUtil::drop_fields('whitelabel_user', 'total_net_income');
        \DBUtil::drop_fields('whitelabel_user', 'last_deposit_date');
        \DBUtil::drop_fields('whitelabel_user', 'last_deposit_amount');
        \DBUtil::drop_fields('whitelabel_user', 'net_winnings');
        \DBUtil::drop_fields('whitelabel_user', 'sale_status');
        \DBUtil::drop_fields('whitelabel_user', 'pnl');
    }
}
