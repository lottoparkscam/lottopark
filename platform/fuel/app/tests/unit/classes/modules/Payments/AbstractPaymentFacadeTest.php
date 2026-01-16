<?php

namespace Unit\Modules\Payments;

use Models\WhitelabelTransaction;
use Models\WhitelabelPaymentMethod;
use Modules\Payments\AbstractPaymentFacade;
use Modules\Payments\PaymentAcceptorDecorator;
use Modules\Payments\PaymentStatus;
use Repositories\Orm\TransactionRepository;
use Repositories\Orm\WhitelabelPaymentMethodRepository;
use Test_Unit;
use Wrappers\Decorators\ConfigContract;

class AbstractPaymentFacadeTest extends Test_Unit
{
    private ConfigContract $config;
    private PaymentAcceptorDecorator $acceptorDecorator;
    private TransactionRepository $transactionRepository;
    private WhitelabelPaymentMethodRepository $whitelabelPaymentMethodRepository;

    private AbstractPaymentFacade $facade;

    public function setUp(): void
    {
        parent::setUp();

        $this->config = $this->createMock(ConfigContract::class);
        $this->acceptorDecorator = $this->createMock(PaymentAcceptorDecorator::class);
        $this->transactionRepository = $this->createMock(TransactionRepository::class);
        $this->whitelabelPaymentMethodRepository = $this->createMock(WhitelabelPaymentMethodRepository::class);

        $this->facade = $this->getMockForAbstractClass(AbstractPaymentFacade::class, [
            $this->config,
            $this->acceptorDecorator,
            $this->transactionRepository,
            $this->whitelabelPaymentMethodRepository
        ]);
    }

    /** @test */
    public function getConfig__no_fields__returns_all_config_data(): void
    {
        // Given
        $fields = [];
        $expectedConfigData = ['field1' => 1, 'field2' => 2];

        $this->config
            ->expects($this->once())
            ->method('get')
            ->willReturn($expectedConfigData);

        // When
        $actual = $this->facade->getConfig($fields);

        // Then
        $this->assertSame($expectedConfigData, $actual);
    }

    /** @test */
    public function getConfig__specifics_fields_provided__returns_config_data_limited_to_fields(): void
    {
        // Given
        $fields = ['field1'];
        $configData = ['field1' => 1, 'field2' => 2];
        $expectedConfigData = ['field1' => 1];

        $this->config
            ->expects($this->once())
            ->method('get')
            ->willReturn($configData);

        // When
        $actual = $this->facade->getConfig($fields);

        // Then
        $this->assertSame($expectedConfigData, $actual);
    }

    /** @test */
    public function getPaymentStatus__by_default_returns_unsupported(): void
    {
        // Given
        $expected = PaymentStatus::UNSUPPORTED();
        $transactionPrefixedToken = 'abc123';
        $whitelabelId = 1;

        // When
        $actual = $this->facade->getPaymentStatus($transactionPrefixedToken, $whitelabelId);

        // Then
        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function confirmPayment__calls_acceptor(): void
    {
        // Given
        $transactionPrefixedToken = 'abc123';
        $whitelabelId = 1;

        $this->acceptorDecorator
            ->expects($this->once())
            ->method('confirm')
            ->with($transactionPrefixedToken, $whitelabelId);

        // When
        $this->facade->confirmPayment($transactionPrefixedToken, $whitelabelId);
    }

    /** @test */
    public function failPayment__transaction_exists__set_status_as_error(): void
    {
        // Given
        $transactionPrefixedToken = 'abc123';
        $whitelabelId = 1;
        $transaction = $this->createMock(WhitelabelTransaction::class);
        $this->transactionRepository
            ->expects($this->once())
            ->method('getByToken')
            ->with($transactionPrefixedToken, $whitelabelId)
            ->willReturn($transaction);

        $this->transactionRepository
            ->expects($this->once())
            ->method('save')
            ->with($transaction);

        // When
        $this->facade->failPayment($transactionPrefixedToken, $whitelabelId);
    }

    /** @test */
    public function getWhitelabelPaymentConfig__no_data_json__returns_base_config(): void
    {
        // Given
        $baseConfig = ['field1' => 1];
        $expected = $baseConfig;

        $this->facade = $this->getMockForAbstractClass(AbstractPaymentFacade::class, [
            $this->config,
            $this->acceptorDecorator,
            $this->transactionRepository,
            $this->whitelabelPaymentMethodRepository
        ], '', true, true, true, ['getConfig']);

        $this->facade->method('getConfig')->willReturn($baseConfig);

        // When
        $actual = $this->facade->getWhitelabelPaymentConfig(1, 1);

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getWhitelabelPaymentConfig__data_json_exists__returns_base_config_merged_with_customizable_options(): void
    {
        // Given
        $baseConfig = ['field1' => 1, 'field2' => 'abc'];
        $customizableOptions = ['field1'];
        $expected = ['field1' => 123, 'field2' => 'abc'];
        $dataJson = ['field1' => 123, 'field2' => 'abc', 'field3' => 'cba'];
        $wlPaymentMethod = new WhitelabelPaymentMethod();
        $wlPaymentMethod->payment_method_id = 123;
        $wlPaymentMethod->data_json = $dataJson;

        $this->facade = $this->getMockForAbstractClass(AbstractPaymentFacade::class, [
            $this->config,
            $this->acceptorDecorator,
            $this->transactionRepository,
            $this->whitelabelPaymentMethodRepository
        ], '', true, true, true, ['getConfig', 'getCustomizableOptions', 'getWhitelabelPaymentConfig']);

        $this->facade->method('getConfig')->willReturn($baseConfig);
        $this->facade->method('getCustomizableOptions')->willReturn($customizableOptions);
        $this->facade->method('getWhitelabelPaymentConfig')->willReturn($expected);

        // When
        $actual = $this->facade->getWhitelabelPaymentConfig($wlPaymentMethod->payment_method_id, 1);
        // Then
        $this->assertSame($expected, $actual);
    }
}
