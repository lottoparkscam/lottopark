<?php

namespace Unit\Services\Payments\Jeton\Client;

use GuzzleHttp\ClientInterface;
use Modules\Payments\ClientFactoryContract;
use Modules\Payments\CustomOptionsAwareContract;
use Modules\Payments\Jeton\Client\JetonCheckoutPayClient;
use Modules\Payments\PaymentLogger;
use Psr\Http\Message\ResponseInterface;
use Test_Unit;

class JetonCheckoutPayClientTest extends Test_Unit
{
    private ClientFactoryContract $factory;
    private JetonCheckoutPayClient $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->factory = $this->createMock(ClientFactoryContract::class);
        $logger = $this->createMock(PaymentLogger::class);
        $this->service = new JetonCheckoutPayClient($this->factory, $logger);
    }

    /** @test */
    public function request__valid_args__calls_endpoint_with_args_values_and_appended_return_url(): void
    {
        // Given
        $orderId = '123';
        $amount = 12.21;
        $currency = 'USD';
        $language = 'EN';
        $returnUrl = 'https://some-url.com/order/success';

        $expectedPayload = [
            'json' => [
                'orderId' => $orderId,
                'amount' => $amount,
                'currency' => $currency,
                'language' => $language,
                'returnUrl' => $returnUrl,
                'method' => 'CHECKOUT'
            ]
        ];

        $client = $this->createMock(ClientInterface::class);
        $client
            ->expects($this->once())
            ->method('request')
            ->with('POST', JetonCheckoutPayClient::URL, $expectedPayload)
            ->willReturn($this->createStub(ResponseInterface::class));

        $order = $this->getMockForAbstractClass(CustomOptionsAwareContract::class);
        $order->method('getOrderId')->willReturn($orderId);

        $this->factory
            ->expects($this->once())
            ->method('create')
            ->with($order)
            ->willReturn($client);

        // When

        $this->service->request(
            $order,
            $amount,
            $currency,
            $returnUrl
        );
    }

    /** @test */
    public function request__returnUrl__is_combination_of_system_base_url_and_config(): void
    {
        // Given
        $orderId = '123';
        $amount = 12.21;
        $currency = 'USD';
        $language = 'EN';

        $baseUrl = 'http://somedomain.com/';
        $returnUrl = 'order/success';
        $expectedReturnUrl = $baseUrl . $returnUrl;

        $expectedPayload = [
            'json' => [
                'orderId' => $orderId,
                'amount' => $amount,
                'currency' => $currency,
                'language' => $language,
                'returnUrl' => $expectedReturnUrl,
                'method' => 'CHECKOUT'
            ]
        ];

        $client = $this->createMock(ClientInterface::class);
        $client
            ->expects($this->once())
            ->method('request')
            ->with('POST', JetonCheckoutPayClient::URL, $expectedPayload)
            ->willReturn($this->createStub(ResponseInterface::class));

        $order = $this->getMockForAbstractClass(CustomOptionsAwareContract::class);
        $order->method('getOrderId')->willReturn($orderId);

        $this->factory
            ->expects($this->once())
            ->method('create')
            ->with($order)
            ->willReturn($client);

        // When
        $this->service->request(
            $order,
            $amount,
            $currency,
            $expectedReturnUrl
        );
    }
}
