<?php

namespace Fuel\Migrations;

class Update_Whitelabel_User_Income_Manager
{
    public function up()
    {
        \DBUtil::modify_fields('whitelabel_user', [
            'total_net_income_manager' => ['constraint' => [9, 2], 'type' => 'decimal', 'null' => true, 'after' => 'total_purchases_manager'],
        ]);
    }
    
    public function down()
    {
        \DBUtil::modify_fields('whitelabel_user', [
            'total_net_income_manager' => ['constraint' => [9, 2], 'type' => 'decimal', 'unsigned' => true, 'null' => true, 'after' => 'total_purchases_manager'],
        ]);
    }
}
