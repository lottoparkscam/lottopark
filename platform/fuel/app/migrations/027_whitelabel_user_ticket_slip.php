<?php

namespace Fuel\Migrations;

class Whitelabel_User_Ticket_Slip
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_user_ticket_slip',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_user_ticket_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'additional_data' => ['type' => 'varchar', 'constraint' => 300, 'null' => true],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_user_ticket_slip_wut_id_wut_idfx',
                    'key' => 'whitelabel_user_ticket_id',
                    'reference' => [
                        'table' => 'whitelabel_user_ticket',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
            ]
        );

        \DBUtil::create_index('whitelabel_user_ticket_slip', 'whitelabel_user_ticket_id', 'whitelabel_user_ticket_slip_wut_id_wut_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_user_ticket_slip', 'whitelabel_user_ticket_slip_wut_id_wut_idfx');
        \DBUtil::drop_table('whitelabel_user_ticket_slip');
    }
}
