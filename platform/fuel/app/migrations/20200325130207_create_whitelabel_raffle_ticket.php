<?php

namespace Fuel\Migrations;

class Create_whitelabel_raffle_ticket
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_raffle_ticket',
            [
                'id' => ['type' => 'int', 'constraint' => 20, 'auto_increment' => true, 'unsigned' => true],
                'whitelabel_id' => ['type' => 'int', 'unsigned' => true],
                'whitelabel_user_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'whitelabel_transaction_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'raffle_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'raffle_rule_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'raffle_draw_id' => ['type' => 'int', 'unsigned' => true, 'null' => true],
                'currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'uuid' => ['type' => 'char', 'constraint' => 36, 'null' => true],
                'token' => ['type' => 'varchar', 'constraint' => 255],
                'draw_date' => ['type' => 'datetime', 'null' => true],
                'status' => ['type' => 'tinyint', 'constraint' => 1],
                'amount' => ['type' => 'decimal', 'constraint' => [5,2], 'unsigned' => true],
                'prize' => ['type' => 'decimal', 'constraint' => [13,2], 'unsigned' => false],
                'ip' => ['type' => 'varchar', 'constraint' => 45],
                'ip_country_code' => ['type' => 'varchar', 'constraint' => 2, 'null' => true],
                'is_paid_out' => ['type' => 'tinyint', 'constraint' => 1],
                'created_at' => ['type' => 'timestamp', 'null'=> true],
                'updated_at' => ['type' => 'timestamp', 'null'=> true]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_raffle_ticket_whitelabel_id_idfx',
                    'key' => 'whitelabel_id',
                    'reference' => [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ]
                ],
                [
                    'constraint' => 'whitelabel_raffle_ticket_raffle_id_idfx',
                    'key' => 'raffle_id',
                    'reference' => [
                        'table' => 'raffle',
                        'column' => 'id'
                    ]
                ],
                [
                    'constraint' => 'whitelabel_raffle_ticket_raffle_rule_id_idfx',
                    'key' => 'raffle_rule_id',
                    'reference' => [
                        'table' => 'raffle_rule',
                        'column' => 'id'
                    ]
                ],
                [
                    'constraint' => 'whitelabel_raffle_ticket_raffle_draw_id_idfx',
                    'key' => 'raffle_draw_id',
                    'reference' => [
                        'table' => 'raffle_draw',
                        'column' => 'id'
                    ]
                ],
                [
                    'constraint' => 'whitelabel_raffle_ticket_currency_id_idfx',
                    'key' => 'currency_id',
                    'reference' => [
                        'table' => 'currency',
                        'column' => 'id'
                    ]
                ],
                [
                    'constraint' => 'whitelabel_raffle_ticket_whitelabel_user_id_idfx',
                    'key' => 'whitelabel_user_id',
                    'reference' => [
                        'table' => 'whitelabel_user',
                        'column' => 'id'
                    ]
                ],
                [
                    'constraint' => 'whitelabel_raffle_ticket_whitelabel_transaction_id_idfx',
                    'key' => 'whitelabel_transaction_id',
                    'reference' => [
                        'table' => 'whitelabel_transaction',
                        'column' => 'id'
                    ]
                ]
            ]
        );

        \DBUtil::create_index('whitelabel_raffle_ticket', 'whitelabel_id', 'whitelabel_raffle_ticket_whitelabel_id_idfx_idx');
        \DBUtil::create_index('whitelabel_raffle_ticket', 'whitelabel_user_id', 'whitelabel_raffle_whitelabel_user_id_idfx_idx');
        \DBUtil::create_index('whitelabel_raffle_ticket', 'raffle_id', 'whitelabel_raffle_ticket_raffle_id_idfx_idx');
        \DBUtil::create_index('whitelabel_raffle_ticket', 'raffle_rule_id', 'whitelabel_raffle_ticket_raffle_rule_id_idfx_idx');
        \DBUtil::create_index('whitelabel_raffle_ticket', 'raffle_draw_id', 'whitelabel_raffle_ticket_raffle_draw_id_idfx_idx');
        \DBUtil::create_index('whitelabel_raffle_ticket', 'currency_id', 'whitelabel_raffle_ticket_currency_id_idfx_idx');
        \DBUtil::create_index('whitelabel_raffle_ticket', 'whitelabel_transaction_id', 'whitelabel_raffle_ticket_whitelabel_transaction_id_idfx_idx');
        \DBUtil::create_index('whitelabel_raffle_ticket', 'uuid', 'whitelabel_raffle_ticket_uuid_idx');
        \DBUtil::create_index('whitelabel_raffle_ticket', 'token', 'whitelabel_raffle_ticket_token_idx');
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_raffle_ticket', 'whitelabel_raffle_ticket_whitelabel_id_idfx');
        \DBUtil::drop_foreign_key('whitelabel_raffle_ticket', 'whitelabel_raffle_ticket_raffle_id_idfx');
        \DBUtil::drop_foreign_key('whitelabel_raffle_ticket', 'whitelabel_raffle_ticket_raffle_rule_id_idfx');
        \DBUtil::drop_foreign_key('whitelabel_raffle_ticket', 'whitelabel_raffle_ticket_raffle_draw_id_idfx');
        \DBUtil::drop_foreign_key('whitelabel_raffle_ticket', 'whitelabel_raffle_ticket_currency_id_idfx');
        \DBUtil::drop_foreign_key('whitelabel_raffle_ticket', 'whitelabel_raffle_ticket_whitelabel_user_id_idfx');
        \DBUtil::drop_foreign_key('whitelabel_raffle_ticket', 'whitelabel_raffle_ticket_whitelabel_transaction_id_idfx');

        \DBUtil::drop_table('whitelabel_raffle_ticket');
    }
}
