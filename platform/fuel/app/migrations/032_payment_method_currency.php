<?php

namespace Fuel\Migrations;

class Payment_Method_Currency
{
    public function up()
    {
        \DBUtil::create_table(
            'payment_method_currency',
            [
                'id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_payment_method_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => null],
                'min_purchase' => ['type' => 'decimal', 'constraint' => [15,2], 'null' => true, 'default' => null],
                'additional_data' => ['type' => 'varchar', 'constraint' => 300, 'null' => true, 'default' => null],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'payment_method_currency__fk',
                    'key' => 'currency_id',
                    'reference' => [
                        'table' => 'currency',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'NO ACTION'
                ],
                [
                    'constraint' => 'payment_method_currency_whitelabel_payment_method___fk',
                    'key' => 'whitelabel_payment_method_id',
                    'reference' => [
                        'table' => 'whitelabel_payment_method',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'NO ACTION'
                ]
            ]
        );

        \DBUtil::create_index('payment_method_currency', 'whitelabel_payment_method_id', 'payment_method_currency_whitelabel_payment_method___fk');
        \DBUtil::create_index('payment_method_currency', 'currency_id', 'payment_method_currency__fk');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('payment_method_currency', 'payment_method_currency__fk');
        \DBUtil::drop_foreign_key('payment_method_currency', 'payment_method_currency_whitelabel_payment_method___fk');

        \DBUtil::drop_table('payment_method_currency');
    }
}
