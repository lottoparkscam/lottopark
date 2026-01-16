<?php

interface Services_Lcs_Draws_Contract
{
    public const DEFAULT_LIMIT = 10;

    public function request(
        string $lottery_slug,
        string $raffle_type = 'closed',
        int $limit = self::DEFAULT_LIMIT,
        int $offset = 0,
        int $lastDrawNumber = 0,
    ): Services_Lcs_Client_Response;
}
