<?php

namespace Unit\Modules\Payments\Astro;

use Fuel\Tasks\Factory\Utils\Faker;
use Modules\Payments\Astro\AstroCheckoutUrlHandler;
use Modules\Payments\Astro\AstroFacade;
use Modules\Payments\Astro\AstroStatusHandler;
use Modules\Payments\PaymentAcceptorDecorator;
use Modules\Payments\PaymentStatus;
use Repositories\Orm\TransactionRepository;
use Test_Unit;
use Wrappers\Decorators\ConfigContract;
use Repositories\Orm\WhitelabelPaymentMethodRepository;

class AstroFacadeTest extends Test_Unit
{
    private ConfigContract $config;
    private PaymentAcceptorDecorator $acceptorDecorator;
    private AstroCheckoutUrlHandler $depositClient;
    private TransactionRepository $transactionRepository;
    private AstroStatusHandler $statusHandler;
    private WhitelabelPaymentMethodRepository $whitelabelPaymentMethodRepository;

    private AstroFacade $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->config = $this->createMock(ConfigContract::class);
        $this->acceptorDecorator = $this->createMock(PaymentAcceptorDecorator::class);
        $this->depositClient = $this->createMock(AstroCheckoutUrlHandler::class);
        $this->transactionRepository = $this->createMock(TransactionRepository::class);
        $this->statusHandler = $this->createMock(AstroStatusHandler::class);
        $this->whitelabelPaymentMethodRepository = $this->createMock(WhitelabelPaymentMethodRepository::class);
        $this->service = new AstroFacade($this->config, $this->acceptorDecorator, $this->transactionRepository, $this->depositClient, $this->statusHandler, $this->whitelabelPaymentMethodRepository);
    }

    /** @test */
    public function requestCheckoutUrl__invokes_url_handler(): void
    {
        // Given
        $token = 'token';
        $amount = 20.0;
        $currency = 'USD';
        $whitelabelId = 1;

        $expected = Faker::forge()->url();

        $this->depositClient
            ->expects($this->once())
            ->method('processPayment')
            ->with($token, $whitelabelId, $amount, $currency)
            ->willReturn($expected);

        // When
        $actual = $this->service->requestCheckoutUrl($token, $whitelabelId, $amount, $currency);

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getPaymentStatus__invokes_handler(): void
    {
        // Given
        $expected = PaymentStatus::PENDING();
        $transactionPrefixedToken = 'abc123';
        $whitelabelId = 1;

        $this->statusHandler
            ->expects($this->once())
            ->method('getStatus')
            ->with($transactionPrefixedToken, $whitelabelId)
            ->willReturn($expected);

        // When
        $actual = $this->service->getPaymentStatus($transactionPrefixedToken, $whitelabelId);

        // Then
        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function getCustomizableOptions__returns_array(): void
    {
        // Given
        $expected = [
            'astro_base_url', 'astro_api_key', 'astro_secret_key', 'astro_default_country'
        ];

        // When
        $actual = $this->service->getCustomizableOptions();

        // Then
        $this->assertSame($expected, $actual);
    }
}
