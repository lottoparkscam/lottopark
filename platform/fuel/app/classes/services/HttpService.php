<?php

namespace Services;

use Fuel\Core\Fuel;
use GuzzleHttp\Client;

class HttpService
{
    public function getClient(array $config = []): Client
    {
        if (!isset($config['verify'])) {
            $config['verify'] = Fuel::$env !== Fuel::DEVELOPMENT;
        }

        return new Client($config);
    }
}
