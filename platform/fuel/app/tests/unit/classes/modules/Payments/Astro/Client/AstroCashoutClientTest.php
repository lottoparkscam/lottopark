<?php

namespace Unit\Modules\Payments\Astro\Client;

use Fuel\Tasks\Factory\Utils\Faker;
use GuzzleHttp\ClientInterface;
use Modules\Payments\Astro\Client\AstroClientFactory;
use Modules\Payments\Astro\Client\AstroDepositClient;
use Modules\Payments\CustomOptionsAwareContract;
use Modules\Payments\PaymentLogger;
use Psr\Http\Message\ResponseInterface;
use Test_Unit;

class AstroCashoutClientTest extends Test_Unit
{
    /** @test */
    public function request__valid_data__returns_response(): void
    {
        // Given
        $orderId = 'ab123';
        $amount = 123.21;
        $currency = 'USD';
        $country = 'PL';
        $user = [];
        $callbackUrl = Faker::forge()->url();
        $redirectUrl = Faker::forge()->url();
        $product = [];

        $payload = [
            'amount' => $amount,
            'currency' => $currency,
            'country' => $country,
            'merchant_deposit_id' => $orderId,
            'callback_url' => $callbackUrl,
            'redirect_url' => $redirectUrl,
            'user' => $user,
            'product' => $product,
            'payment_method_code' => 'UI'
        ];

        $payloadAsJson = ['json' => $payload];

        $order = $this->getMockForAbstractClass(CustomOptionsAwareContract::class);
        $order->method('getOrderId')->willReturn($orderId);

        $response = $this->createMock(ResponseInterface::class);

        $baseClient = $this->createMock(ClientInterface::class);
        $baseClient
            ->expects($this->once())
            ->method('request')
            ->with('POST', '/merchant/v1/deposit/init', $payloadAsJson)
            ->willReturn($response);

        $factory = $this->createMock(AstroClientFactory::class);
        $factory->expects($this->once())
            ->method('create')
            ->with($order, $payload)
            ->willReturn($baseClient);

        // When
        $logger = $this->createMock(PaymentLogger::class);
        $client = new AstroDepositClient($factory, $logger);
        $actual = $client->request(
            $order,
            $amount,
            $currency,
            $country,
            $user,
            $product,
            $callbackUrl,
            $redirectUrl
        );

        // Then
        $this->assertInstanceOf(ResponseInterface::class, $actual);
    }
}
