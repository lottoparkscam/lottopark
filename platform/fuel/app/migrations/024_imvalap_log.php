<?php

namespace Fuel\Migrations;

class Imvalap_Log
{
    public function up()
    {
        \DBUtil::create_table(
            'imvalap_log',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'whitelabel_user_ticket_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'imvalap_job_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'date' => ['type' => 'datetime'],
                'type' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'message' => ['type' => 'text']
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'imvalap_log_ijid_ijid_idfx',
                    'key' => 'imvalap_job_id',
                    'reference' => [
                        'table' => 'imvalap_job',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'SET NULL'
                ],
                [
                    'constraint' => 'imvalap_log_wid_wid_idfx',
                    'key' => 'whitelabel_id',
                    'reference' => [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'imvalap_log_wutid_wutid_idfx',
                    'key' => 'whitelabel_user_ticket_id',
                    'reference' => [
                        'table' => 'whitelabel_user_ticket',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ]
            ]
        );

        \DBUtil::create_index('imvalap_log', 'date', 'imvalap_log_date_idx');
        \DBUtil::create_index('imvalap_log', 'type', 'imvalap_log_type_idx');
        \DBUtil::create_index('imvalap_log', ['whitelabel_id', 'date'], 'imvalap_log_whitelabel_id_date_idmx');
        \DBUtil::create_index('imvalap_log', ['whitelabel_id', 'type'], 'imvalap_log_whitelabel_id_type_idx');
        \DBUtil::create_index('imvalap_log', ['whitelabel_id', 'type', 'date'], 'imvalap_log_whitelabel_id_type_date_idmx');
        \DBUtil::create_index('imvalap_log', 'whitelabel_user_ticket_id', 'imvalap_log_wutid_wutid_idfx_idx');
        \DBUtil::create_index('imvalap_log', 'imvalap_job_id', 'imvalap_log_ijid_ijid_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('imvalap_log', 'imvalap_log_ijid_ijid_idfx');
        \DBUtil::drop_foreign_key('imvalap_log', 'imvalap_log_wid_wid_idfx');
        \DBUtil::drop_foreign_key('imvalap_log', 'imvalap_log_wutid_wutid_idfx');

        \DBUtil::drop_table('imvalap_log');
    }
}
