<?php

use Carbon\Carbon;
use Fuel\Core\Database_Result;
use Repositories\LotteryLogRepository;

/** Chain task, which will synchronize tickets with provider (LCS) */
final class Task_Lotterycentralserver_Chain_Synchronize_Tickets extends Task_Lotterycentralserver_Chain_Lottery_Batch
{

    use Task_Lotterycentralserver_Chain_Config_Ticket_Batch_Methods;

    // TODO: {Vordis 2019-10-29 10:27:51} double check iterations

    /**
     * Run task.
     * NOTE: called directly is not error safe.
     */
    public function run(): void
    {
        if ($this->lottery === null) {
            $this->throw_task_failure(
                $this->get_result(),
                'Lottery with given slug does not exist.'
            );

            return;
        }
        if ($this->lottery->is_disabled()) {
            print_r('lottery disabled');

            return; // lottery is disabled - exit early
        }

        if (empty($this->lottery->next_date_local)) {
            print_r('Next_date_local in lottery table is null');
            return;
        }

        // send slips in batches to LCS
        $processed_batches_count = $synchronized_tickets_count = 0;
        $process = function (Database_Result $batch, bool &$is_last_batch) use (&$processed_batches_count, &$synchronized_tickets_count): void { // define batch processing logic

            $lines_batch = $batch->as_array();
            // send batch to lcs
            $sending_result = Task_Lotterycentralserver_Send_Ticket::execute(
                $this->lottery,
                $lines_batch,
                $this->get_ticket_count($this->lottery->id)
            );

            // throw if failed NOTE: what was sent will not be rolled back
            $this->throw_task_failure($sending_result, 'Failed to send batch of tickets to lottery central server');
            $lines_batch = $sending_result->get_data_item('lines');

            // save result into lcs_ticket table
            // IMPORTANT: this must be flawless (it must happen)
            // or raise critical error (which need manual work)
            $retries = 0;
            while (
                ($storing_result = Task_Lotterycentralserver_Chain_Synchronize_Store::execute(
                    $this->lottery,
                    $sending_result,
                    $lines_batch
                ))
                    ->is_failed()
                && ($retries++ < $this->get_lcs_ticket_retries($this->lottery->id))
            ) {
                // sleep for tenth of second, and retry
                usleep(Helpers_Time::TENTH_OF_SECOND_IN_MICRO);
            }

            // validate result of insert
            // TODO: {Vordis 2019-06-24 12:33:46} maybe additional mail here
            $this->throw_task_failure(
                $storing_result,
                'Failed to store response from LCS in database (insert lcs_ticket or update is_synchronized on ticket table)'
            );

            $synchronized_tickets_count += $storing_result->get_data_item('synchronized_tickets_count');
            $processed_batches_count++;

            if ($processed_batches_count === $this->get_max_iterations($this->lottery->id)) {
                $is_last_batch = true; // break here if we reached maximum iteration.
            }

            // When we send many tickets to ltech in short time, their request time is getting longer
            // When it's longer than 1 second we receive timeout from LCS
            sleep(5);
        };
        Model_Model::chunk_no_offset(
            Model_Whitelabel_User_Ticket::unsychronized_for_lottery_with_lines_lcs($this->lottery->id, $this->lottery->next_date_local),
            $this->get_batch_size($this->lottery->id),
            $process,
            false
        );

        $this->addSuccessLog($processed_batches_count, $synchronized_tickets_count);

        /**
         * Specially for keno we want to check if there are some not processed tickets for previous draw
         * If ticket was bought in LCS before, then will be processed
         * If not, ticket will buy on the soonest draw
         */
        if ($this->lottery->is_keno()) {
            $processed_batches_count = $synchronized_tickets_count = 0;
            $previousDrawDate = Carbon::parse($this->lottery->next_date_local)->subMinutes(
                Helpers_Lottery::KENO_DRAW_INTERVAL_IN_MINUTES
            );
            Model_Model::chunk_no_offset(
                Model_Whitelabel_User_Ticket::unsychronized_for_lottery_with_lines_lcs($this->lottery->id, $previousDrawDate),
                $this->get_batch_size($this->lottery->id),
                $process,
                false
            );

            $this->addSuccessLog($processed_batches_count, $synchronized_tickets_count, true);
        }
    }

    private function addSuccessLog(int $processedBatchesCount, int $synchronizedTicketsCount, bool $oldTickets = false): void
    {
        /** @var LotteryLogRepository $lotteryLogRepository */
        $lotteryLogRepository = Container::get(LotteryLogRepository::class);

        $oldPrefix = '';
        if ($oldTickets) {
            $oldPrefix = 'OLD (MISSED FIRST DRAW AFTER PURCHASE)';
        }

        // we want to log success only if at least one task was processed
        if ($processedBatchesCount !== 0) {
            $message = "Successfully synchronized $oldPrefix tickets with LCS (sent them to LCS and stored them as slips).\r\n" .
                "Number of synchronized tickets = $synchronizedTicketsCount\r\n" .
                "Number of processed batches = $processedBatchesCount\r\n" .
                "Batch size = " . $this->get_batch_size($this->lottery->id) . "\r\n" .
                "Ticket count per batch = " . $this->get_ticket_count($this->lottery->id);
            Helpers_Cli::success($message);

            $lotteryLogRepository->addSuccessLog(
                $this->lottery->id,
                $message
            );
        } else {
            Helpers_Cli::info("No OLD tickets synchronized with LCS.");
        }
    }
}
