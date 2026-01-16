<?php

namespace Unit\Modules\Payments;

use Models\WhitelabelTransaction;
use Models\PaymentMethod;
use Models\WhitelabelPaymentMethod;
use Modules\Payments\PaymentFacadeContract;
use Modules\Payments\PaymentFacadeLocator;
use Modules\Payments\PaymentUrlHelper;
use Repositories\Orm\TransactionRepository;
use Repositories\Orm\WhitelabelPaymentMethodRepository;
use Services\Shared\System;
use Test_Unit;

class PaymentUrlHelperTest extends Test_Unit
{
    private const BASE_DOMAIN = 'http://somedomain.com';

    private System $system;
    private WhitelabelPaymentMethodRepository $repo;
    private TransactionRepository $transactionRepository;
    private PaymentFacadeLocator $locator;

    private PaymentUrlHelper $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->system = $this->createMock(System::class);
        $this->repo = $this->createMock(WhitelabelPaymentMethodRepository::class);
        $this->transactionRepository = $this->createMock(TransactionRepository::class);
        $this->locator = $this->createMock(PaymentFacadeLocator::class);
        $this->service = new PaymentUrlHelper($this->system, $this->repo, $this->transactionRepository, $this->locator);
    }

    /**
     * @test
     * @dataProvider baseUrl_dataProvider
     * @param string $baseUrl
     */
    public function getConfirmationUrl__transaction__returns_url_with_token_and_out_hash(string $baseUrl): void
    {
        // Given
        $transactionOutHash = 'asd123sad123sad'; # $out
        $helperRouteOrderConfirmUrl = '/order/confirm';
        $paymentMethodSlug = 'slug';
        $whitelabelPaymentMethodId = 1;
        $config['slug'] = $paymentMethodSlug;
        $token = '123';

        $transaction = new WhitelabelTransaction();
        $transaction->whitelabel_payment_method_id = $whitelabelPaymentMethodId;
        $transaction->token = $token;
        $transaction->transaction_out_id = $transactionOutHash;

        $expected = vsprintf('%s%s/%s/%d/?token=%s&out=%s', [
            self::BASE_DOMAIN,
            $helperRouteOrderConfirmUrl,
            $paymentMethodSlug,
            $whitelabelPaymentMethodId,
            $transaction->prefixed_token,
            $transactionOutHash
        ]);

        $method = new WhitelabelPaymentMethod();
        $method->payment_method = new PaymentMethod(['name' => 'Slug']);
        $method->payment_method_id = $whitelabelPaymentMethodId;

        $facade = $this->createMock(PaymentFacadeContract::class);
        $facade
            ->expects($this->once())
            ->method('getConfig')
            ->willReturn($config);

        # locator
        $this->locator
            ->expects($this->once())
            ->method('getById')
            ->with($method->payment_method_id)
            ->willReturn($facade);

        # repo
        $this->repo
            ->expects($this->once())
            ->method('getById')
            ->with($whitelabelPaymentMethodId, ['payment_method'])
            ->willReturn($method);

        # system
        $this->system
            ->expects($this->once())
            ->method('baseFullUrl')
            ->willReturn($baseUrl);

        // When
        $actual = $this->service->getConfirmationUrl($transaction);

        // Then
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     * @dataProvider baseUrl_dataProvider
     * @param string $baseUrl
     */
    public function getSuccessUrl__returns_combination_of_base_url_and_helper_success_url(string $baseUrl): void
    {
        // Given
        $helperOrderSuccessUrl = '/order/success/';
        $expected = self::BASE_DOMAIN . $helperOrderSuccessUrl;

        $this->system
            ->expects($this->once())
            ->method('baseFullUrl')
            ->willReturn($baseUrl);

        // When
        $actual = $this->service->getSuccessUrl();

        // Then
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     * @dataProvider baseUrl_dataProvider
     * @param string $baseUrl
     */
    public function getFailureUrl__returns_combination_of_base_url_and_helper_success_url(string $baseUrl): void
    {
        // Given
        $helperOrderSuccessUrl = '/order/failure/';
        $expected = self::BASE_DOMAIN . $helperOrderSuccessUrl;

        $this->system
            ->expects($this->once())
            ->method('baseFullUrl')
            ->willReturn($baseUrl);

        // When
        $actual = $this->service->getFailureUrl();

        // Then
        $this->assertSame($expected, $actual);
    }

    public function baseUrl_dataProvider(): array
    {
        return [
            'with slash at the end' => [self::BASE_DOMAIN . '/'],
            'without slash at the end' => [self::BASE_DOMAIN],
        ];
    }
}
