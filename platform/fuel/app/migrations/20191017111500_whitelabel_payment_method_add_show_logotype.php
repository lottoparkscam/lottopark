<?php

namespace Fuel\Migrations;

class Whitelabel_Payment_Method_Add_Show_Logotype
{
    public function up()
    {
        \DBUtil::add_fields(
            'whitelabel_payment_method',
            [
                'show_payment_logotype' => [
                    'type' => 'tinyint',
                    'constraint' => 3,
                    'unsigned' => true,
                    'default' => 1,
                ],
            ]
        );
    }

    public function down()
    {
        \DBUtil::drop_fields('whitelabel_payment_method', 'show_payment_logotype');
    }
}
