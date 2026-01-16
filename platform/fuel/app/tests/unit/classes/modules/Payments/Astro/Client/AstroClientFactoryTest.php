<?php

namespace Unit\Modules\Payments\Astro\Client;

use Fuel\Tasks\Factory\Utils\Faker;
use GuzzleHttp\Psr7\Uri;
use Modules\Payments\Astro\Client\AstroClientFactory;
use Modules\Payments\Astro\Client\AstroSignatureGenerator;
use Modules\Payments\CustomOptionsAwareContract;
use Test_Unit;

class AstroClientFactoryTest extends Test_Unit
{
    /** @test */
    public function create__client_contains_default_config(): void
    {
        // Given
        $baseUri = Faker::forge()->url();
        $expectedApiKey = Faker::forge()->uuid();
        $expectedSecretKey = Faker::forge()->uuid();
        $expectedMerchantApiKey = $expectedApiKey;
        $expectedSignature = Faker::forge()->uuid();
        $expectedVerify = true;
        $payload = [];
        $options = [
            'astro_secret_key' => $expectedSecretKey,
            'astro_base_url' => $baseUri,
            'astro_api_key' => $expectedApiKey,
        ];
        $expectedContentType = 'application/json';
        $baseUriChunks = parse_url($baseUri);

        $order = $this->getMockForAbstractClass(CustomOptionsAwareContract::class);
        $order->method('getOptions')->willReturn($options);

        $signatureGenerator = $this->createMock(AstroSignatureGenerator::class);
        $signatureGenerator
            ->expects($this->once())
            ->method('issue')
            ->with($expectedSecretKey, $payload)
            ->willReturn($expectedSignature);

        // When
        $client = new AstroClientFactory($signatureGenerator);
        $client = $client->create($order, $payload);
        /** @var Uri $actualBaseUri */
        $actualBaseUri = $client->getConfig('base_uri');
        $actualVerify = $client->getConfig('verify');
        $actualContentType = $client->getConfig('headers')['Content-Type'];
        $actualMerchantApiKey = $client->getConfig('headers')['Merchant-Gateway-Api-Key'];
        $actualSignature = $client->getConfig('headers')['Signature'];

        // Then

        # Check base uri is as config value
        $this->assertSame($actualBaseUri->getScheme(), $baseUriChunks['scheme']);
        $this->assertSame($actualBaseUri->getHost(), $baseUriChunks['host']);
        $this->assertSame($actualBaseUri->getPath(), $baseUriChunks['path']);

        # Verify is disabled by default
        $this->assertSame($expectedVerify, $actualVerify);

        # Verify content type
        $this->assertSame($expectedContentType, $actualContentType);

        # Verify merchant type
        $this->assertSame($expectedMerchantApiKey, $actualMerchantApiKey);

        # Verify merchant type
        $this->assertSame($expectedSignature, $actualSignature);
    }
}
