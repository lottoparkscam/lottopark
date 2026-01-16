<?php

use GuzzleHttp\Psr7\Response;

class Services_Lcs_Raffle_Ticket_Get_Mock implements Services_Lcs_Raffle_Ticket_Get_Contract
{
    public function request(
        array $payload,
        string $raffle_slug,
        string $raffle_type = 'closed'
    ): Services_Lcs_Client_Response {
        $data = array_map(function ($uuid) {
            return [
                'status' => Helpers_General::TICKET_STATUS_PENDING,
                'token' => '',
                'uuid' => $uuid,
                'prize' => '',
                'amount' => '',
                'draw_date' => '',
                'lottery_ticket_lines' => 1000
            ];
        }, $payload);
        $response = new Response(200, [], json_encode($data));
        return new Services_Lcs_Client_Response($response);
    }
}
