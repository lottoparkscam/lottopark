<?php

namespace Unit\Modules\Payments\Trustpayments;

use Fuel\Tasks\Factory\Utils\Faker;
use Models\WhitelabelTransaction;
use Modules\Payments\Trustpayments\TrustpaymentsCheckoutUrlHandler;
use Orm\RecordNotFound;
use Repositories\Orm\TransactionRepository;
use RuntimeException;
use Test_Unit;
use Wrappers\Decorators\ConfigContract;

class TrustpaymentsCheckoutUrlHandlerTest extends Test_Unit
{
    private TransactionRepository $repo;
    private ConfigContract $config;

    private TrustpaymentsCheckoutUrlHandler $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->repo = $this->createMock(TransactionRepository::class);
        $this->config = $this->createMock(ConfigContract::class);
        $this->service = new TrustpaymentsCheckoutUrlHandler($this->repo, $this->config);
    }

    /** @test */
    public function processPayment__not_existing_transaction__throws_runtime_exception(): void
    {
        // Except
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Transaction #dsdsa not found!');

        // Given
        $token = 'dsdsa';
        $whitelabelId = 1;

        $this->repo
            ->expects($this->once())
            ->method('getByToken')
            ->with($token, $whitelabelId)
            ->willThrowException(new RecordNotFound('Some message'));

        // When
        $this->service->processPayment($token, $whitelabelId);
    }

    /** @test */
    public function processPayment__returns_full_url_based_on_config(): void
    {
        // Given
        $token = 'dsdsa';
        $whitelabelId = 1;

        $trustpaymentsUrl = 'process/payments/details';
        $baseUrl = Faker::forge()->url();
        $expected = $baseUrl . $trustpaymentsUrl;

        $this->repo
            ->expects($this->once())
            ->method('getByToken')
            ->with($token, $whitelabelId)
            ->willReturn(new WhitelabelTransaction());

        $this->config
            ->expects($this->once())
            ->method('get')
            ->with('payments.trustpayments.base_url')
            ->willReturn($baseUrl);

        // When
        $actual = $this->service->processPayment($token, $whitelabelId);

        // Then
        $this->assertSame($expected, $actual);
    }
}
