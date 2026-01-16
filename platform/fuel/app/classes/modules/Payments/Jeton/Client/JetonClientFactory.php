<?php

namespace Modules\Payments\Jeton\Client;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Modules\Payments\ClientFactoryContract;
use Modules\Payments\CustomOptionsAwareContract;

/**
 * Class JetonClientFactory
 * Creates base client (Guzzle) for Jetton payments.
 */
class JetonClientFactory implements ClientFactoryContract
{
    public function create(CustomOptionsAwareContract $order, array $payload = []): ClientInterface
    {
        $options = $order->getOptions();

        return new Client([
            'base_uri' => $options['jeton_base_url'],
            'verify' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-API-KEY' => $options['jeton_api_key'],
            ],
            'timeout' => \Helpers_General::GUZZLE_TIMEOUT_IN_SECONDS
        ]);
    }
}
