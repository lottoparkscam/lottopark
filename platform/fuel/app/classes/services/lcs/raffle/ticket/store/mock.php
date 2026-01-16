<?php

use GuzzleHttp\Psr7\Response;

class Services_Lcs_Raffle_Ticket_Store_Mock implements Services_Lcs_Raffle_Ticket_Store_Contract
{
    public function request(
        array $payload,
        string $raffle_slug,
        string $raffle_type = 'closed'
    ): Services_Lcs_Client_Response {
        $data = ['updated_count' => count($payload['uuids'])];
        $response = new Response(200, [], json_encode($data));
        return new Services_Lcs_Client_Response($response);
    }
}
