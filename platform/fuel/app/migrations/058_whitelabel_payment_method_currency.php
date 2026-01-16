<?php

namespace Fuel\Migrations;

class Whitelabel_Payment_Method_Currency
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_payment_method_currency',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_payment_method_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 2],
                'is_zero_decimal' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => 0],
                'min_purchase' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => 0.00],
                'is_default' => ['type' => 'tinyint', 'constraint' => 1, 'default' => 0],
                'is_enabled' => ['type' => 'tinyint', 'constraint' => 1, 'default' => 1],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_payment_method_currency_c_id_c_idfx_idx',
                    'key' => 'currency_id',
                    'reference' => [
                        'table' => 'currency',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_payment_method_currency_wpm_id_wpm_idfx_idx',
                    'key' => 'whitelabel_payment_method_id',
                    'reference' => [
                        'table' => 'whitelabel_payment_method',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
            ]
        );

        \DBUtil::create_index('whitelabel_payment_method_currency', 'whitelabel_payment_method_id', 'whitelabel_payment_method_currency_wpm_id_wpm_idfx_idx');
        \DBUtil::create_index('whitelabel_payment_method_currency', 'currency_id', 'whitelabel_payment_method_currency_c_id_c_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_payment_method_currency', 'whitelabel_payment_method_currency_c_id_c_idfx_idx');
        \DBUtil::drop_foreign_key('whitelabel_payment_method_currency', 'whitelabel_payment_method_currency_wpm_id_wpm_idfx_idx');

        \DBUtil::drop_table('whitelabel_payment_method_currency');
    }
}
