<?php

namespace Unit\Modules\Payments\Synchronizer;

use DateTimeImmutable;
use Exception;
use Helpers_General;
use Models\WhitelabelTransaction;
use Modules\Payments\Synchronizer\PaymentsCleaner;
use Repositories\CleanerLogRepository;
use Repositories\Orm\TransactionRepository;
use Services\Shared\System;
use Test_Unit;
use Wrappers\Decorators\ConfigContract;
use Models\Whitelabel;
use Models\WhitelabelUserTicket;
use Services\Logs\FileLoggerService;

class PaymentsCleanerTest extends Test_Unit
{
    private TransactionRepository $transactionRepository;
    private ConfigContract $config;
    private System $system;
    private PaymentsCleaner $service;
    private CleanerLogRepository $cleanerLogRepository;
    private FileLoggerService $fileLoggerService;

    public function setUp(): void
    {
        parent::setUp();
        $this->transactionRepository = $this->createMock(TransactionRepository::class);
        $this->system = $this->createMock(System::class);
        $this->config = $this->createMock(ConfigContract::class);
        $this->cleanerLogRepository = $this->createMock(CleanerLogRepository::class);
        $this->fileLoggerService = $this->createMock(FileLoggerService::class);

        $this->service = new PaymentsCleaner(
            $this->transactionRepository,
            $this->config,
            $this->system,
            $this->cleanerLogRepository,
            $this->fileLoggerService
        );
    }

    /** @test */
    public function synchronize__no_transactions__skips(): void
    {
        // Given
        $date = new DateTimeImmutable();

        $this->system
            ->method('date')
            ->willReturn($date);

        $this->config
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['payments.synchronizer.max_age'], ['payments.synchronizer.max_transactions_chunk'])
            ->willReturnOnConsecutiveCalls('7 days', 100);

        $this->transactionRepository
            ->expects($this->once())
            ->method('getOutDatedTransactions')
            ->willReturn([]);

        $this->transactionRepository
            ->expects($this->never())
            ->method('save');

        // When
        $this->service->synchronize();
    }

    /** @test */
    public function synchronize__has_transactions__marks_all_as_failed(): void
    {
        // Given
        $date = new DateTimeImmutable();
        $expectedStatus = Helpers_General::STATUS_TRANSACTION_ERROR;

        $transaction = $this->createPartialMock(
            WhitelabelTransaction::class,
            []
        );

        $transaction->status = Helpers_General::STATUS_TRANSACTION_PENDING;
        $transaction->token = 123;
        $transaction->id = 1;
        $transaction->whitelabel = new Whitelabel(['id' => 1, 'prefix' => Whitelabel::LOTTOPARK_PREFIX]);
        $transaction->whitelabel_tickets = new WhitelabelUserTicket();
        $transaction->whitelabel_id = 1;

        $this->system
            ->method('date')
            ->willReturn($date);

        $this->config
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['payments.synchronizer.max_age'], ['payments.synchronizer.max_transactions_chunk'])
            ->willReturnOnConsecutiveCalls('7 days', 100);

        $this->transactionRepository
            ->expects($this->once())
            ->method('getOutDatedTransactions')
            ->willReturn([$transaction]);

        $this->transactionRepository
            ->expects($this->once())
            ->method('save');

        $this->cleanerLogRepository
            ->expects($this->once())
            ->method('addLog')
            ->withConsecutive(
                [$transaction->whitelabel_id, $transaction->id, sprintf('CLEANER | Transaction %s status changed to failed', $transaction->prefixed_token)],
            );

        // When
        $this->service->synchronize();

        // Then
        $this->assertEquals($expectedStatus, $transaction->status);
    }

    /** @test */
    public function synchronize__an_error_occurred__logs(): void
    {
        // Given
        $exceptionMessage = 'some message';
        $exception = new Exception($exceptionMessage);

        $date = new DateTimeImmutable();

        $transactions[] = new WhitelabelTransaction(['status' => 0, 'token' => 123]);
        $transactions[] = new WhitelabelTransaction(['status' => 0, 'token' => 123]);

        $this->system
            ->method('date')
            ->willReturn($date);

        $this->config
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['payments.synchronizer.max_age'], ['payments.synchronizer.max_transactions_chunk'])
            ->willReturnOnConsecutiveCalls('7 days', 100);

        $this->transactionRepository
            ->expects($this->once())
            ->method('getOutDatedTransactions')
            ->willReturn($transactions);

        $this->transactionRepository
            ->expects($this->exactly(count($transactions)))
            ->method('save')
            ->willThrowException($exception);

        // When
        $this->service->synchronize();
    }
}
