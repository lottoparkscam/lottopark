<?php

namespace Fuel\Migrations;

class Update_Transaction_Decreased_Amount
{
    public function up()
    {
        \DBUtil::add_fields('whitelabel_transaction', [
            'decreased_amount' => ['type' => 'decimal', 'constraint' => [9,2], 'unsigned' => true, 'default' => '0.00'],
        ]);
    }

    public function down()
    {
        \DBUtil::drop_fields('whitelabel_transaction', 'decreased_amount');
    }
}
