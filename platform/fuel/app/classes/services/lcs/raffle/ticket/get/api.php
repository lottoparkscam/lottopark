<?php

/**
 * Class Services_Lcs_Raffle_Ticket_Get_Api
 * LCS Api - returns tickets data.
 */
class Services_Lcs_Raffle_Ticket_Get_Api extends Services_Lcs_Client_Abstract implements Services_Lcs_Raffle_Ticket_Get_Contract
{
    const URL = '/lottery/tickets/get';

    /**
     * @param array $payload - ['uuids' => ['uuid1', 'uuid2']]
     * @param string $raffle_slug
     * @param string $raffle_type
     *
     * @return Services_Lcs_Client_Response
     */
    public function request(
        array $payload,
        string $raffle_slug,
        string $raffle_type = 'closed'
    ): Services_Lcs_Client_Response {
        $encoded_payload = $this->get_encoded_payload($payload);
        $client = $this->create_base_client($encoded_payload);
        $response = $client->get(self::URL, [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'lottery-slug' => $raffle_slug,
            ],
            'body' => $encoded_payload
        ]);
        return new Services_Lcs_Client_Response($response);
    }

    private function get_encoded_payload(array $ticket_uuids): string
    {
        return json_encode($ticket_uuids);
    }
}
