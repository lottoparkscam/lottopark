<?php

use Services\Logs\FileLoggerService;

/**
 * Translate tickets into human readable form.
 * NOTE: auto include of insured trait.
 */
trait Traits_Formats_Tickets
{
    use Traits_Formats_Insured;

    /**
     * Prepare tickets to human readable form.
     * @param array $tickets tickets
     * @param array|Model_Whitelabel $whitelabel whitelabel
     * @return array prepared tickets.
     */
    private function prepare_tickets(array $tickets, $whitelabel): array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        // prepare arrays of tickets data
        $tickets_prepared = [];
        $lotteries = Model_Lottery::get_really_all_lotteries_for_whitelabel($whitelabel);
        
        // format to human readable.
        foreach ($tickets as $ticket) {
            if (!isset($lotteries["__by_id"][$ticket->lottery_id])) {
                $msg = "There is a problem with lottery settings. " .
                    "No lottery within lotteries list. Lottery ID: " .
                    $ticket->lottery_id . " " .
                    "Ticket ID: " .
                    $ticket->id . " " .
                    "Whitelabel ID: " . $whitelabel['id'];

                $fileLoggerService->error(
                    $msg
                );
                
                continue;
            }
            
            $tickets_prepared[] = [
                'type_of_lottery' => $lotteries['__by_id'][$ticket->lottery_id]["name"],
                'type_of_ticket' => $this->translate_is_insured($ticket->is_insured),
                'number_of_bets' => $ticket->line_count,
                'amount' => $ticket->amount_manager,
                'cost' => $ticket->cost_manager,
            ];
        }
        // return prepared tickets
        return $tickets_prepared;
    }
}
