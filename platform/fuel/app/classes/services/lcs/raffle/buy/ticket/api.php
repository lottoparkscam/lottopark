<?php

/**
 * Class Services_Lcs_Raffle_Buy_Ticket_Api
 * Endpoint for creating new tickets on LCS.
 */
class Services_Lcs_Raffle_Buy_Ticket_Api extends Services_Lcs_Client_Abstract implements Services_Lcs_Raffle_Buy_Ticket_Contract
{
    const URL = '/lottery/tickets';

    /**
     * @param array $payload
     * @param string $raffle_slug
     * @param string $raffle_type
     * 'tickets' => [
     *    [
     *       'token' => $token,
     *       'amount' => $this->amount,
     *       'ip' => 127.0.0.1,
     *       'lines' => [[1-1000]]
     *   ]
     * ]
     *
     *
     * @return Services_Lcs_Client_Response
     */
    public function request(array $payload, string $raffle_slug, string $raffle_type = 'closed'): Services_Lcs_Client_Response
    {
        $response = $this->create_base_client(json_encode($payload))->post(self::URL, [
            'headers' => ['lottery-slug' => $raffle_slug],
            'json'    => $payload
        ]);
        return new Services_Lcs_Client_Response($response);
    }
}
