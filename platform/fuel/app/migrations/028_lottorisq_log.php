<?php

namespace Fuel\Migrations;

class Lottorisq_Log
{
    public function up()
    {
        \DBUtil::create_table(
            'lottorisq_log',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'whitelabel_user_ticket_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'whitelabel_user_ticket_slip_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'date' => ['type' => 'datetime'],
                'type' => ['type' => 'tinyint', 'constraint' => 4],
                'message' => ['type' => 'text'],
                'data' => ['type' => 'mediumtext', 'null' => true],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'lottorisq_log_wid_wid_idfx',
                    'key' => 'whitelabel_id',
                    'reference' => [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'SET NULL'
                ],
                [
                    'constraint' => 'lottorisq_log_wusid_wusid_idfx',
                    'key' => 'whitelabel_user_ticket_slip_id',
                    'reference' => [
                        'table' => 'whitelabel_user_ticket_slip',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'SET NULL'
                ],
                [
                    'constraint' => 'lottorisq_log_wutid_wutid_idfx',
                    'key' => 'whitelabel_user_ticket_id',
                    'reference' => [
                        'table' => 'whitelabel_user_ticket',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'SET NULL'
                ]
            ]
        );

        \DBUtil::create_index('lottorisq_log', 'whitelabel_id', 'lottorisq_log_wid_wid_idfx_idx');
        \DBUtil::create_index('lottorisq_log', 'whitelabel_user_ticket_id', 'lottorisq_log_wutid_wutid_idfx_idx');
        \DBUtil::create_index('lottorisq_log', 'date', 'lottorisq_log_date_idx');
        \DBUtil::create_index('lottorisq_log', 'type', 'lottorisq_log_type_idx');
        \DBUtil::create_index('lottorisq_log', ['whitelabel_id', 'date'], 'lottorisq_log_whitelabel_id_date_idmx');
        \DBUtil::create_index('lottorisq_log', ['type', 'date'], 'lottorisq_log_whitelabel_id_type_date_idmx');
        \DBUtil::create_index('lottorisq_log', 'whitelabel_user_ticket_slip_id', 'lottorisq_log_wusid_wusid_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('lottorisq_log', 'lottorisq_log_wid_wid_idfx');
        \DBUtil::drop_foreign_key('lottorisq_log', 'lottorisq_log_wusid_wusid_idfx');
        \DBUtil::drop_foreign_key('lottorisq_log', 'lottorisq_log_wutid_wutid_idfx');

        \DBUtil::drop_table('lottorisq_log');
    }
}
