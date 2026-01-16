<?php

use Task\LotteryCentralServer\Send\DuplicateTicketTask;

/**
 * Task for sending tickets into lottery central server.
 */
final class Task_Lotterycentralserver_Send_Ticket extends Task_Lotterycentralserver_Send_Task
{

    /**
     * lottery.
     * @var Model_Lottery
     */
    private $lottery;

    /**
     * Unsynchronized lines, that will be collected into proper form and sent to lcs.
     * @var array
     */
    private $lines;

    /**
     * ticket_count.
     * @var int
     */
    private $ticket_count;

    private int $tickets_count = 0;

    /**
     * Create new send ticket to lcs task.
     *
     * @param Model_Lottery $lottery 
     * @param array $lines unsynchronized lines, that will be collected into proper form and sent to lcs.
     * @param int $ticket_count ticket_count.
     */
    public function __construct(Model_Lottery $lottery, array &$lines, int $ticket_count)
    {
        parent::__construct();
        $this->lottery = $lottery;
        $this->lines = &$lines;
        $this->tickets_count = $ticket_count;
    }

    /**
     * Format numbers to LCS array form.
     *
     * @param array $line line with numbers.
     * @return array numbers in proper format.
     */
    private function get_array_numbers(array $line): array
    {
        $numbers = [];
        $numbers[] = explode(',', $line['numbers']);
        if (!empty($line['bnumbers'])) { // bnumbers are optional and we don't want to add them if they not exist
            $numbers[] = explode(',', $line['bnumbers']);
        }
        return $numbers;
    }

    private function has_reached_maximum_ticket_count(array $line, ?string &$last_ticket_id, int &$ticket_count): bool
    {
        $current_ticket_id = &$line['whitelabel_user_ticket_id'];
        if ($current_ticket_id !== $last_ticket_id) {
            $ticket_count++;
            $last_ticket_id = $current_ticket_id;
        }

        return $ticket_count === $this->tickets_count;
    }

    private function remove_unused_lines(string $last_ticket_id): void
    {
        $lines = &$this->lines;
        do {
            $line = array_pop($lines);
        } while ($line['whitelabel_user_ticket_id'] >= $last_ticket_id);
        // restore last line for the last ticket
        $lines[] = $line;
    }

    /**
     * Collect slips from lines into lcs request payload.
     * NOTE2: we leave slip_id as keys - the only important thing about them is order.
     *
     * @return array request_payload for insert ticket lcs endpoint.
     */
    private function collect_lines_into_lcs_payload(): array
    {
        $request_payload = [
            'tickets' => []
        ];
        [$last_ticket_id, $slip_id_old, $ticket_count] = [null, null, -1];

        // collect data from database int lcs format
        // we iterate over lines, so we need to squash them back to tickets
        // or even more accurately slips (lcs_tickets)
        $has_reached_maximum_ticket_count = false;
        foreach ($this->lines as $line) {
            $line_price = $this->lottery->price;
            if ($this->has_reached_maximum_ticket_count($line, $last_ticket_id, $ticket_count)) {
                $has_reached_maximum_ticket_count = true;
                break;
            }
            $slip_id = &$line['whitelabel_user_ticket_slip_id'];
            if (empty($slip_id)){
                throw new Exception("Ticket line (ID: {$line['id']}) has no slip_id.");
            }

            if (isset($line['multiplier'])) {
                $line_price *= $line['multiplier'];
            }

            if ($slip_id_old !== $slip_id) { // we got new slip line - full insert
                $request_payload['tickets'][$slip_id] = [
                    'token' => "{$line['ticket_token']}-$slip_id",
                    'amount' => $line_price,
                    'ip' => $line['ip'],
                    'lines' => [
                        ['numbers' => $this->get_array_numbers($line)]
                    ],
                ];

                $possible_extra_fields = [
                    'multiplier',
                    'numbers_per_line',
                ];
                foreach ($possible_extra_fields as $possible_extra_field) {
                    if (isset($line[$possible_extra_field])) {
                        $request_payload['tickets'][$slip_id][$possible_extra_field] = $line[$possible_extra_field];
                    }
                }

                $slip_id_old = $slip_id; // remember slip id
            } else { // line for the same slip - add amount and numbers
                $request_payload['tickets'][$slip_id]['amount']
                    += $line_price;
                $request_payload['tickets'][$slip_id]['lines'][]['numbers']
                    = $this->get_array_numbers($line);
            }
        }

        if ($has_reached_maximum_ticket_count) {
            $this->remove_unused_lines($last_ticket_id);
        }

        return $request_payload;
    }

    /**
     * Send slips to Lottery Central Server.
     * @param array $request_payload to be sent with request.
     *
     * @return array Lottery Central Server response.
     */
    private function send_slips(array $request_payload): array
    {
        // prepare necessary parameters for communication
        $route = "lottery/tickets";
        $endpoint_url = parent::absolute_url($route);
        $headers = parent::headers($route, Helpers_Lottery::get_slug($this->lottery->id), $request_payload);

        // query LCS via Curl and return decoded result
        $response = Services_Curl::post_json($endpoint_url, $request_payload, $headers);

        return json_decode($response, true);
    }

    /**
     * Run task.
     * NOTE: called directly is not error safe.
     * @return void
     */
    public function run(): void
    {
        // first we need to collect lines for slips
        $request_payload = $this->collect_lines_into_lcs_payload();

        // send data to lcs
        $lcs_response = $this->send_slips($request_payload);

        try {
            // validate result of send
            parent::evaluate_response($lcs_response, 'lottery_tickets');
        } catch (ErrorException $exception) {
            // We have received "error" with tickets already purchased in LCS. This should be handled by WL to unblock batch.
            $isAlreadyUsedTokensLcsError = Services_Curl::get_last_request_result_code() === DuplicateTicketTask::ALREADY_USED_TOKENS_ERROR_CODE && key_exists('error', $lcs_response) && key_exists('usedTokens', $lcs_response['error']);
            if ($isAlreadyUsedTokensLcsError) {
                DuplicateTicketTask::execute($lcs_response, $request_payload);
                /**
                 * In this scenario we need to stop the entire batch, it is expecting correct data to synchronize tickets.
                 * We only want to fix duplicates and the batch will start working again in the next run.
                 */
                die;
            }

            throw new Exception($exception->getMessage());
        }

        // attach result (slip uuids) if they are valid
        $this->get_result()->put_data_item('request_payload', $request_payload); // payload is needed for slip_id
        $this->get_result()->put_data_item('lcs_response', $lcs_response);
        $this->get_result()->put_data_item('lines', $this->lines);
    }
}
