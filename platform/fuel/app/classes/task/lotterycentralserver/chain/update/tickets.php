<?php

use Fuel\Core\Database_Result;
use Repositories\LotteryLogRepository;

/** Chain task, which will update draw data for tickets. */
final class Task_Lotterycentralserver_Chain_Update_Tickets extends Task_Lotterycentralserver_Chain_Lottery_Batch
{

    /**
     * How many LCS tickets/slips should be processed at once. This value should not be greater than max slip count per
     * whitelabel user ticket
     */
    const BATCH_SIZE = 1000;

    /**
     * How many batches can be done per one task.
     */
    const MAX_ITERATION = 5; // 5x5000 = 25000 lines per task

    private function fetch_tickets_from_lcs(array $batch_array): Task_Interface_Result
    {
        return Task_Lotterycentralserver_Fetch_Lottery_Tickets::execute($batch_array, $this->lottery);
    }

    private function get_lcs_payout_task_class(): string
    {
        return Task_Lotterycentralserver_Send_Pay_Out::class;
    }

    /**
     * Run task.
     * NOTE: called directly is not error safe.
     *
     * @return void
     */
    public function run(): void
    {
        // load lottery - needed for prize calculation
        $lottery = $this->lottery;
        $lottery_id = $lottery['id'];
        if ($lottery->is_disabled()) {
            return; // lottery is disabled - exit early
        }
        $lottery_currency_id = $lottery->currency_id;

        // first check if last draw has pending tickets
        $last_draw = Model_Lottery_Draw::last_for_lottery_by_draw_no($lottery_id);

        if ($last_draw === null) {
            return; // there is no draw, should only occur on fresh lottery
        }

        /** @var mixed $last_draw */
        if (!$last_draw->has_pending_tickets) {
            return; // draw is already calculated - exit
        }

        // fetch prizes
        if ($is_keno = $lottery->is_keno()) {
            $columns = ['prizes', 'is_jackpot', 'lottery_type_data_id', 'slug'];
        } else {
            $columns = ['winners', 'prizes', 'is_jackpot', 'lottery_type_data_id'];
        }
        $prizes = $last_draw->prizes_with_type_data($columns, $is_keno);
        if ($prizes === null) {
            throw new \Exception("Failed to load prizes for lottery {$this->lottery_slug}, draw {$last_draw->id}");
        }

        // load currencies - for calculation of prizes (prize_usd, prize_user, net_, uncovered)
        $currencies = Helpers_Currency::getCurrencies();
        if ($currencies === null) {
            throw new \Exception("Failed to load currencies for lottery {$this->lottery_slug}, draw {$last_draw->id}");
        }

        // load provider for lottery
        $lottery_provider = Model_Lottery_Provider::last_for_lottery($lottery_id);
        if ($lottery_provider === null) {
            throw new \Exception("Failed to load provider for lottery {$this->lottery_slug}, draw {$last_draw->id}");
        }

        $processed_batches_count = 0;
        $finished = true;
        switch ($lottery->type) {
            case Helpers_Lottery::TYPE_KENO:
                $update_tickets_prize_class = Task_Lotterycentralserver_Database_Update_Tickets_Prize_Keno::class;
                break;
            default:
                $update_tickets_prize_class = Task_Lotterycentralserver_Database_Update_Tickets_Prize::class;
                break;
        }
        // process batches of tickets: fetch them from lcs, update appropriate fields
        $process = function (Database_Result $batch, bool &$is_last_batch) use ($prizes, $currencies, $lottery_provider, $lottery_currency_id, &$processed_batches_count, &$finished, $update_tickets_prize_class, &$lottery): void { // define batch processing logic
            // fetch tickets data from lcs
            $batch_array = $batch->as_array();
            $is_not_last_batch = !$is_last_batch;
            if ($is_not_last_batch) {
                // if not last batch we need to remove last whitelabel ticket, since we don't know if all of it's slips are taken into account
                $last_ticket_index = self::BATCH_SIZE - 1;
                $last_ticket_id = $batch_array[$last_ticket_index]['ticket_id'];
                $removed_ticket_entries = [];
                // pick last ticket entries, stop if reached beginning of the batch
                for ($i = $last_ticket_index; $i >= 0 && $last_ticket_id === $batch_array[$i]['ticket_id']; $i--) {
                    $removed_ticket_entries[$i] = $batch_array[$i];
                    unset($batch_array[$i]);
                }
                if ($i === -1) { // iteration reached the end - there is only one ticket in batch
                    $batch_array = $removed_ticket_entries; // restore it
                }
            }
            $fetching_tickets_result =
                $this->fetch_tickets_from_lcs($batch_array);

            $this->throw_task_failure($fetching_tickets_result, 'Failed to fetch tickets from lcs'); // NOTE: batches before will not be rolled back

            // run in transaction chain: update ticket and lines, update user balance, send uuids to the lcs
            $updating_and_sending_result = Task_Runner::execute(
                [
                    $update_tickets_prize_class => [
                        $fetching_tickets_result,
                        $batch_array,
                        $prizes,
                        $currencies,
                        $lottery_provider,
                        $lottery_currency_id,
                        $lottery
                    ],
                    Task_Lotterycentralserver_Database_Update_Users_Balance::class => [],
                    $this->get_lcs_payout_task_class() => [$this->lottery->id],
                ]
            );

            if ($updating_and_sending_result->is_failed()) {
                $this->throw_failure(
                    "{$updating_and_sending_result->get_data_item('failed_task_class')} has failed, unable to proceed"
                ); // NOTE: batches before will not be rolled back
            }

            $processed_batches_count++; // count successful batches

            if ($processed_batches_count === self::MAX_ITERATION) { // TODO: {Vordis 2019-07-19 09:52:18} probably should be abstracted
                $is_last_batch = true; // break here if we reached maximum iteration.
                $finished = false;
            }
        };

        Model_Model::chunk_no_offset(
            Model_Whitelabel_User_Ticket::pending_for_lottery_with_lcs_ticket_user_and_whitelabel_task($lottery_id, $last_draw->date_local),
            self::BATCH_SIZE,
            $process
        );

        // at last if everything worked fine set draw has_pending_tickets
        if ($finished) { // take into account that task could be finished without actually synchronizing everything
            $last_draw->has_pending_tickets = 0;
            $last_draw->save(); // NOTE: failure here is not dangerous - it will do it in next run.

            // Set draw id in draw email notifications list
            $notification_draw_helper = new Helpers_Notifications_Draw();
            $notification_draw_helper->update_draw_notification_emails($last_draw->lottery_id, $last_draw->date_local, $last_draw->id);
        }
        /** @var LotteryLogRepository $lotteryLogRepository */
        $lotteryLogRepository = Container::get(LotteryLogRepository::class);

        $lotteryLogRepository->addSuccessLog(
            $lottery_id,
            "Successfully updated tickets (prizes), paid them out and marked them as paid out at LCS. 
            Number of processed batches = $processed_batches_count
            Batch size = " . self::BATCH_SIZE
        );
    }
}
