<?php

namespace Fuel\Migrations;

class Drop_Transaction_Decreased_Amount
{
    public function up()
    {
        \DBUtil::drop_fields('whitelabel_transaction', 'decreased_amount');
    }

    public function down()
    {
        \DBUtil::add_fields('whitelabel_transaction', [
            'decreased_amount' => ['type' => 'decimal', 'constraint' => [9,2], 'unsigned' => true, 'default' => '0.00'],
        ]);
    }
}
