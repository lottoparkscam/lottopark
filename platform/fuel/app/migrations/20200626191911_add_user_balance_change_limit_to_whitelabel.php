<?php

namespace Fuel\Migrations;

class Add_user_balance_change_limit_to_whitelabel
{
    public function up()
    {
        \DBUtil::add_fields('whitelabel', [
            'user_balance_change_limit' => ['type' => 'decimal', 'constraint' => [9,2], 'unsigned' => true, 'default'=> 0, 'after' => 'max_order_count']
        ]);
    }

    public function down()
    {
        \DBUtil::drop_fields('whitelabel', [
            'user_balance_change_limit'
        ]);
    }
}
