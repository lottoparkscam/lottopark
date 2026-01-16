<?php

use GuzzleHttp\Psr7\Response;

/**
 * Endpoint for fetching LCS draws.
 */
class Services_Lcs_Draws_Mock implements Services_Lcs_Draws_Contract
{
    const URL = '/lottery/draws';

    public function request(string $lottery_slug, string $raffle_type = 'closed', int $limit = self::DEFAULT_LIMIT, int $offset = 0, int $lastDrawNumber = 0): Services_Lcs_Client_Response
    {
        $data = json_decode(file_get_contents(APPPATH . str_replace('\\', DIRECTORY_SEPARATOR, 'tests\data\lcs\draws_to_sync_response.json')), true);
        $data = array_slice($data, $offset, $limit);
        $response = new Response(200, [], json_encode($data));
        return new Services_Lcs_Client_Response($response);
    }
}
