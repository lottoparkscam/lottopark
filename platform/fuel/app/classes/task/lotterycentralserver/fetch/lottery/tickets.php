<?php

/**
 * Fetch tickets from Lottery Central Server.
 */
final class Task_Lotterycentralserver_Fetch_Lottery_Tickets extends Task_Lotterycentralserver_Fetch_Tickets
{
    /**
     * Create new fetch ticket from LCS task.
     *
     * @param array  $tickets Tickets with uuids.
     * @param Model_Lottery $lottery lottery_slug.
     */
    public function __construct(array $tickets, Model_Lottery $lottery)
    {
        parent::__construct($tickets);
        $this->set_lottery($lottery);
    }


    private function numeral_array_to_uuid_map(array $lcs_tickets): array
    {
        $tickets_map = [];
        foreach ($lcs_tickets as $ticket) {
            $tickets_map[$ticket['uuid']] = $ticket;
        }
        return $tickets_map;
    }

    /**
     * Run task.
     * NOTE: called directly is not error safe.
     * @return void
     * @throws Exception
     */
    public function run(): void
    {
        /** @var Response_Lcs_Lottery_Tickets $response */
        $response = $this->fetch();

        // validate result of request (not empty and without errors)
        parent::evaluate_response($response->tickets);

        // attach to result
        $this->get_result()->put_data_item('lottery_tickets', $this->numeral_array_to_uuid_map($response->tickets));
    }

}
