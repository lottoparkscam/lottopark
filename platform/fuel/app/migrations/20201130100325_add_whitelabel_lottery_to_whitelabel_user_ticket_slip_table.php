<?php

namespace Fuel\Migrations;

use Fuel\Core\DB;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Add_Whitelabel_Lottery_To_Whitelabel_User_Ticket_Slip_Table extends \Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields('whitelabel_user_ticket_slip', [
            'whitelabel_lottery_id' => [
                'type'          => 'int',
                'constraint'    => 10,
                'unsigned'      => true,
                'null'          => true,
                'default'       => null,
                'after'         => 'whitelabel_ltech_id'
            ]
        ]);

        DBUtil::add_foreign_key(
            'whitelabel_user_ticket_slip',
            Helper_Migration::generate_foreign_key('whitelabel_user_ticket_slip', 'whitelabel_lottery_id')
        );

        DB::update('whitelabel_user_ticket_slip')
            ->join('whitelabel_user_ticket', 'INNER')
            ->on('whitelabel_user_ticket_slip.whitelabel_user_ticket_id', '=', 'whitelabel_user_ticket.id')
            ->join('whitelabel_lottery', 'INNER')
            ->on('whitelabel_user_ticket.lottery_id', '=', 'whitelabel_lottery.lottery_id')
            ->and_on('whitelabel_user_ticket.whitelabel_id', '=', 'whitelabel_lottery.whitelabel_id')
            ->set(['whitelabel_user_ticket_slip.whitelabel_lottery_id' => DB::expr('whitelabel_lottery.id')])
            ->where('whitelabel_user_ticket_slip.whitelabel_lottery_id', '=', NULL)
            ->execute();

        DBUtil::modify_fields('whitelabel_user_ticket_slip', [
            'whitelabel_lottery_id' => [
                'type'          => 'int',
                'constraint'    => 10,
                'unsigned'      => true,
            ]
        ]);
    }

    protected function down_gracefully(): void
    {
        $key = Helper_Migration::generate_foreign_key('whitelabel_user_ticket_slip', 'whitelabel_lottery_id');
        DBUtil::drop_foreign_key('whitelabel_user_ticket_slip', $key['constraint']);
        DBUtil::drop_fields('whitelabel_user_ticket_slip', [
            'whitelabel_lottery_id'
        ]);
    }
}