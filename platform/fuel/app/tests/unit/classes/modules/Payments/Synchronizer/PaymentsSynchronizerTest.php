<?php

namespace Unit\Modules\Payments\Synchronizer;

use Exception;
use Generator;
use DateTimeImmutable;
use Models\Whitelabel;
use Models\SynchronizerLog;
use Models\WhitelabelTransaction;
use Modules\Payments\PaymentFacadeLocator;
use Modules\Payments\PaymentFacadeContract;
use Modules\Payments\PaymentStatus;
use Modules\Payments\Synchronizer\PaymentsSynchronizer;
use Repositories\Orm\TransactionRepository;
use Repositories\SynchronizerLogRepository;
use Services\Logs\FileLoggerService;
use Services\Shared\System;
use Test_Unit;
use Wrappers\Decorators\ConfigContract;

class PaymentsSynchronizerTest extends Test_Unit
{
    private TransactionRepository $transactionRepository;
    private SynchronizerLogRepository $synchronizerLogRepository;
    private System $system;
    private ConfigContract $config;
    private PaymentsSynchronizer $service;
    private PaymentFacadeLocator $serviceLocator;
    private FileLoggerService $fileLoggerService;

    public function setUp(): void
    {
        parent::setUp();
        $this->transactionRepository = $this->createMock(TransactionRepository::class);
        $this->system = $this->createMock(System::class);
        $this->config = $this->createMock(ConfigContract::class);
        $this->serviceLocator = $this->createMock(PaymentFacadeLocator::class);
        $this->synchronizerLogRepository = $this->createMock(SynchronizerLogRepository::class);
        $this->fileLoggerService = $this->createMock(FileLoggerService::class);

        $this->service = new PaymentsSynchronizer(
            $this->transactionRepository,
            $this->system,
            $this->config,
            $this->serviceLocator,
            $this->synchronizerLogRepository,
            $this->fileLoggerService
        );
    }

    /** @test */
    public function synchronize__disabled_in_config__skips(): void
    {
        // Given
        $this->config
            ->method('get')
            ->willReturn(false);

        $this->transactionRepository
            ->expects($this->never())
            ->method('getPendingTransaction');

        // When
        $this->service->synchronize();
    }

    /** @test */
    public function synchronize__no_transactions__skips(): void
    {
        // Given
        $this->createConfig();

        $date = new DateTimeImmutable();

        $this->system
            ->method('date')
            ->willReturn($date);

        $this->transactionRepository
            ->expects($this->once())
            ->method('getPendingTransaction')
            ->willReturn([]);

        // When
        $this->service->synchronize();
    }

    /** @test */
    public function synchronize__prepares_dates_by_config(): void
    {
        // Given
        $isEnabled = true;
        $retryInterval = 3;
        $minimalAge = 15;
        $maxAge = '7 days';
        $limit = 10;
        $maxAttempts = 10;

        $this->createConfig($isEnabled, $retryInterval, $minimalAge, $maxAge, $limit, $maxAttempts);

        $date = new DateTimeImmutable();

        $this->system
            ->method('date')
            ->willReturn($date);

        # parsed dates
        $lastAttemptDate = $this->system->date()->modify("- $retryInterval minutes");
        $dateTo = $this->system->date()->modify("- $minimalAge minutes");
        $dateFrom = $this->system->date()->modify("- $maxAge");

        // When
        $this->transactionRepository
            ->expects($this->once())
            ->method('getPendingTransaction')
            ->with(
                $dateFrom,
                $dateTo,
                $lastAttemptDate,
                $limit
            )
            ->willReturn([]);

        $this->service->synchronize();
    }

    /** @test */
    public function synchronize__no_transaction_payment_facade__skips(): void
    {
        // Given
        $paymentSlug = 'slug';
        $this->createConfig();

        $date = new DateTimeImmutable();

        $this->system
            ->method('date')
            ->willReturn($date);

        $transaction = $this->createPartialMock(
            WhitelabelTransaction::class,
            ['get_payment_method_slug_attribute']
        );

        $transaction->id = 1;
        $transaction->whitelabel_id = 1;
        $transaction->whitelabel = new Whitelabel(['id' => 1, 'prefix' => Whitelabel::LOTTOPARK_PREFIX]);

        $transaction
            ->method('get_payment_method_slug_attribute')
            ->willReturn($paymentSlug);

        $this->transactionRepository
            ->method('getPendingTransaction')
            ->willReturn([$transaction]);

        $this->serviceLocator
            ->expects($this->once())
            ->method('hasFacade')
            ->willReturn(false);

        $facade = $this->createMock(PaymentFacadeContract::class);
        $facade
            ->expects($this->never())
            ->method('getPaymentStatus');

        // When
        $this->service->synchronize();
    }

    /** @test */
    public function synchronize__transaction_has_payment_facade__getPaymentStatus(): void
    {
        // Given
        $paymentSlug = 'slug';
        $this->createConfig();

        $date = new DateTimeImmutable();

        $this->system
            ->method('date')
            ->willReturn($date);

        $transaction = $this->createPartialMock(
            WhitelabelTransaction::class,
            ['get_payment_method_slug_attribute']
        );
        $transaction->token = 123;
        $transaction->id = 1;
        $transaction->whitelabel_id = 1;
        $transaction->whitelabel = new Whitelabel(['id' => 1, 'prefix' => Whitelabel::LOTTOPARK_PREFIX]);

        $transaction
            ->expects($this->once())
            ->method('get_payment_method_slug_attribute')
            ->willReturn($paymentSlug);

        $this->transactionRepository
            ->expects($this->once())
            ->method('getPendingTransaction')
            ->willReturn([$transaction]);

        $this->serviceLocator
            ->expects($this->once())
            ->method('hasFacade')
            ->with($paymentSlug)
            ->willReturn(true);

        $facade = $this->createMock(PaymentFacadeContract::class);
        $facade
            ->expects($this->once())
            ->method('getPaymentStatus')
            ->with($transaction->prefixed_token);

        $this->serviceLocator
            ->expects($this->once())
            ->method('getBySlug')
            ->with($paymentSlug)
            ->willReturn($facade);

        // When
        $this->service->synchronize();
    }

    /** @test */
    public function synchronize__any_errors__nothing_is_thrown_and_is_logged(): void
    {
        // Given
        $exception = new Exception('Some error');
        $this->createConfig();

        $date = new DateTimeImmutable();

        $this->system
            ->method('date')
            ->willReturn($date);

        $transaction = $this->createPartialMock(
            WhitelabelTransaction::class,
            ['get_payment_method_slug_attribute']
        );

        $this->transactionRepository
            ->method('getPendingTransaction')
            ->willReturn([$transaction]);

        $this->serviceLocator
            ->method('hasFacade')
            ->willThrowException($exception);

        $this->serviceLocator
            ->expects($this->never())
            ->method('getBySlug');

        $this->service->synchronize();
    }

    /** @test */
    public function synchronize__facade_status_is_paid__confirms_payment(): void
    {
        // Given
        $paymentSlug = 'slug';
        $this->createConfig();

        $date = new DateTimeImmutable();

        $this->system
            ->method('date')
            ->willReturn($date);

        $transaction = $this->createPartialMock(
            WhitelabelTransaction::class,
            ['get_payment_method_slug_attribute']
        );
        $transaction->token = 123;
        $transaction->id = 1;
        $transaction->whitelabel_id = 1;
        $transaction->whitelabel = new Whitelabel(['id' => 1, 'prefix' => Whitelabel::LOTTOPARK_PREFIX]);

        $transaction
            ->expects($this->once())
            ->method('get_payment_method_slug_attribute')
            ->willReturn($paymentSlug);

        $this->transactionRepository
            ->expects($this->once())
            ->method('getPendingTransaction')
            ->willReturn([$transaction]);

        $this->serviceLocator
            ->expects($this->once())
            ->method('hasFacade')
            ->with($paymentSlug)
            ->willReturn(true);

        $status = PaymentStatus::PAID();
        $facade = $this->createMock(PaymentFacadeContract::class);
        $facade
            ->expects($this->once())
            ->method('getPaymentStatus')
            ->with($transaction->prefixed_token)
            ->willReturn($status);
        $facade
            ->expects($this->once())
            ->method('confirmPayment')
            ->with($transaction->prefixed_token);

        $this->serviceLocator
            ->expects($this->once())
            ->method('getBySlug')
            ->with($paymentSlug)
            ->willReturn($facade);

        $this->synchronizerLogRepository
            ->expects($this->exactly(2))
            ->method('addLog')
            ->withConsecutive(
                [$transaction->whitelabel_id, $transaction->id, SynchronizerLog::TYPE_INFO, sprintf('SYNCHRONIZER | Attempting #%d to sync transaction: #%s', $transaction->payment_attempts_count + 1, $transaction->prefixed_token)],
                [$transaction->whitelabel_id, $transaction->id, SynchronizerLog::TYPE_SUCCESS, sprintf('SYNCHRONIZER | Transaction #%s status changed to confirmed', $transaction->prefixed_token)]
            );

        // When
        $this->service->synchronize();
    }

    /**
     * @test
     * @dataProvider provideStatusesWithoutAnyHandler
     */
    public function synchronize__facade_status_is_pending_or_unsupported__increases_attempt(PaymentStatus $status): void
    {
        // Given
        $paymentSlug = 'slug';
        $this->createConfig();

        $date = new DateTimeImmutable();

        $this->system
            ->method('date')
            ->willReturn($date);

        $transaction = $this->createPartialMock(
            WhitelabelTransaction::class,
            ['get_payment_method_slug_attribute']
        );
        $transaction->token = 123;
        $transaction->id = 1;
        $transaction->whitelabel_id = 1;
        $transaction->whitelabel = new Whitelabel(['id' => 1, 'prefix' => Whitelabel::LOTTOPARK_PREFIX]);

        $transaction
            ->expects($this->once())
            ->method('get_payment_method_slug_attribute')
            ->willReturn($paymentSlug);

        $this->transactionRepository
            ->expects($this->once())
            ->method('getPendingTransaction')
            ->willReturn([$transaction]);

        $this->serviceLocator
            ->expects($this->once())
            ->method('hasFacade')
            ->with($paymentSlug)
            ->willReturn(true);

        $facade = $this->createMock(PaymentFacadeContract::class);
        $facade
            ->expects($this->once())
            ->method('getPaymentStatus')
            ->with($transaction->prefixed_token)
            ->willReturn($status);
        $facade
            ->expects($this->never())
            ->method('confirmPayment');
        $facade
            ->expects($this->never())
            ->method('failPayment');

        $this->transactionRepository->expects($this->once())
            ->method('attemptPayment')
            ->with($transaction->prefixed_token, $transaction->whitelabel_id);

        $this->serviceLocator
            ->expects($this->once())
            ->method('getBySlug')
            ->with($paymentSlug)
            ->willReturn($facade);

        $this->synchronizerLogRepository
            ->expects($this->exactly(2))
            ->method('addLog')
            ->withConsecutive(
                [$transaction->whitelabel_id, $transaction->id, SynchronizerLog::TYPE_INFO, sprintf('SYNCHRONIZER | Attempting #%d to sync transaction: #%s', $transaction->payment_attempts_count + 1, $transaction->prefixed_token)],
                [$transaction->whitelabel_id, $transaction->id, SynchronizerLog::TYPE_INFO, sprintf('SYNCHRONIZER | Transaction #%s attempted to handle status, but no explicit success / failure recognized', $transaction->prefixed_token)]
            );

        // When
        $this->service->synchronize();
    }

    /** @test */
    public function synchronize__facade_status_is_failed__fails_payment(): void
    {
        // Given payment with failed status
        $status = PaymentStatus::FAILED();
        $paymentSlug = 'slug';
        $this->createConfig();

        $date = new DateTimeImmutable();

        $this->system
            ->method('date')
            ->willReturn($date);

        $transaction = $this->createPartialMock(
            WhitelabelTransaction::class,
            ['get_payment_method_slug_attribute']
        );
        $transaction->token = 123;
        $transaction->id = 1;
        $transaction->whitelabel_id = 1;
        $transaction->whitelabel = new Whitelabel(['id' => 1, 'prefix' => Whitelabel::LOTTOPARK_PREFIX]);

        $transaction
            ->expects($this->once())
            ->method('get_payment_method_slug_attribute')
            ->willReturn($paymentSlug);

        $this->transactionRepository
            ->expects($this->once())
            ->method('getPendingTransaction')
            ->willReturn([$transaction]);

        $this->serviceLocator
            ->expects($this->once())
            ->method('hasFacade')
            ->with($paymentSlug)
            ->willReturn(true);

        $facade = $this->createMock(PaymentFacadeContract::class);
        $facade
            ->expects($this->once())
            ->method('getPaymentStatus')
            ->with($transaction->prefixed_token)
            ->willReturn($status);
        $facade
            ->expects($this->once())
            ->method('failPayment')
            ->with($transaction->prefixed_token, $transaction->whitelabel_id);

        $this->serviceLocator
            ->expects($this->once())
            ->method('getBySlug')
            ->with($paymentSlug)
            ->willReturn($facade);

        $this->synchronizerLogRepository
            ->expects($this->exactly(2))
            ->method('addLog')
            ->withConsecutive(
                [$transaction->whitelabel_id, $transaction->id, SynchronizerLog::TYPE_INFO, sprintf(
                    'SYNCHRONIZER | Attempting #%d to sync transaction: #%s',
                    $transaction->payment_attempts_count + 1,
                    $transaction->prefixed_token
                )],
                [$transaction->whitelabel_id, $transaction->id, SynchronizerLog::TYPE_INFO,  sprintf(
                    'SYNCHRONIZER | Transaction #%s status changed to failed due resolved status was #%s',
                    $transaction->prefixed_token,
                    $status
                )]
            );

        // When
        $this->service->synchronize();
    }

    /** @test */
    public function synchronize_FacadeStatusIsCorrupted_FailsPayment(): void
    {
        // Given payment with failed status
        $status = PaymentStatus::CORRUPTED();
        $paymentSlug = 'slug';
        $this->createConfig();

        $date = new DateTimeImmutable();

        $this->system
            ->method('date')
            ->willReturn($date);

        $transaction = $this->createPartialMock(
            WhitelabelTransaction::class,
            ['get_payment_method_slug_attribute']
        );
        $transaction->token = 123;
        $transaction->id = 1;
        $transaction->whitelabel_id = 1;
        $transaction->whitelabel = new Whitelabel(['id' => 1, 'prefix' => Whitelabel::LOTTOPARK_PREFIX]);

        $transaction
            ->expects($this->once())
            ->method('get_payment_method_slug_attribute')
            ->willReturn($paymentSlug);

        $this->transactionRepository
            ->expects($this->once())
            ->method('getPendingTransaction')
            ->willReturn([$transaction]);

        $this->serviceLocator
            ->expects($this->once())
            ->method('hasFacade')
            ->with($paymentSlug)
            ->willReturn(true);

        $facade = $this->createMock(PaymentFacadeContract::class);
        $facade
            ->expects($this->once())
            ->method('getPaymentStatus')
            ->with($transaction->prefixed_token)
            ->willReturn($status);
        $facade
            ->expects($this->once())
            ->method('failPayment')
            ->with($transaction->prefixed_token);

        $this->serviceLocator
            ->expects($this->once())
            ->method('getBySlug')
            ->with($paymentSlug)
            ->willReturn($facade);

        $this->synchronizerLogRepository
            ->expects($this->exactly(2))
            ->method('addLog')
            ->withConsecutive(
                [$transaction->whitelabel_id, $transaction->id, SynchronizerLog::TYPE_INFO, sprintf(
                    'SYNCHRONIZER | Attempting #%d to sync transaction: #%s',
                    $transaction->payment_attempts_count + 1,
                    $transaction->prefixed_token
                )],
                [$transaction->whitelabel_id, $transaction->id, SynchronizerLog::TYPE_INFO,  sprintf(
                    'SYNCHRONIZER | Transaction #%s status changed to failed due resolved status was #%s',
                    $transaction->prefixed_token,
                    $status
                )]
            );

        // When
        $this->service->synchronize();
    }

    public function provideStatusesWithoutAnyHandler(): Generator
    {
        yield PaymentStatus::PENDING => [PaymentStatus::PENDING()];
        yield PaymentStatus::UNSUPPORTED => [PaymentStatus::UNSUPPORTED()];
    }

    private function createConfig(bool $isEnabled = true, int $retryInterval = 3, int $minimalAge = 15, string $maxAge = '7 days', int $limit = 10, $maxAttempts = 5): void
    {
        $this->config
            ->method('get')
            ->withConsecutive(
                ['payments.synchronizer.is_enabled'],
                ['payments.synchronizer.retry_interval_mins'],
                ['payments.synchronizer.minimal_age_mins'],
                ['payments.synchronizer.max_age'],
                ['payments.synchronizer.max_transactions_chunk'],
                ['payments.synchronizer.max_attempts'],
            )
            ->willReturnOnConsecutiveCalls($isEnabled, $retryInterval, $minimalAge, $maxAge, $limit, $maxAttempts);
    }
}
