<?php

namespace Fuel\Migrations;

class Whitelabel_Transaction
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_transaction',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'token' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'whitelabel_user_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'payment_method_type' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => null],
                'whitelabel_payment_method_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'whitelabel_cc_method_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'payment_currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 2],
                'amount' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => null],
                'amount_usd' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => null],
                'amount_payment' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => 0.00],
                'amount_manager' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => 0.00],
                'income' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => null],
                'income_usd' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => null],
                'income_manager' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => 0.00],
                'cost' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => null],
                'cost_usd' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => null],
                'cost_manager' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => 0.00],
                'payment_cost' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => null],
                'payment_cost_usd' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => null],
                'payment_cost_manager' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => 0.00],
                'margin' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => null],
                'margin_usd' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => null],
                'margin_manager' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => 0.00],
                'date' => ['type' => 'datetime'],
                'date_confirmed' => ['type' => 'datetime', 'null' => true, 'default' => null],
                'status' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'transaction_out_id' => ['type' => 'varchar', 'constraint' => 100, 'null' => true, 'default' => null],
                'additional_data' => ['type' => 'text', 'null' => true],
                'type' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_transaction_currency_id_currency_idfx',
                    'key' => 'currency_id',
                    'reference' => [
                        'table' => 'currency',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_transaction_wcm_id_wcm_idfx',
                    'key' => 'whitelabel_cc_method_id',
                    'reference' => [
                        'table' => 'whitelabel_cc_method',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'SET NULL'
                ],
                [
                    'constraint' => 'whitelabel_transaction_whitelabel_id_whitelabel_idfx',
                    'key' => 'whitelabel_id',
                    'reference' => [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_transaction_whitelabel_user_id_whitelabel_user_idfx',
                    'key' => 'whitelabel_user_id',
                    'reference' => [
                        'table' => 'whitelabel_user',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_transaction_wpm_id_whitelabel_payment_method_idfx',
                    'key' => 'whitelabel_payment_method_id',
                    'reference' => [
                        'table' => 'whitelabel_payment_method',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'SET NULL'
                ],
            ]
        );

        \DBUtil::create_index('whitelabel_transaction', 'whitelabel_id', 'whitelabel_transaction_whitelabel_id_whitelabel_idfx_idx');
        \DBUtil::create_index('whitelabel_transaction', 'whitelabel_user_id', 'whitelabel_transaction_whitelabel_user_id_whitelabel_user_i_idx');
        \DBUtil::create_index('whitelabel_transaction', 'whitelabel_payment_method_id', 'whitelabel_transaction_wpm_id_whitelabel_payment_method_idf_idx');
        \DBUtil::create_index('whitelabel_transaction', 'currency_id', 'whitelabel_transaction_currency_id_currency_idfx_idx');
        \DBUtil::create_index('whitelabel_transaction', ['whitelabel_id', 'whitelabel_user_id', 'date'], 'whitelabel_transaction_w_id_w_user_id_date_idmx');
        \DBUtil::create_index('whitelabel_transaction', ['whitelabel_id', 'whitelabel_user_id', 'status'], 'whitelabel_transaction_w_id_w_user_id_status_idmx');
        \DBUtil::create_index('whitelabel_transaction', ['whitelabel_id', 'whitelabel_user_id', 'id'], 'whitelabel_transaction_w_id_w_user_id_id_idmx');
        \DBUtil::create_index('whitelabel_transaction', ['whitelabel_id', 'whitelabel_user_id', 'amount'], 'whitelabel_transaction_w_id_w_user_id_amount_idmx');
        \DBUtil::create_index('whitelabel_transaction', ['whitelabel_id', 'whitelabel_user_id', 'status', 'payment_method_type'], 'whitelabel_transaction_w_id_w_user_id_status_pmt_idmx');
        \DBUtil::create_index('whitelabel_transaction', ['whitelabel_id', 'type'], 'whitelabel_transaction_w_id_type_idmx');
        \DBUtil::create_index('whitelabel_transaction', ['whitelabel_id', 'type', 'id', 'whitelabel_user_id', 'status', 'payment_method_type', 'whitelabel_payment_method_id'], 'whitelabel_transaction_w_id_type_id_wu_id_status_pm_wpm_idmx');
        \DBUtil::create_index('whitelabel_transaction', ['whitelabel_id', 'type', 'amount'], 'whitelabel_transaction_w_id_type_amount_idmx');
        \DBUtil::create_index('whitelabel_transaction', ['whitelabel_id', 'type', 'date_confirmed'], 'whitelabel_transaction_w_id_type_date_confirmed_idmx');
        \DBUtil::create_index('whitelabel_transaction', ['whitelabel_id', 'transaction_out_id'], 'whitelabel_transaction_w_id_w_transaction_out_id_idmx');
        \DBUtil::create_index('whitelabel_transaction', ['whitelabel_id', 'token'], 'whitelabel_transaction_w_id_token_idx');
        \DBUtil::create_index('whitelabel_transaction', ['whitelabel_id', 'type', 'token'], 'whitelabel_transaction_w_id_type_token_idmx');
        \DBUtil::create_index('whitelabel_transaction', 'whitelabel_cc_method_id', 'whitelabel_transaction_wcm_id_wcm_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_transaction', 'whitelabel_transaction_currency_id_currency_idfx');
        \DBUtil::drop_foreign_key('whitelabel_transaction', 'whitelabel_transaction_wcm_id_wcm_idfx');
        \DBUtil::drop_foreign_key('whitelabel_transaction', 'whitelabel_transaction_whitelabel_id_whitelabel_idfx');
        \DBUtil::drop_foreign_key('whitelabel_transaction', 'whitelabel_transaction_whitelabel_user_id_whitelabel_user_idfx');
        \DBUtil::drop_foreign_key('whitelabel_transaction', 'whitelabel_transaction_wpm_id_whitelabel_payment_method_idfx');

        \DBUtil::drop_table('whitelabel_transaction');
    }
}
