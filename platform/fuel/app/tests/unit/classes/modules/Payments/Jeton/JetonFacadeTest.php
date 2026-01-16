<?php

namespace Unit\Services\Payments\Jeton;

use Modules\Payments\Jeton\JetonCheckoutUrlHandler;
use Modules\Payments\Jeton\JetonFacade;
use Modules\Payments\Jeton\JetonTransactionHandler;
use Modules\Payments\PaymentAcceptorDecorator;
use Repositories\Orm\TransactionRepository;
use Repositories\Orm\WhitelabelPaymentMethodRepository;
use Test_Unit;
use Wrappers\Decorators\ConfigContract;

class JetonFacadeTest extends Test_Unit
{
    private JetonCheckoutUrlHandler $checkoutUrlHandler;
    private JetonTransactionHandler $transactionHandler;
    private ConfigContract $config;
    private PaymentAcceptorDecorator $acceptorDecorator;
    private TransactionRepository $transactionRepository;
    private WhitelabelPaymentMethodRepository $whitelabelPaymentMethodRepository;

    private JetonFacade $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->checkoutUrlHandler = $this->createMock(JetonCheckoutUrlHandler::class);
        $this->transactionHandler = $this->createMock(JetonTransactionHandler::class);
        $this->config = $this->createMock(ConfigContract::class);
        $this->acceptorDecorator = $this->createMock(PaymentAcceptorDecorator::class);
        $this->transactionRepository = $this->createMock(TransactionRepository::class);
        $this->whitelabelPaymentMethodRepository = $this->createMock(WhitelabelPaymentMethodRepository::class);

        $this->service = new JetonFacade(
            $this->checkoutUrlHandler,
            $this->transactionHandler,
            $this->config,
            $this->acceptorDecorator,
            $this->transactionRepository,
            $this->whitelabelPaymentMethodRepository
        );
    }

    /** @test */
    public function requestCheckoutUrl__propagate_arguments(): void
    {
        // Given
        $transactionId = 1;
        $amount = 12.12;
        $language = 'EN';
        $currencyCode = 'USD';
        $whitelabelId = 1;

        $this->checkoutUrlHandler
            ->expects($this->once())
            ->method('processPayment')
            ->with(
                $transactionId,
                $whitelabelId,
                $amount,
                $currencyCode,
                $language
            )
            ->willReturn('url');

        // When
        $this->service->requestCheckoutUrl($transactionId, $whitelabelId, $amount, $currencyCode, $language);
    }

    /** @test */
    public function getPaymentStatus__propagate_arguments(): void
    {
        // Given
        $transactionId = 1;
        $whitelabelId = 1;

        $this->transactionHandler
            ->expects($this->once())
            ->method('getPaymentStatus')
            ->with($transactionId);

        // When
        $this->service->getPaymentStatus($transactionId, $whitelabelId);
    }

    /** @test */
    public function getCustomizableOptions__returns_selected_fields(): void
    {
        // Given
        $expected = ['jeton_base_url', 'jeton_api_key'];

        // When
        $actual = $this->service->getCustomizableOptions();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getConfig__invokes_config_contract(): void
    {
        // Given
        $slug = 'jeton';
        $expected = ['field'];

        $this->config
            ->expects($this->once())
            ->method('get')
            ->with("payments.$slug")
            ->willReturn($expected);

        // When
        $actual = $this->service->getConfig();

        // Then
        $this->assertSame($expected, $actual);
    }
}
