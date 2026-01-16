<?php

namespace Modules\Payments\Astro\Client;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Modules\Payments\ClientFactoryContract;
use Modules\Payments\CustomOptionsAwareContract;

class AstroClientFactory implements ClientFactoryContract
{
    private AstroSignatureGenerator $signatureGenerator;

    public function __construct(AstroSignatureGenerator $signatureGenerator)
    {
        $this->signatureGenerator = $signatureGenerator;
    }

    public function create(CustomOptionsAwareContract $order, array $payload = []): ClientInterface
    {
        $options = $order->getOptions();
        $secretKey = $options['astro_secret_key'];
        $baseUrl = $options['astro_base_url'];
        $apiKey = $options['astro_api_key'];
        $signature = $this->signatureGenerator->issue(
            $secretKey,
            $payload
        );

        $client = new Client([
            'base_uri' => $baseUrl,
            'verify' => true,
            'headers' => [
                'Content-Type' => 'application/json',
                'Merchant-Gateway-Api-Key' => $apiKey,
                'Signature' => $signature
            ],
            'timeout' => \Helpers_General::GUZZLE_TIMEOUT_IN_SECONDS
        ]);

        return $client;
    }
}
