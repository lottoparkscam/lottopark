<?php

namespace classes\modules\Payments\Astro;

use Fuel\Tasks\Factory\Utils\Faker;
use Models\WhitelabelUser;
use Modules\Payments\Astro\AstroCheckoutUrlHandler;
use Modules\Payments\Astro\Client\AstroDepositClient;
use Modules\Payments\PaymentUrlHelper;
use Repositories\Orm\TransactionRepository;
use Services\Shared\Logger\LoggerContract;
use Test_Unit;
use Wrappers\Decorators\ConfigContract;

class AstroCheckoutUrlHandlerTest extends Test_Unit
{
    private AstroDepositClient $payClient;
    private LoggerContract $logger;
    private TransactionRepository $repo;
    private PaymentUrlHelper $urlHelper;
    private ConfigContract $config;

    private AstroCheckoutUrlHandler $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->payClient = $this->createMock(AstroDepositClient::class);
        $this->logger = $this->createMock(LoggerContract::class);
        $this->repo = $this->createMock(TransactionRepository::class);
        $this->urlHelper = $this->createMock(PaymentUrlHelper::class);
        $this->config = $this->createMock(ConfigContract::class);

        $this->service = new AstroCheckoutUrlHandler(
            $this->payClient,
            $this->repo,
            $this->logger,
            $this->urlHelper,
            $this->config
        );
    }

    /** @test */
    public function processPayment__country_provided__uses_country(): void
    {
        // Given
        $transactionPrefixedToken = 'ABC123';
        $amount = 20.10;
        $currencyCode = 'EUR';
        $country = 'UY';
        $expectedCountry = $country;
        $transaction = $this->get_ticket()->transaction;
        $confirmationUrl = Faker::forge()->url();
        $returnUrl = Faker::forge()->url();
        $responseUrl = Faker::forge()->url();

        $user = ['merchant_user_id' => $transaction->whitelabel_user_id];

        $mcc = 123;

        $this->config
            ->expects($this->once())
            ->method('get')
            ->with('payments.astro.mcc')
            ->willReturn($mcc);

        $product = [
            'mcc' => $mcc,
            'merchant_code' => 'Gambling',
            'description' => 'Gambling ticket',
        ];

        $this->urlHelper
            ->expects($this->once())
            ->method('getConfirmationUrl')
            ->with($transaction)
            ->willReturn($confirmationUrl);

        $this->urlHelper
            ->expects($this->once())
            ->method('getResultUrl')
            ->with($transaction)
            ->willReturn($returnUrl);

        $depositExternalId = '123';
        $responseData = [
            'url' => $responseUrl,
            'deposit_external_id' => $depositExternalId
        ];
        $response = $this->mockResponse($responseData);

        $expectedServiceResponse = $responseUrl;

        $this->payClient
            ->expects($this->once())
            ->method('request')
            ->with(
                $transaction,
                $amount,
                $currencyCode,
                $expectedCountry,
                $user,
                $product,
                $confirmationUrl,
                $returnUrl
            )
            ->willReturn($response);

        $this->repo
            ->expects($this->once())
            ->method('getByToken')
            ->with($transactionPrefixedToken, $transaction->whitelabel_id)
            ->willReturn($transaction);

        $this->repo
            ->expects($this->once())
            ->method('save')
            ->with($transaction);

        // When
        $actual = $this->service->processPayment($transactionPrefixedToken, $transaction->whitelabel_id, $amount, $currencyCode, $country);
        $this->assertSame($actual, $expectedServiceResponse);

        // Then
        $transactionData = $transaction->to_array();
        $this->assertSame($transactionData['additional_data_json'], '{"deposit_external_id":"123"}');
    }

    /** @test */
    public function processPayment__country_not_provided_user_has_country__uses_user_country(): void
    {
        // Given
        $transactionPrefixedToken = 'ABC123';
        $amount = 20.10;
        $currencyCode = 'EUR';
        $country = null;
        $userCountry = 'UY';
        $expectedCountry = $userCountry;
        $transaction = $this->get_ticket()->transaction;
        $transaction->user = new WhitelabelUser(['country' => $userCountry]);
        $confirmationUrl = Faker::forge()->url();
        $returnUrl = Faker::forge()->url();
        $responseUrl = Faker::forge()->url();

        $user = ['merchant_user_id' => $transaction->whitelabel_user_id];

        $mcc = 123;

        $this->config
            ->expects($this->once())
            ->method('get')
            ->with('payments.astro.mcc')
            ->willReturn($mcc);

        $product = [
            'mcc' => $mcc,
            'merchant_code' => 'Gambling',
            'description' => 'Gambling ticket',
        ];

        $this->urlHelper
            ->expects($this->once())
            ->method('getConfirmationUrl')
            ->with($transaction)
            ->willReturn($confirmationUrl);

        $this->urlHelper
            ->expects($this->once())
            ->method('getResultUrl')
            ->with($transaction)
            ->willReturn($returnUrl);

        $depositExternalId = '123';
        $responseData = [
            'url' => $responseUrl,
            'deposit_external_id' => $depositExternalId
        ];
        $response = $this->mockResponse($responseData);

        $this->payClient
            ->expects($this->once())
            ->method('request')
            ->with(
                $transaction,
                $amount,
                $currencyCode,
                $expectedCountry,
                $user,
                $product,
                $confirmationUrl,
                $returnUrl
            )
            ->willReturn($response);

        $this->repo
            ->expects($this->once())
            ->method('getByToken')
            ->with($transactionPrefixedToken, $transaction->whitelabel_id)
            ->willReturn($transaction);

        $this->repo
            ->expects($this->once())
            ->method('save')
            ->with($transaction);

        // When
        $this->service->processPayment($transactionPrefixedToken, $transaction->whitelabel_id, $amount, $currencyCode, $country);

        // Then
        $transactionData = $transaction->to_array();
        $this->assertSame($transactionData['additional_data_json'], '{"deposit_external_id":"123"}');
    }

    /** @test */
    public function processPayment__country_not_provided_user_has_no_country__uses_config_country(): void
    {
        // Given
        $transactionPrefixedToken = 'ABC123';
        $amount = 20.10;
        $currencyCode = 'EUR';
        $country = null;
        $userCountry = null;
        $configCountry = 'UY';
        $expectedCountry = $configCountry;
        $transaction = $this->get_ticket()->transaction;
        $transaction->user = new WhitelabelUser(['country' => $userCountry]);
        $confirmationUrl = Faker::forge()->url();
        $returnUrl = Faker::forge()->url();
        $responseUrl = Faker::forge()->url();

        $user = ['merchant_user_id' => $transaction->whitelabel_user_id];

        $mcc = 123;

        $this->config
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['payments.astro.astro_default_country'], ['payments.astro.mcc'])
            ->willReturnOnConsecutiveCalls($configCountry, $mcc);

        $product = [
            'mcc' => $mcc,
            'merchant_code' => 'Gambling',
            'description' => 'Gambling ticket',
        ];

        $this->urlHelper
            ->expects($this->once())
            ->method('getConfirmationUrl')
            ->with($transaction)
            ->willReturn($confirmationUrl);

        $this->urlHelper
            ->expects($this->once())
            ->method('getResultUrl')
            ->with($transaction)
            ->willReturn($returnUrl);

        $depositExternalId = '123';
        $responseData = [
            'url' => $responseUrl,
            'deposit_external_id' => $depositExternalId
        ];
        $response = $this->mockResponse($responseData);

        $this->payClient
            ->expects($this->once())
            ->method('request')
            ->with(
                $transaction,
                $amount,
                $currencyCode,
                $expectedCountry,
                $user,
                $product,
                $confirmationUrl,
                $returnUrl
            )
            ->willReturn($response);

        $this->repo
            ->expects($this->once())
            ->method('getByToken')
            ->with($transactionPrefixedToken, $transaction->whitelabel_id)
            ->willReturn($transaction);

        $this->repo
            ->expects($this->once())
            ->method('save')
            ->with($transaction);

        // When
        $this->service->processPayment($transactionPrefixedToken, $transaction->whitelabel_id, $amount, $currencyCode, $country);

        // Then
        $transactionData = $transaction->to_array();
        $this->assertSame($transactionData['additional_data_json'], '{"deposit_external_id":"123"}');
    }
}
