<?php

/**
 * Class Services_Lcs_Raffle_Ticket_Free_Api
 * LCS Api - returns taken numbers.
 */
class Services_Lcs_Raffle_Ticket_Free_Api extends Services_Lcs_Client_Abstract implements Services_Lcs_Raffle_Ticket_Free_Contract
{
    const URL = '/lottery/tickets/raffle/free_numbers';

    /**
     * @param string $raffle_slug - for example "gg-world-raffle"
     * @param string $raffle_type - for example "open"
     *
     * @return Services_Lcs_Client_Response
     *         [data][free_numbers]
     */
    public function request(
        string $raffle_slug,
        string $raffle_type  = 'closed'
    ): Services_Lcs_Client_Response {
        $response = $this->create_base_client()->get(self::URL, [
            'headers' => ['lottery-slug' => $raffle_slug],
        ]);
        return new Services_Lcs_Client_Response($response);
    }
}
