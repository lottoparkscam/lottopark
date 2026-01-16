<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

class Alter_whitelabel_raffle_ticket_missing_ticket_columns
{
    public function up()
    {
        DBUtil::add_fields('whitelabel_raffle_ticket', [
            'prize_local' => ['type' => 'decimal', 'constraint' => [12, 2], 'unsigned' => true, 'null' => true, 'default' => null, 'after' => 'amount'],
            'prize_usd' => ['type' => 'decimal', 'constraint' => [12, 2], 'unsigned' => true, 'null' => true, 'default' => null, 'after' => 'prize_local'],
            'prize_manager' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => 0.00, 'after' => 'prize_usd'],
            'prize_net_local' => ['type' => 'decimal', 'constraint' => [12, 2], 'unsigned' => true, 'null' => true, 'default' => null, 'after' => 'prize_manager'],
            'prize_net_usd' => ['type' => 'decimal', 'constraint' => [12, 2], 'unsigned' => true, 'null' => true, 'default' => null, 'after' => 'prize_net_local'],
            'prize_net' => ['type' => 'decimal', 'constraint' => [12, 2], 'unsigned' => true, 'null' => true, 'default' => null, 'after' => 'prize_net_usd'],
            'prize_net_manager' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => 0.00, 'after' => 'prize_net'],
            'prize_jackpot' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => 0, 'after' => 'prize_net_manager'],
            'prize_quickpick' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => 0, 'after' => 'prize_jackpot'],
        ]);
    }

    public function down()
    {
        DBUtil::drop_fields('whitelabel_raffle_ticket', [
            'prize_local',
            'prize_usd',
            'prize_manager',
            'prize_net_local',
            'prize_net_usd',
            'prize_net',
            'prize_net_manager',
            'prize_jackpot',
            'prize_quickpick',
        ]);
    }
}
