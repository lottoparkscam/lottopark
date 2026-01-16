<?php

namespace Fuel\Migrations;

class Update_Whitelabel_User_Pnl
{
    public function up()
    {
        \DBUtil::modify_fields('whitelabel_user', [
            'net_winnings' => ['constraint' => [15, 2], 'type' => 'decimal', 'unsigned' => true, 'null' => true, 'after' => 'last_deposit_amount'],
            'pnl' => ['constraint' => [15, 2], 'type' => 'decimal', 'null' => true, 'after' => 'sale_status'],
        ]);
    }
    
    public function down()
    {
        \DBUtil::modify_fields('whitelabel_user', [
            'net_winnings' => ['constraint' => [9, 2], 'type' => 'decimal', 'unsigned' => true, 'null' => true, 'after' => 'last_deposit_amount'],
            'pnl' => ['constraint' => [9, 2], 'type' => 'decimal', 'null' => true, 'after' => 'sale_status'],
        ]);
    }
}
