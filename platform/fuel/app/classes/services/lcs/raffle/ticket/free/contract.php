<?php

interface Services_Lcs_Raffle_Ticket_Free_Contract
{
    public function request(
        string $raffle_slug,
        string $raffle_type = 'closed'
    ): Services_Lcs_Client_Response;
}
