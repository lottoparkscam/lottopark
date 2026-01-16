<?php

use Carbon\Carbon;

/**
 * Chain task, which will store in database values for ticket synchronization.
 *
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-06-19
 * Time: 14:03:26
 */
final class Task_Lotterycentralserver_Chain_Synchronize_Store extends Task_Lotterycentralserver_Chain_Task
{
    /**
     * id of the lottery for which tickets will be updated.
     *
     * @var Model_Lottery
     */
    private $lottery;

    /**
     * Result of the Task_Lotterycentralserver_Send_Ticket.
     *
     * @var Task_Result
     */
    private $sending_result;

    /**
     * Batch of lines, needed by set is_synchronized logic.
     *
     * @var array
     */
    private $lines_batch;


    /**
     * Create new instance of chain task
     *
     * @param Model_Lottery $lottery        id of the lottery for which tickets will be updated.
     * @param Task_Result   $sending_result Result of the Task_Lotterycentralserver_Send_Ticket.
     * @param array         $lines_batch    Batch of lines, needed by set is_synchronized logic.
     */
    public function __construct(Model_Lottery $lottery, Task_Result $sending_result, array $lines_batch)
    {
        parent::__construct(''); // sub chain - doesn't need lottery slug.
        $this->lottery = $lottery;
        $this->sending_result = $sending_result;
        $this->lines_batch = $lines_batch;
    }

    /**
     * Get draw date from lcs response.
     * It will be picked from first ticket from response.
     * NOTE: this field should be the same across all tickets in response.
     *
     *
     * @return string
     */
    private function get_lcs_draw_date(): string
    {
        $draw_date = $this->sending_result
            ->get_data_item('lcs_response')['lottery_tickets'][0]['draw_date'];
        $lottery_provider = Model_Lottery_Provider::last_for_lottery($this->lottery->id);

        return Carbon::parse($draw_date, $lottery_provider->timezone)
            ->setTimezone($this->lottery->timezone)
            ->format(Helpers_Time::DATETIME_FORMAT);
    }

    /**
     * Run task.
     * NOTE: called directly is not error safe.
     *
     * @return void
     */
    public function run(): void
    {
        // insert into lcs_ticket
        $insertion_result = Task_Lotterycentralserver_Database_Insert_Lcs_Tickets::execute($this->sending_result);

        // silently return with failure - insert task already logged its cause.
        if ($insertion_result->is_failed()) {
            $this->get_result()->mark_as_failed();

            return;
        }

        // get ticket ids from lines batch
        $ticket_ids = array_unique(
            array_column($this->lines_batch, 'whitelabel_user_ticket_id')
        );
        // set tickets as synchronized
        $synchronized_tickets_count = // NOTE: db exception will automatically exit task with failure
            Model_Whitelabel_User_Ticket::buildQueryBuilderUpdateToSynchronized($ticket_ids)
                ->value('draw_date', $this->get_lcs_draw_date())
                ->execute();
        // we assume that there is no possibility to have empty result from LCS
        if (empty($synchronized_tickets_count)) { // NOTE: insert above will be rolled back on update failure
            throw new \Exception('Ticket synchronization returned 0 rows updated!');
        }

        $this->get_result()->put_data_item('synchronized_tickets_count', $synchronized_tickets_count);
    }
}
