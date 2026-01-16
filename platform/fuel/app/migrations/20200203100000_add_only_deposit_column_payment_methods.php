<?php

namespace Fuel\Migrations;

class Add_Only_Deposit_Column_Payment_Methods
{
    public function up()
    {
        \DBUtil::add_fields('whitelabel_payment_method', [
            'only_deposit' => ['type' => 'tinyint', 'constraint' => 1, 'default' => 0],
        ]);
    }

    public function down()
    {
        \DBUtil::drop_fields('whitelabel_payment_method', 'only_deposit');
    }
}
