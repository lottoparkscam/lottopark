<?php

namespace Fuel\Migrations;

class Add_last_purchase_amount_manager_to_whitelabel_user
{
    public function up()
    {
        \DBUtil::add_fields('whitelabel_user', [
            'last_purchase_amount_manager' => ['constraint' => [9, 2], 'type' => 'decimal', 'unsigned' => true, 'null' => true, 'after' => 'total_withdrawal_manager'],
        ]);
    }

    public function down()
    {
        \DBUtil::drop_fields('whitelabel_user', [
            'last_purchase_amount_manager'
        ]);
    }
}
