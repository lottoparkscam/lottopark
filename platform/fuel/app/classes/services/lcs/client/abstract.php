<?php

use Fuel\Core\Config;
use GuzzleHttp\Client;

abstract class Services_Lcs_Client_Abstract
{
    public const URL = '';

    protected Services_Lcs_Auth_Resolver $credentials_resolver;

    public function __construct(Services_Lcs_Auth_Resolver $credentials_resolver)
    {
        $this->credentials_resolver = $credentials_resolver;
        $this->verify_url_is_defined();
    }

    protected function create_base_client(string $message = ''): Client
    {
        Config::load('lottery_central_server', true);
        return new Client([
            'base_uri' => Config::get('lottery_central_server.url.base'),
            'headers' => array_merge(
                [
                    'X-Requested-With' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                $this->credentials_resolver->issue(static::URL, $message)->to_array()
            ),
            'timeout' => Helpers_General::GUZZLE_TIMEOUT_IN_SECONDS
        ]);
    }

    private function verify_url_is_defined()
    {
        if (empty(static::URL)) {
            throw new RuntimeException('URL const must be defined in parent API class!');
        }
    }
}
