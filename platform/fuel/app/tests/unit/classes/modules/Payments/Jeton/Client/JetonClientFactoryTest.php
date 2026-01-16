<?php

namespace Unit\Services\Payments\Jeton\Client;

use GuzzleHttp\Psr7\Uri;
use Modules\Payments\CustomOptionsAwareContract;
use Modules\Payments\Jeton\Client\JetonClientFactory;
use Test_Unit;

class JetonClientFactoryTest extends Test_Unit
{
    /** @test */
    public function create__valid_client_with_base_url_and_headers_set(): void
    {
        // Given
        $baseUrl = 'some-base_url.com';
        $apiKey = 'api-key';
        $expectedContentTypeHeader = 'application/json';
        $options = [
            'jeton_base_url' => $baseUrl,
            'jeton_api_key' => $apiKey
        ];

        $order = $this->getMockForAbstractClass(CustomOptionsAwareContract::class);
        $order->method('getOptions')->willReturn($options);

        $s = new JetonClientFactory();

        // When
        $client = $s->create($order);
        $actualConfig = $client->getConfig();

        // Then
        $this->assertEquals(new Uri($baseUrl), $actualConfig['base_uri']);
        $this->assertSame($expectedContentTypeHeader, $actualConfig['headers']['Content-Type']);
        $this->assertSame($apiKey, $actualConfig['headers']['X-API-KEY']);
    }
}
