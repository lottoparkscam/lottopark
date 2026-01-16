<?php

use GuzzleHttp\Psr7\Response;

class Services_Lcs_Raffle_Ticket_Free_Mock implements Services_Lcs_Raffle_Ticket_Free_Contract
{
    const URL = 'lottery/tickets/raffle/free_numbers';

    /**
     * @param string $raffle_slug - for example "gg-world-raffle"
     * @param string $raffle_type - for example "open"
     *
     * @return Services_Lcs_Client_Response
     *         [data][free_numbers]
     */
    public function request(
        string $raffle_slug,
        string $raffle_type = 'closed'
    ): Services_Lcs_Client_Response {
        $data = [
            'data' => [
                'free_numbers' => range(1, rand(1, 1000))
            ]
        ];
        $response = new Response(200, [], json_encode($data));
        return new Services_Lcs_Client_Response($response);
    }
}
