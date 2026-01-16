<?php

namespace Fuel\Tasks;

use Fuel\Core\Cli;
use Fuel\Core\DB;
use Task_Cli;
use Throwable;

final class Count_Raffle_Ticket_Lines extends Task_Cli
{
    /**
     * Count each raffle ticket lines and fill it in line_count field in whitelabel_raffle_ticket table.
     */
    public function run()
    {
        try {
            $count_query = DB::expr('(SELECT COUNT(id) FROM whitelabel_raffle_ticket_line WHERE whitelabel_raffle_ticket_id = whitelabel_raffle_ticket.id)');

            $changed_tickets = DB::update('whitelabel_raffle_ticket')
                ->set(['line_count' => $count_query])
                ->where('line_count', '=', 0)
                ->execute();

            Cli::write(sprintf('Line_count has been updated in %d tickets!', $changed_tickets));
        } catch (Throwable $e) {
            Cli::write($e->getMessage());
        }
    }
}
