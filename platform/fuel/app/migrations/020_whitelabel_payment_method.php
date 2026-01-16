<?php

namespace Fuel\Migrations;

class Whitelabel_Payment_Method
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_payment_method',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'payment_method_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'language_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'name' => ['type' => 'varchar', 'constraint' => 100],
                'show' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'data' => ['type' => 'text'],
                'order' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'cost_percent' => ['type' => 'decimal', 'constraint' => [4, 2], 'unsigned' => true, 'default' => 0.00],
                'cost_fixed' => ['type' => 'decimal', 'constraint' => [5, 2], 'unsigned' => true, 'default' => 0.00],
                'cost_currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => null],
                'payment_currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 2]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_payment_method_c_c_id_c_idfx',
                    'key' => 'cost_currency_id',
                    'reference' => [
                        'table' => 'currency',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'SET NULL'
                ],
                [
                    'constraint' => 'whitelabel_payment_method_language_id_language_idfx',
                    'key' => 'language_id',
                    'reference' => [
                        'table' => 'language',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_payment_method_payment_method_id_payment_method_idfx',
                    'key' => 'payment_method_id',
                    'reference' => [
                        'table' => 'payment_method',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_payment_method_whitelabel_id_whitelabel_idfx',
                    'key' => 'whitelabel_id',
                    'reference' => [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
            ]
        );

        \DBUtil::create_index('whitelabel_payment_method', 'whitelabel_id', 'whitelabel_payment_method_whitelabel_id_whitelabel_idfx_idx');
        \DBUtil::create_index('whitelabel_payment_method', 'payment_method_id', 'whitelabel_payment_method_payment_method_id_payment_method__idx');
        \DBUtil::create_index('whitelabel_payment_method', 'language_id', 'whitelabel_payment_method_language_id_language_idfx_idx');
        \DBUtil::create_index('whitelabel_payment_method', ['whitelabel_id', 'language_id', 'order'], 'wpm_w_id_l_id_order_idmx');
        \DBUtil::create_index('whitelabel_payment_method', 'cost_currency_id', 'whitelabel_payment_method_c_c_id_c_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_payment_method', 'whitelabel_payment_method_c_c_id_c_idfx');
        \DBUtil::drop_foreign_key('whitelabel_payment_method', 'whitelabel_payment_method_language_id_language_idfx');
        \DBUtil::drop_foreign_key('whitelabel_payment_method', 'whitelabel_payment_method_payment_method_id_payment_method_idfx');
        \DBUtil::drop_foreign_key('whitelabel_payment_method', 'whitelabel_payment_method_whitelabel_id_whitelabel_idfx');

        \DBUtil::drop_table('whitelabel_payment_method');
    }
}
