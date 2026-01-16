<?php

use Fuel\Core\Config;
use GuzzleHttp\Client;

/**
 * Endpoint for fetching LCS draws.
 */
class Services_Lcs_Draws_Api extends Services_Lcs_Client_Abstract implements Services_Lcs_Draws_Contract
{
    const URL = '/lottery/draws';

    private string $url_with_query = '';

    public function request(string $lottery_slug, string $raffle_type = 'closed', int $limit = self::DEFAULT_LIMIT, int $offset = 0, int $lastDrawNumber = 0): Services_Lcs_Client_Response
    {
        $drawNumberParam = $lastDrawNumber ? sprintf('&draw_no=%d', $lastDrawNumber) : '';
        $this->url_with_query = sprintf("%s?limit=%d&offset=%d$drawNumberParam", self::URL, $limit, $offset);
        $response = $this->create_base_client()->get($this->url_with_query, [
            'headers' => [
                'lottery-slug' => $lottery_slug
            ],
        ]);
        return new Services_Lcs_Client_Response($response);
    }

    protected function create_base_client(string $message = ''): Client
    {
        Config::load('lottery_central_server', true);
        return new Client([
            'base_uri' => Config::get('lottery_central_server.url.base'),
            'headers'  => array_merge([
                'X-Requested-With' => 'application/json',
                'Content-Type'     => 'application/json',
            ], $this->credentials_resolver->issue($this->url_with_query, $message)->to_array()),
            'timeout' => Helpers_General::GUZZLE_TIMEOUT_IN_SECONDS
        ]);
    }
}
