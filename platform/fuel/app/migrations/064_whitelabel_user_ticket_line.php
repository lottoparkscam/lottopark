<?php

namespace Fuel\Migrations;

class Whitelabel_User_Ticket_Line
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_user_ticket_line',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_user_ticket_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'lottery_type_data_id' => ['type' => 'smallint', 'constraint' => 5, 'unsigned' => true, 'null' => true, 'default' => null],
                'whitelabel_user_ticket_slip_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'numbers' => ['type' => 'varchar', 'constraint' => 30],
                'bnumbers' => ['type' => 'varchar', 'constraint' => 30],
                'amount_local' => ['type' => 'decimal', 'constraint' => [9, 2], 'unsigned' => true],
                'amount' => ['type' => 'decimal', 'constraint' => [9, 2], 'unsigned' => true],
                'amount_usd' => ['type' => 'decimal', 'constraint' => [9, 2], 'unsigned' => true],
                'amount_payment' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => 0.00],
                'amount_manager' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => 0.00],
                'status' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'payout' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'prize_local' => ['type' => 'decimal', 'constraint' => [12, 2], 'unsigned' => true, 'null' => true, 'default' => null],
                'prize_usd' => ['type' => 'decimal', 'constraint' => [12, 2], 'unsigned' => true, 'null' => true, 'default' => null],
                'prize' => ['type' => 'decimal', 'constraint' => [12, 2], 'unsigned' => true, 'null' => true, 'default' => null],
                'prize_manager' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => 0.00],
                'prize_net_local' => ['type' => 'decimal', 'constraint' => [12, 2], 'unsigned' => true, 'null' => true, 'default' => null],
                'prize_net_usd' => ['type' => 'decimal', 'constraint' => [12, 2], 'unsigned' => true, 'null' => true, 'default' => null],
                'prize_net' => ['type' => 'decimal', 'constraint' => [12, 2], 'unsigned' => true, 'null' => true, 'default' => null],
                'prize_net_manager' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => 0.00],
                'uncovered_prize_local' => ['type' => 'decimal', 'constraint' => [12, 2], 'unsigned' => true, 'null' => true, 'default' => null],
                'uncovered_prize_usd' => ['type' => 'decimal', 'constraint' => [12, 2], 'unsigned' => true, 'null' => true, 'default' => null],
                'uncovered_prize' => ['type' => 'decimal', 'constraint' => [12, 2], 'unsigned' => true, 'null' => true, 'default' => null],
                'uncovered_prize_manager' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => 0.00],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_user_ticket_line_ltd_id_lottery_type_data_idfx',
                    'key' => 'lottery_type_data_id',
                    'reference' => [
                        'table' => 'lottery_type_data',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'SET NULL'
                ],
                [
                    'constraint' => 'whitelabel_user_ticket_line_wut_id_whitelabel_user_ticket_idfx',
                    'key' => 'whitelabel_user_ticket_id',
                    'reference' => [
                        'table' => 'whitelabel_user_ticket',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_user_ticket_line_wuts_id_wuts_idfx',
                    'key' => 'whitelabel_user_ticket_slip_id',
                    'reference' => [
                        'table' => 'whitelabel_user_ticket_slip',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'SET NULL'
                ],
            ]
        );

        \DBUtil::create_index('whitelabel_user_ticket_line', 'whitelabel_user_ticket_id', 'whitelabel_user_ticket_line_wut_id_whitelabel_user_ticket_i_idx');
        \DBUtil::create_index('whitelabel_user_ticket_line', 'lottery_type_data_id', 'whitelabel_user_ticket_line_ltd_id_lottery_type_data_idfx_idx');
        \DBUtil::create_index('whitelabel_user_ticket_line', ['whitelabel_user_ticket_id', 'payout'], 'whitelabel_user_ticket_line_wut_id_payout_idmx');
        \DBUtil::create_index('whitelabel_user_ticket_line', 'whitelabel_user_ticket_slip_id', 'whitelabel_user_ticket_line_wuts_id_wuts_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_user_ticket_line', 'whitelabel_user_ticket_line_ltd_id_lottery_type_data_idfx');
        \DBUtil::drop_foreign_key('whitelabel_user_ticket_line', 'whitelabel_user_ticket_line_wut_id_whitelabel_user_ticket_idfx');
        \DBUtil::drop_foreign_key('whitelabel_user_ticket_line', 'whitelabel_user_ticket_line_wuts_id_wuts_idfx');

        \DBUtil::drop_table('whitelabel_user_ticket_line');
    }
}
