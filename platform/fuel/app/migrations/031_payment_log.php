<?php

namespace Fuel\Migrations;

class Payment_Log
{
    public function up()
    {
        \DBUtil::create_table(
            'payment_log',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'payment_method_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'cc_method' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => null],
                'whitelabel_transaction_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'payment_method_type' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => null],
                'date' => ['type' => 'datetime'],
                'type' => ['type' => 'tinyint', 'constraint' => 4, 'unsigned' => true],
                'message' => ['type' => 'text'],
                'data' => ['type' => 'mediumtext', 'null' => true],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'payment_log_pm_id_pm_idfx',
                    'key' => 'payment_method_id',
                    'reference' => [
                        'table' => 'payment_method',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'payment_log_w_id_widfx',
                    'key' => 'whitelabel_id',
                    'reference' => [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'payment_log_wt_id_wt_idfx',
                    'key' => 'whitelabel_transaction_id',
                    'reference' => [
                        'table' => 'whitelabel_transaction',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ]
            ]
        );

        \DBUtil::create_index('payment_log', 'whitelabel_id', 'payment_log_w_id_widfx_idx');
        \DBUtil::create_index('payment_log', 'payment_method_id', 'payment_log_pm_id_pm_idfx_idx');
        \DBUtil::create_index('payment_log', 'whitelabel_transaction_id', 'payment_log_wt_id_wt_idfx_idx');
        \DBUtil::create_index('payment_log', 'type', 'payment_log_type_idx');
        \DBUtil::create_index('payment_log', 'date', 'payment_log_date_idx');
        \DBUtil::create_index('payment_log', ['type', 'date'], 'payment_log_type_date_idmx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('payment_log', 'payment_log_pm_id_pm_idfx');
        \DBUtil::drop_foreign_key('payment_log', 'payment_log_w_id_widfx');
        \DBUtil::drop_foreign_key('payment_log', 'payment_log_wt_id_wt_idfx');

        \DBUtil::drop_table('payment_log');
    }
}
