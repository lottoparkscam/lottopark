<?php

namespace Fuel\Migrations;

class Add_last_purchase_date_to_whitelabel_user
{
    public function up()
    {
        \DBUtil::add_fields('whitelabel_user', [
            'last_purchase_date' => ['type' => 'datetime', 'null' => true, 'after' => 'second_purchase'],
        ]);
    }

    public function down()
    {
        \DBUtil::drop_fields('whitelabel_user', [
            'last_purchase_date'
        ]);
    }
}
