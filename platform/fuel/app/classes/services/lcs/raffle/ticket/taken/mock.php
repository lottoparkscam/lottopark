<?php

use GuzzleHttp\Psr7\Response;

/**
 * LCS Api - returns taken numbers.
 */
class Services_Lcs_Raffle_Ticket_Taken_Mock implements Services_Lcs_Raffle_Ticket_Taken_Contract
{
    const URL = 'lottery/tickets/raffle/taken_numbers';

    /**
     * @param string $raffle_slug - for example "gg-world-raffle"
     * @param string $raffle_type - for example "open"
     *
     * @return Services_Lcs_Client_Response
     *         [data][taken_numbers]
     */
    public function request(
        string $raffle_slug,
        string $raffle_type = 'closed'
    ): Services_Lcs_Client_Response {
        $data = ['data' => ['taken_numbers' => range(rand(1, 10), rand(10, 40))]];
        $response = new Response(200, [], json_encode($data));
        return new Services_Lcs_Client_Response($response);
    }
}
