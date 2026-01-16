<?php

namespace Fuel\Migrations;

class Withdrawal_Request
{
    public function up()
    {
        \DBUtil::create_table(
            'withdrawal_request',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'token' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'whitelabel_user_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'withdrawal_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'amount' => ['type' => 'decimal', 'constraint' => [9,2], 'unsigned' => true],
                'amount_usd' => ['type' => 'decimal', 'constraint' => [10, 2], 'unsigned' => true],
                'amount_manager' => ['type' => 'decimal', 'constraint' => [15, 2], 'unsigned' => true, 'default' => '0.00'],
                'date' => ['type' => 'datetime'],
                'date_confirmed' => ['type' => 'datetime', 'null' => true, 'default' => null],
                'status' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'data' => ['type' => 'text']
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'withdrawal_request_currency_id_currency_idfx',
                    'key' => 'currency_id',
                    'reference' => [
                        'table' => 'currency',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'withdrawal_request_whitelabel_id_whitelabel_idfx',
                    'key' => 'whitelabel_id',
                    'reference' => [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'withdrawal_request_whitelabel_user_id_whitelabel_user_idfx',
                    'key' => 'whitelabel_user_id',
                    'reference' => [
                        'table' => 'whitelabel_user',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'withdrawal_request_withdrawal_id_withdrawal_idfx',
                    'key' => 'withdrawal_id',
                    'reference' => [
                        'table' => 'withdrawal',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ]
            ]
        );

        \DBUtil::create_index('withdrawal_request', 'whitelabel_id', 'withdrawal_request_whitelabel_id_whitelabel_idfx_idx');
        \DBUtil::create_index('withdrawal_request', 'whitelabel_user_id', 'withdrawal_request_whitelabel_user_id_whitelabel_user_idfx_idx');
        \DBUtil::create_index('withdrawal_request', 'withdrawal_id', 'withdrawal_request_withdrawal_id_withdrawal_idfx_idx');
        \DBUtil::create_index('withdrawal_request', 'currency_id', 'withdrawal_request_currency_id_currency_idfx_idx');
        \DBUtil::create_index('withdrawal_request', ['whitelabel_id', 'whitelabel_user_id', 'withdrawal_id', 'status'], 'withdrawal_request_wid_wuid_withdrawal_id_status_idmx');
        \DBUtil::create_index('withdrawal_request', ['whitelabel_id', 'whitelabel_user_id', 'token'], 'withdrawal_request_whitelabel_id_whitelabel_user_id_token_idmx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('withdrawal_request', 'withdrawal_request_currency_id_currency_idfx');
        \DBUtil::drop_foreign_key('withdrawal_request', 'withdrawal_request_whitelabel_id_whitelabel_idfx');
        \DBUtil::drop_foreign_key('withdrawal_request', 'withdrawal_request_whitelabel_user_id_whitelabel_user_idfx');
        \DBUtil::drop_foreign_key('withdrawal_request', 'withdrawal_request_withdrawal_id_withdrawal_idfx');

        \DBUtil::drop_table('withdrawal_request');
    }
}
