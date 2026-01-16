<?php

namespace Fuel\Tasks;

use Fuel\Core\DB;
use Task_Cli;

class Create_LCS_Missing_Slips extends Task_Cli
{
    public function __construct()
    {
        $this->disableOnProduction();
    }

    public function run(): void
    {
        $pending_tickets = DB::query("SELECT w.name, wut.date, wt.date_confirmed, wut.whitelabel_id, wut.draw_date, l.name AS lottery_name, l.timezone, wut.id AS ticket_id,
            wut.token, (SELECT COUNT(*) FROM whitelabel_user_ticket_slip wuts WHERE wuts.whitelabel_user_ticket_id = wut.id) AS count
            FROM whitelabel_user_ticket wut
            LEFT JOIN whitelabel_transaction wt ON wt.id = wut.whitelabel_transaction_id
            LEFT JOIN whitelabel_lottery wl ON wl.lottery_id = wut.lottery_id
            LEFT JOIN whitelabel w ON w.id = wut.whitelabel_id
            LEFT JOIN lottery_provider lp ON lp.id = wl.lottery_provider_id
            LEFT JOIN lottery l ON wut.lottery_id = l.id
            WHERE wl.whitelabel_id = wut.whitelabel_id
            AND wut.paid = 1
            AND wut.status = 0
            AND lp.provider = 3
            AND wut.is_synchronized = 0
            AND wut.draw_date = l.next_date_local
            HAVING count = 0")->execute()->as_array();

        foreach ($pending_tickets as $pending_ticket) {
            $ticket = \Model_Whitelabel_User_Ticket::find_by_pk($pending_ticket['ticket_id']);
            \Lotto_Helper::create_slips_for_ticket($ticket);
        }
    }
}
