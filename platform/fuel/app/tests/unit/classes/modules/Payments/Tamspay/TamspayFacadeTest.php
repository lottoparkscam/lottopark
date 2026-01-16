<?php

namespace Unit\Modules\Payments\Tamspay;

use Fuel\Tasks\Factory\Utils\Faker;
use Modules\Payments\PaymentAcceptorDecorator;
use Modules\Payments\PaymentStatus;
use Modules\Payments\Tamspay\TamspayCheckoutUrlHandler;
use Modules\Payments\Tamspay\TamspayFacade;
use Repositories\Orm\TransactionRepository;
use Repositories\Orm\WhitelabelPaymentMethodRepository;
use Test_Unit;
use Wrappers\Decorators\ConfigContract;

class TamspayFacadeTest extends Test_Unit
{
    private ConfigContract $config;
    private PaymentAcceptorDecorator $acceptorDecorator;
    private TamspayCheckoutUrlHandler $checkoutUrlHandler;
    private TransactionRepository $transactionRepository;
    private WhitelabelPaymentMethodRepository $whitelabelPaymentRepository;

    private TamspayFacade $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->config = $this->createMock(ConfigContract::class);
        $this->acceptorDecorator = $this->createMock(PaymentAcceptorDecorator::class);
        $this->checkoutUrlHandler = $this->createMock(TamspayCheckoutUrlHandler::class);
        $this->transactionRepository = $this->createMock(TransactionRepository::class);
        $this->whitelabelPaymentRepository = $this->createMock(WhitelabelPaymentMethodRepository::class);
        $this->service = new TamspayFacade($this->config, $this->acceptorDecorator, $this->checkoutUrlHandler, $this->transactionRepository, $this->whitelabelPaymentRepository);
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

        $this->checkoutUrlHandler
            ->expects($this->once())
            ->method('processPayment')
            ->with($token, $whitelabelId)
            ->willReturn($expected);

        // When
        $actual = $this->service->requestCheckoutUrl($token, $whitelabelId, $amount, $currency);

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getPaymentStatus__returns_unsupported(): void
    {
        // Given
        $expected = PaymentStatus::UNSUPPORTED();

        // When
        $actual = $this->service->getPaymentStatus('asd', 1);

        // Then
        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function getCustomizableOptions__returns_array(): void
    {
        // Given
        $expected = ['tamspay_sid'];

        // When
        $actual = $this->service->getCustomizableOptions();

        // Then
        $this->assertSame($expected, $actual);
    }
}
