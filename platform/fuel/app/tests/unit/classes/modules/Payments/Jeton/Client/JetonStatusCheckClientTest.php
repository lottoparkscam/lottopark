<?php

namespace Unit\Services\Payments\Jeton\Client;

use GuzzleHttp\ClientInterface;
use Modules\Payments\ClientFactoryContract;
use Modules\Payments\CustomOptionsAwareContract;
use Modules\Payments\Jeton\Client\JetonStatusCheckClient;
use Modules\Payments\Jeton\Client\JetonTransactionType;
use Psr\Http\Message\ResponseInterface;
use Test_Unit;

class JetonStatusCheckClientTest extends Test_Unit
{
    private ClientFactoryContract $factory;
    private JetonStatusCheckClient $client;

    public function setUp(): void
    {
        parent::setUp();
        $this->factory = $this->createMock(ClientFactoryContract::class);
        $this->client = new JetonStatusCheckClient($this->factory);
    }

    /** @test */
    public function request__valid_args__calls_endpoint_with_args_values(): void
    {
        // Given
        $orderId = '123';
        $transactionType = JetonTransactionType::PAY();

        $expectedPayload = [
            'json' => [
                'orderId' => $orderId,
                'type' => (string)$transactionType
            ]
        ];

        $client = $this->createMock(ClientInterface::class);
        $client
            ->expects($this->once())
            ->method('request')
            ->with('POST', JetonStatusCheckClient::URL, $expectedPayload)
            ->willReturn($this->createStub(ResponseInterface::class));

        $order = $this->getMockForAbstractClass(CustomOptionsAwareContract::class);
        $order->method('getOrderId')->willReturn($orderId);

        $this->factory
            ->expects($this->once())
            ->method('create')
            ->with($order)
            ->willReturn($client);

        // When
        $this->client->request(
            $order,
            $transactionType
        );
    }
}
