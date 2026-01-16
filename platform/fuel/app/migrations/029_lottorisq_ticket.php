<?php

namespace Fuel\Migrations;

class Lottorisq_Ticket
{
    public function up()
    {
        \DBUtil::create_table(
            'lottorisq_ticket',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_user_ticket_slip_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'lottorisqid' => ['type' => 'varchar', 'constraint' => 60],
                'confirm_data' => ['type' => 'text', 'null' => true],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'lottorisq_ticket_wutsid_wutsid_idfx',
                    'key' => 'whitelabel_user_ticket_slip_id',
                    'reference' => [
                        'table' => 'whitelabel_user_ticket_slip',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ]
            ]
        );

        \DBUtil::create_index('lottorisq_ticket', 'whitelabel_user_ticket_slip_id', 'lottorisq_ticket_wusid_wusid_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('lottorisq_ticket', 'lottorisq_ticket_wutsid_wutsid_idfx');

        \DBUtil::drop_table('lottorisq_ticket');
    }
}
