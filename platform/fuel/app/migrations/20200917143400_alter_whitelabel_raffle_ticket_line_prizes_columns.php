<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

class Alter_whitelabel_raffle_ticket_line_prizes_columns
{
    public function up()
    {
        DBUtil::add_fields('whitelabel_raffle_ticket_line', [
            'prize_local'   => ['type' => 'decimal', 'constraint' => [12, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'amount_manager'],
            'prize_usd'     => ['type' => 'decimal', 'constraint' => [12, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'prize_local'],
            'prize'         => ['type' => 'decimal', 'constraint' => [12, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'prize_usd'],
            'prize_manager' => ['type' => 'decimal', 'constraint' => [15, 2], 'unsigned' => true, 'default' => 0.00, 'after' => 'prize'],
        ]);
    }

    public function down()
    {
        DBUtil::drop_fields('whitelabel_raffle_ticket_line', [
            'prize_local',
            'prize_usd',
            'prize',
            'prize_manager',
        ]);
    }
}
