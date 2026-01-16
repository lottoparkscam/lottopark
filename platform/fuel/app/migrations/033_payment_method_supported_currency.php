<?php

namespace Fuel\Migrations;

class Payment_Method_Supported_Currency
{
    public function up()
    {
        \DBUtil::create_table(
            'payment_method_supported_currency',
            [
                'id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'auto_increment' => true],
                'payment_method_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'code' => ['type' => 'varchar', 'constraint' => 3],
                'iso_code' => ['type' => 'varchar', 'constraint' => 3, 'null' => true, 'default' => null],
                'is_zero_decimal' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => 0]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'payment_method_s_curr_p_method_id_payment_method_idfx',
                    'key' => 'payment_method_id',
                    'reference' => [
                        'table' => 'payment_method',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ]
            ]
        );

        \DBUtil::create_index('payment_method_supported_currency', 'payment_method_id', 'payment_method_s_curr_p_method_id_payment_method_idfx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('payment_method_supported_currency', 'payment_method_s_curr_p_method_id_payment_method_idfx');

        \DBUtil::drop_table('payment_method_supported_currency');
    }
}
