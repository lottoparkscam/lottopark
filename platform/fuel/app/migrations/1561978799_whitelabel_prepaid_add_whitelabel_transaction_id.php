<?php

namespace Fuel\Migrations;

class Whitelabel_Prepaid_Add_Whitelabel_Transaction_Id
{
    public function up()
    {
        \DBUtil::add_fields('whitelabel_prepaid', [
            'whitelabel_transaction_id' => [
                'type' => 'int',
                'constraint' => 10,
                'unsigned' => true,
                'null' => true,
                'default' => null
            ],
        ]);
    }
    
    public function down()
    {
        \DBUtil::drop_fields('whitelabel_prepaid', 'whitelabel_transaction_id');
    }
}
