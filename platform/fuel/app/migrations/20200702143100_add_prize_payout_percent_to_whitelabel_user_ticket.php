<?php

namespace Fuel\Migrations;

class Add_prize_payout_percent_to_whitelabel_user_ticket
{
    public function up()
    {
        \DBUtil::add_fields(
            'whitelabel_user_ticket',
            ['prize_payout_percent' => ['type' => 'decimal', 'constraint' => [5,2], 'unsigned' => true, 'null' => true, 'default' => 100.00, 'after' => 'payout']]
        );
    }

    public function down()
    {
        \DBUtil::drop_fields(
            'whitelabel_user_ticket',
            ['prize_payout_percent']
        );
    }
}
