<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;
use Throwable;

class Drop_ticket_line_draw_id_column
{
    public function up()
    {
        try {
            DBUtil::drop_foreign_key('whitelabel_raffle_ticket_line', 'whitelabel_raffle_ticket_line_raffle_draw_id_idfx');
        } catch (Throwable $exception) {
        }
        DBUtil::drop_fields('whitelabel_raffle_ticket_line', ['raffle_draw_id']);
    }

    public function down()
    {
        try {
            DBUtil::add_foreign_key('whitelabel_raffle_ticket_line', [
                'constraint' => 'raffle_prize_raffle_draw_id_idfx',
                'key'        => 'raffle_draw_id',
                'reference'  => [
                    'table'  => 'raffle_draw',
                    'column' => 'id'
                ]
            ]);
        } catch (Throwable $exception) {
        }
        DBUtil::add_fields('whitelabel_raffle_ticket_line', [
            'raffle_draw_id' => ['type' => 'int', 'unsigned' => true]
        ]);
    }
}
