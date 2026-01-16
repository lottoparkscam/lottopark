<?php

namespace Fuel\Migrations;

class Add_bonus_balance_to_whitelabel_user
{
    public function up()
    {
        \DBUtil::add_fields('whitelabel_user', [
            'bonus_balance' => ['type' => 'decimal', 'constraint' => [9,2], 'unsigned' => true, 'default' => 0.00, 'after' => 'balance']
        ]);
    }

    public function down()
    {
        \DBUtil::drop_fields('whitelabel_user', [
            'bonus_balance'
        ]);
    }
}
