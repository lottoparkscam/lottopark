<?php

namespace Unit\Modules\Payments\Astro\Client;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Modules\Payments\Astro\Client\AstroCheckStatusClient;
use Modules\Payments\Astro\Client\AstroClientFactory;
use Modules\Payments\CustomOptionsAwareContract;
use Test_Unit;

class AstroCheckStatusClientTest extends Test_Unit
{
    private AstroClientFactory $factory;

    private AstroCheckStatusClient $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->factory = $this->createMock(AstroClientFactory::class);
        $this->client = new AstroCheckStatusClient($this->factory);
    }

    /** @test */
    public function request__no_deposit_external_id__throws_invalid_argument_exception(): void
    {
        // Except
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected the key "deposit_external_id" to exist.');

        // Given
        $order = $this->getMockForAbstractClass(CustomOptionsAwareContract::class);

        // When
        $this->client->request($order);
    }

    /** @test */
    public function request__valid_data(): void
    {
        // Given
        $externalId = 'asb12312332';
        $additionalData = ['deposit_external_id' => $externalId];

        $order = $this->getMockForAbstractClass(CustomOptionsAwareContract::class);
        $order
            ->expects($this->once())
            ->method('getAdditionalData')
            ->willReturn($additionalData);

        $client = $this->createMock(Client::class);

        $this->factory
            ->expects($this->once())
            ->method('create')
            ->with($order)
            ->willReturn($client);

        $expectedFinalUrl = "/merchant/v1/deposit/$externalId/status";

        $responseMock = $this->mockResponse([]);
        $client
            ->expects($this->once())
            ->method('request')
            ->with('GET', $expectedFinalUrl)
            ->willReturn($responseMock);

        // When
        $this->client->request($order);
    }
}
