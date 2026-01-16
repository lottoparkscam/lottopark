<?php

namespace Fuel\Migrations;

class Create_whitelabel_raffle_ticket_line
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_raffle_ticket_line',
            [
                'id' => ['type' => 'int', 'constraint' => 20, 'auto_increment' => true, 'unsigned' => true],
                'whitelabel_id' => ['type' => 'int', 'unsigned' => true],
                'whitelabel_raffle_ticket_id' => ['type' => 'int', 'constraint' => 20, 'unsigned' => true],
                'raffle_prize_id' => ['type' => 'int', 'unsigned' => true, 'null' => true],
                'raffle_draw_id' => ['type' => 'int', 'unsigned' => true, 'null' => true],
                'number' => ['type' => 'int', 'unsigned' => true],
                'status' => ['type' => 'tinyint', 'constraint' => 1],
                'created_at' => ['type' => 'timestamp', 'null' => true],
                'updated_at' => ['type' => 'timestamp', 'null' => true]
            ],
            ['id'],
            true,
            false,
            null,
            [
                [
                    'constraint' => 'whitelabel_raffle_ticket_line_whitelabel_id_idfx',
                    'key' => 'whitelabel_id',
                    'reference' => [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ]
                ],
                [
                    'constraint' => 'whitelabel_raffle_ticket_line_wl_raffle_ticket_id_idfx',
                    'key' => 'whitelabel_raffle_ticket_id',
                    'reference' => [
                        'table' => 'whitelabel_raffle_ticket',
                        'column' => 'id'
                    ]
                ],
                [
                    'constraint' => 'whitelabel_raffle_ticket_line_raffle_prize_id_idfx',
                    'key' => 'raffle_prize_id',
                    'reference' => [
                        'table' => 'raffle_prize',
                        'column' => 'id'
                    ]
                ],
                [
                    'constraint' => 'whitelabel_raffle_ticket_line_raffle_draw_id_idfx',
                    'key' => 'raffle_draw_id',
                    'reference' => [
                        'table' => 'raffle_draw',
                        'column' => 'id'
                    ]
                ],
            ]
        );

        \DBUtil::create_index('whitelabel_raffle_ticket_line', 'whitelabel_id', 'whitelabel_raffle_ticket_line_whitelabel_id_idfx_idx');
        \DBUtil::create_index('whitelabel_raffle_ticket_line', 'whitelabel_raffle_ticket_id', 'whitelabel_raffle_ticket_line_wl_raffle_ticket_id_idfx_idx');
        \DBUtil::create_index('whitelabel_raffle_ticket_line', 'raffle_prize_id', 'whitelabel_raffle_ticket_line_raffle_prize_id_idfx_idx');
        \DBUtil::create_index('whitelabel_raffle_ticket_line', 'raffle_draw_id', 'whitelabel_raffle_ticket_line_raffle_draw_id_idfx_idx');
        \DBUtil::create_index('whitelabel_raffle_ticket_line', 'number', 'whitelabel_raffle_ticket_number_idfx_idx');
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_raffle_ticket_line', 'whitelabel_raffle_ticket_line_whitelabel_id_idfx');
        \DBUtil::drop_foreign_key('whitelabel_raffle_ticket_line', 'whitelabel_raffle_ticket_line_wl_raffle_ticket_id_idfx');
        \DBUtil::drop_foreign_key('whitelabel_raffle_ticket_line', 'whitelabel_raffle_ticket_line_raffle_prize_id_idfx');
        \DBUtil::drop_foreign_key('whitelabel_raffle_ticket_line', 'whitelabel_raffle_ticket_line_raffle_draw_id_idfx');

        \DBUtil::drop_table('whitelabel_raffle_ticket_line');
    }
}
