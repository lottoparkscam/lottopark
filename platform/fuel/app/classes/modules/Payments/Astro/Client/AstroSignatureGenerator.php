<?php

namespace Modules\Payments\Astro\Client;

/**
 * Class AstroSignatureGenerator
 * Generates signature for Astro clients.
 *
 * @url https://developers-wallet.astropay.com/#security
 */
class AstroSignatureGenerator
{
    public function issue(string $key, array $payload = []): string
    {
        $payloadAsJson = json_encode($payload);
        return hash_hmac('sha256', $payloadAsJson, $key, false);
    }
}
