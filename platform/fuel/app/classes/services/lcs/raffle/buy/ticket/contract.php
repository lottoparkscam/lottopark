<?php

interface Services_Lcs_Raffle_Buy_Ticket_Contract
{
    public function request(array $payload, string $raffle_slug, string $raffle_type = 'closed'): Services_Lcs_Client_Response;
}
