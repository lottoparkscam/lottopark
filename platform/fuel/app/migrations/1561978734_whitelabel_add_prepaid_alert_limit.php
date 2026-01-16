<?php

namespace Fuel\Migrations;

class Whitelabel_Add_Prepaid_Alert_Limit
{
    public function up()
    {
        \DBUtil::add_fields('whitelabel', [
            'prepaid_alert_limit' => [
                'type' => 'decimal',
                'constraint' => [10,2],
                'default' => 0.00,
                'after' => 'prepaid'
            ],
        ]);
    }
    
    public function down()
    {
        \DBUtil::drop_fields('whitelabel', 'prepaid_alert_limit');
    }
}
