<?php

/**
 * Fetch tickets from Lottery Central Server.
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-06-11
 * Time: 11:06:56
 */
abstract class Task_Lotterycentralserver_Fetch_Tickets extends Task_Lotterycentralserver_Fetch_Task
{
    /**
     * Tickets with uuids.
     * @var array
     */
    protected $tickets;

    /**
     * Create new fetch ticket from LCS task.
     *
     * @param array $tickets Tickets with uuids.
     */
    public function __construct(array $tickets)
    {
        parent::__construct();
        $this->tickets = $tickets;
    }

    /**
     * Fetch tickets from LCS.
     *
     * @return Response_Interface should contain: tickets with prizes and rule tiers.
     * @throws Exception
     */
    public function fetch(): Response_Interface
    {
        // prepare payload
        $payload = [
            'uuids' => array_column($this->tickets, 'uuid')
        ];
        // prepare necessary parameters for communication
        $route = "lottery/tickets/get";
        $endpoint_url = parent::absolute_url($route);
        $headers = parent::headers($route, Helpers_Lottery::get_slug($this->get_lottery()->id), $payload);

        // query LCS via Curl and return decoded result
        $response = Services_Curl::get_json_with_payload($endpoint_url, $payload, $headers);

        return Response_Lcs_Lottery_Tickets::build_from_json(json_encode(['tickets' => json_decode($response)]));
    }
}
