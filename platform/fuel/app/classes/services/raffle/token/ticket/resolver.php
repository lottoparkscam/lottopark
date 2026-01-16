<?php

/**
 * Simple wrapper for security generate token.
 */
class Services_Raffle_Token_Ticket_Resolver
{
    private Lotto_Security $lotto_security;

    public function __construct(Lotto_Security $lotto_security)
    {
        $this->lotto_security = $lotto_security;
    }

    public function issue(int $whitelabel_id): int
    {
        return $this->lotto_security::generate_ticket_token($whitelabel_id);
    }
}
