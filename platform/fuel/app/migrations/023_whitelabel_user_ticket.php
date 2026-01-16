<?php

namespace Fuel\Migrations;

class Whitelabel_User_Ticket
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_user_ticket',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'token' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'whitelabel_transaction_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'whitelabel_user_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'lottery_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'lottery_type_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'lottery_provider_id' => ['type' => 'smallint', 'constraint' => 5, 'unsigned' => true, 'null' => true, 'default' => null],
                'valid_to_draw' => ['type' => 'date'],
                'draw_date' => ['type' => 'date', 'null' => true, 'default' => null],
                'amount_local' => ['type' => 'decimal', 'constraint' => [9,2], 'unsigned' => true],
                'amount' => ['type' => 'decimal', 'constraint' => [9,2], 'unsigned' => true],
                'amount_usd' => ['type' => 'decimal', 'constraint' => [9,2], 'unsigned' => true],
                'amount_payment' => ['type' => 'decimal', 'constraint' => [15,2], 'null' => true, 'default' => 0.00],
                'amount_manager' => ['type' => 'decimal', 'constraint' => [15,2], 'null' => true, 'default' => 0.00],
                'date' => ['type' => 'datetime'],
                'date_processed' => ['type' => 'datetime', 'null' => true, 'default' => null],
                'status' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'paid' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'payout' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'prize_local' => ['type' => 'decimal', 'constraint' => [12,2], 'unsigned' => true, 'null' => true, 'default' => null],
                'prize_usd' => ['type' => 'decimal', 'constraint' => [12,2], 'unsigned' => true, 'null' => true, 'default' => null],
                'prize' => ['type' => 'decimal', 'constraint' => [12,2], 'unsigned' => true, 'null' => true, 'default' => null],
                'prize_manager' => ['type' => 'decimal', 'constraint' => [15,2], 'null' => true, 'default' => 0.00],
                'prize_net_local' => ['type' => 'decimal', 'constraint' => [12,2], 'unsigned' => true, 'null' => true, 'default' => null],
                'prize_net_usd' => ['type' => 'decimal', 'constraint' => [12,2], 'unsigned' => true, 'null' => true, 'default' => null],
                'prize_net' => ['type' => 'decimal', 'constraint' => [12,2], 'unsigned' => true, 'null' => true, 'default' => null],
                'prize_net_manager' => ['type' => 'decimal', 'constraint' => [15,2], 'null' => true, 'default' => 0.00],
                'prize_jackpot' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => 0],
                'prize_quickpick' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => 0],
                'model' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'null' => true, 'default' => 0],
                'cost_local' => ['type' => 'decimal', 'constraint' => [9,2], 'unsigned' => true],
                'cost_usd' => ['type' => 'decimal', 'constraint' => [9,2], 'unsigned' => true],
                'cost' => ['type' => 'decimal', 'constraint' => [9,2], 'unsigned' => true],
                'cost_manager' => ['type' => 'decimal', 'constraint' => [15,2], 'null' => true, 'default' => 0.00],
                'income_local' => ['type' => 'decimal', 'constraint' => [9,2]],
                'income_usd' => ['type' => 'decimal', 'constraint' => [9,2]],
                'income' => ['type' => 'decimal', 'constraint' => [9,2]],
                'income_value' => ['type' => 'decimal', 'constraint' => [5,2], 'unsigned' => true],
                'income_manager' => ['type' => 'decimal', 'constraint' => [15,2], 'null' => true, 'default' => 0.00],
                'income_type' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true],
                'is_insured' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true],
                'tier' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'margin_value' => ['type' => 'decimal', 'constraint' => [5,2], 'unsigned' => true],
                'margin_local' => ['type' => 'decimal', 'constraint' => [9,2], 'unsigned' => true],
                'margin_usd' => ['type' => 'decimal', 'constraint' => [9,2], 'unsigned' => true],
                'margin' => ['type' => 'decimal', 'constraint' => [9,2], 'unsigned' => true],
                'margin_manager' => ['type' => 'decimal', 'constraint' => [15,2], 'null' => true, 'default' => 0.00],
                'ip' => ['type' => 'varchar', 'constraint' => 45],
                'line_count' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 0.00],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_user_ticket_currency_id_currency_idfx',
                    'key' => 'currency_id',
                    'reference' => [
                        'table' => 'currency',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_user_ticket_lottery_id_lottery_idfx',
                    'key' => 'lottery_id',
                    'reference' => [
                        'table' => 'lottery',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_user_ticket_lottery_provider_id_lottery_provider_idfx',
                    'key' => 'lottery_provider_id',
                    'reference' => [
                        'table' => 'lottery_provider',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'SET NULL'
                ],
                [
                    'constraint' => 'whitelabel_user_ticket_lottery_type_id_lottery_type_idfx',
                    'key' => 'lottery_type_id',
                    'reference' => [
                        'table' => 'lottery_type',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_user_ticket_whitelabel_id_whitelabel_idfx',
                    'key' => 'whitelabel_id',
                    'reference' => [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_user_ticket_whitelabel_user_id_whitelabel_user_idfx',
                    'key' => 'whitelabel_user_id',
                    'reference' => [
                        'table' => 'whitelabel_user',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_user_ticket_wt_id_whitelabel_transaction_idfx',
                    'key' => 'whitelabel_transaction_id',
                    'reference' => [
                        'table' => 'whitelabel_transaction',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
            ]
        );

        \DBUtil::create_index('whitelabel_user_ticket', 'whitelabel_id', 'whitelabel_user_ticket_whitelabel_id_whitelabel_idfx_idx');
        \DBUtil::create_index('whitelabel_user_ticket', 'whitelabel_user_id', 'whitelabel_user_ticket_whitelabel_user_id_whitelabel_user_i_idx');
        \DBUtil::create_index('whitelabel_user_ticket', 'lottery_id', 'whitelabel_user_ticket_lottery_id_lottery_idfx_idx');
        \DBUtil::create_index('whitelabel_user_ticket', 'lottery_type_id', 'whitelabel_user_ticket_lottery_type_id_lottery_type_idfx_idx');
        \DBUtil::create_index('whitelabel_user_ticket', 'currency_id', 'whitelabel_user_ticket_currency_id_currency_idfx_idx');
        \DBUtil::create_index('whitelabel_user_ticket', ['whitelabel_id', 'whitelabel_user_id', 'id'], 'whitelabel_user_ticket_w_id_wu_id_idmx');
        \DBUtil::create_index('whitelabel_user_ticket', 'whitelabel_transaction_id', 'whitelabel_user_ticket_whitelabel_transaction_id_whitelabel_idx');
        \DBUtil::create_index('whitelabel_user_ticket', ['whitelabel_id', 'whitelabel_user_id', 'paid', 'draw_date'], 'whitelabel_user_ticket_w_id_wu_id_paid_draw_date_local_idmx');
        \DBUtil::create_index('whitelabel_user_ticket', ['whitelabel_id', 'whitelabel_user_id', 'paid', 'id'], 'whitelabel_user_ticket_w_id_wu_id_paid_id_idmx');
        \DBUtil::create_index('whitelabel_user_ticket', ['whitelabel_id', 'whitelabel_user_id', 'paid', 'amount'], 'whitelabel_user_ticket_w_id_wu_id_paid_amount_idmx');
        \DBUtil::create_index('whitelabel_user_ticket', ['whitelabel_id', 'whitelabel_user_id', 'paid', 'prize_local'], 'whitelabel_user_ticket_w_id_wu_id_paid_prize_local_idmx');
        \DBUtil::create_index('whitelabel_user_ticket', ['whitelabel_id', 'paid', 'id', 'whitelabel_transaction_id', 'whitelabel_user_id', 'status', 'payout', 'lottery_id', 'draw_date'], 'whitelabel_user_ticket_w_id_paid_id_tid_uid_s_p_lid_draw_idmx');
        \DBUtil::create_index('whitelabel_user_ticket', ['whitelabel_id', 'paid', 'id'], 'whitelabel_user_ticket_w_id_paid_id_idmx');
        \DBUtil::create_index('whitelabel_user_ticket', ['whitelabel_id', 'paid', 'amount'], 'whitelabel_user_ticket_w_id_paid_amount_idmx');
        \DBUtil::create_index('whitelabel_user_ticket', ['whitelabel_id', 'paid', 'draw_date'], 'whitelabel_user_ticket_w_id_paid_draw_date_idmx');
        \DBUtil::create_index('whitelabel_user_ticket', ['whitelabel_id', 'paid', 'prize'], 'whitelabel_user_ticket_w_id_paid_prize_idmx');
        \DBUtil::create_index('whitelabel_user_ticket', ['whitelabel_transaction_id', 'date_processed'], 'whitelabel_user_ticket_wt_id_date_processed');
        \DBUtil::create_index('whitelabel_user_ticket', ['lottery_id', 'status', 'draw_date'], 'whitelabel_user_ticket_l_id_status_draw_date_idmx');
        \DBUtil::create_index('whitelabel_user_ticket', ['lottery_id', 'draw_date'], 'whitelabel_user_ticket_l_id_draw_date_idmx');
        \DBUtil::create_index('whitelabel_user_ticket', ['lottery_id', 'valid_to_draw'], 'whitelabel_user_ticket_l_id_valid_to_draw_idmx');
        \DBUtil::create_index('whitelabel_user_ticket', 'lottery_provider_id', 'whitelabel_user_ticket_lottery_provider_id_lottery_provider_idx');
        \DBUtil::create_index('whitelabel_user_ticket', ['whitelabel_id', 'token'], 'whitelabel_user_ticket_w_id_token_idx');
        \DBUtil::create_index('whitelabel_user_ticket', ['paid', 'status', 'model'], 'whitelabel_user_ticket_w_id_paid_status_model_idx');




    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_user_ticket', 'whitelabel_user_ticket_currency_id_currency_idfx');
        \DBUtil::drop_foreign_key('whitelabel_user_ticket', 'whitelabel_user_ticket_lottery_id_lottery_idfx');
        \DBUtil::drop_foreign_key('whitelabel_user_ticket', 'whitelabel_user_ticket_lottery_provider_id_lottery_provider_idfx');
        \DBUtil::drop_foreign_key('whitelabel_user_ticket', 'whitelabel_user_ticket_lottery_type_id_lottery_type_idfx');
        \DBUtil::drop_foreign_key('whitelabel_user_ticket', 'whitelabel_user_ticket_whitelabel_id_whitelabel_idfx');
        \DBUtil::drop_foreign_key('whitelabel_user_ticket', 'whitelabel_user_ticket_whitelabel_user_id_whitelabel_user_idfx');
        \DBUtil::drop_foreign_key('whitelabel_user_ticket', 'whitelabel_user_ticket_wt_id_whitelabel_transaction_idfx');

        \DBUtil::drop_table('whitelabel_user_ticket');
    }
}
