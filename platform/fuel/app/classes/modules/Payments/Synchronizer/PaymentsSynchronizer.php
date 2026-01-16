<?php

namespace Modules\Payments\Synchronizer;

use Modules\Payments\PaymentFacadeLocator;
use Modules\Payments\PaymentStatus;
use Repositories\Orm\TransactionRepository;
use Services\Shared\System;
use Throwable;
use Wrappers\Decorators\ConfigContract;
use Repositories\SynchronizerLogRepository;
use Models\SynchronizerLog;
use Services\Logs\FileLoggerService;

/**
 * Class should be run in cron. The job of this class
 * is to fetch all pending transactions and find proper payment.slug.facade,
 * and attempt to check it was successfully parsed by payment provider.
 *
 * Many configuration options are available in payments.php (config file).
 */
final class PaymentsSynchronizer
{
    private const LOG_PREFIX = 'SYNCHRONIZER |';

    private TransactionRepository $transactionRepository;
    private SynchronizerLogRepository $synchronizerLogRepository;
    private System $system;
    private ConfigContract $config;
    private PaymentFacadeLocator $locator;
    private FileLoggerService $fileLoggerService;

    public function __construct(
        TransactionRepository $transactionRepository,
        System $system,
        ConfigContract $config,
        PaymentFacadeLocator $serviceLocator,
        SynchronizerLogRepository $synchronizerLogRepository,
        FileLoggerService $fileLoggerService
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->synchronizerLogRepository = $synchronizerLogRepository;
        $this->system = $system;
        $this->config = $config;
        $this->locator = $serviceLocator;
        $this->fileLoggerService = $fileLoggerService;
    }

    public function synchronize(): void
    {
        if ($this->config->get('payments.synchronizer.is_enabled') !== true) {
            return;
        }

        $transactions = $this->transactionRepository->getPendingTransaction(
            ...$this->getPreparedCriteria()
        );

        if (empty($transactions)) {
            echo sprintf('%s No transactions to check', self::LOG_PREFIX) . "\n";
            return;
        }

        foreach ($transactions as $transaction) {
            $whitelabelId = $transaction->whitelabel_id;
            $transactionId = $transaction->id;

            try {
                $paymentSlug = $transaction->payment_method_slug;

                // We don't add any log if payment has not facade.
                // There is a task for setting payment as fail after x weeks.
                // task location: platform/fuel/app/tasks/set_old_pending_transactions_as_fail.php
                if ($this->locator->hasFacade($paymentSlug) === false) {
                    continue;
                }

                $this->synchronizerLogRepository->addLog(
                    $whitelabelId,
                    $transactionId,
                    SynchronizerLog::TYPE_INFO,
                    sprintf(
                        '%s Attempting #%d to sync transaction: #%s',
                        self::LOG_PREFIX,
                        $transaction->payment_attempts_count + 1,
                        $transaction->prefixed_token
                    ),
                );

                $facade = $this->locator->getBySlug($paymentSlug);
                $status = $facade->getPaymentStatus($transaction->prefixed_token, $whitelabelId);

                switch ((string)$status) {
                    case PaymentStatus::PAID:
                        $facade->confirmPayment($transaction->prefixed_token, $whitelabelId);
                        $this->synchronizerLogRepository->addLog(
                            $whitelabelId,
                            $transactionId,
                            SynchronizerLog::TYPE_SUCCESS,
                            sprintf(
                                '%s Transaction #%s status changed to confirmed',
                                self::LOG_PREFIX,
                                $transaction->prefixed_token
                            )
                        );
                        break;
                    case PaymentStatus::CORRUPTED:
                    case PaymentStatus::FAILED:
                        $facade->failPayment($transaction->prefixed_token, $whitelabelId);
                        $this->synchronizerLogRepository->addLog(
                            $whitelabelId,
                            $transactionId,
                            SynchronizerLog::TYPE_INFO,
                            sprintf(
                                '%s Transaction #%s status changed to failed due resolved status was #%s',
                                self::LOG_PREFIX,
                                $transaction->prefixed_token,
                                $status
                            )
                        );
                        break;
                    default:
                        $this->transactionRepository->attemptPayment($transaction->prefixed_token, $whitelabelId);
                        $this->synchronizerLogRepository->addLog(
                            $whitelabelId,
                            $transactionId,
                            SynchronizerLog::TYPE_INFO,
                            sprintf(
                                '%s Transaction #%s attempted to handle status, but no explicit success / failure recognized',
                                self::LOG_PREFIX,
                                $transaction->prefixed_token
                            )
                        );
                        break;
                }
            } catch (Throwable $e) {
                $this->fileLoggerService->error(
                    self::LOG_PREFIX .
                        " Log from exception for payment provider with slug {$transaction->payment_method_slug}: 
                    Transaction id: {$transaction->prefixed_token}
                    Exception message: " . $e->getMessage()
                );
            }
        }
    }

    private function getPreparedCriteria(): array
    {
        $retryIntervalMins = $this->config->get('payments.synchronizer.retry_interval_mins');
        $lastAttemptDate = $this->system->date()->modify("- $retryIntervalMins minutes");

        $minimalAgeMins = $this->config->get('payments.synchronizer.minimal_age_mins');
        $dateTo = $this->system->date()->modify("- $minimalAgeMins minutes");

        $maxAge = $this->config->get('payments.synchronizer.max_age');
        $dateFrom = $this->system->date()->modify("- $maxAge");

        $limit = $this->config->get('payments.synchronizer.max_transactions_chunk', 100);

        $maxAttemptsCount = $this->config->get('payments.synchronizer.max_attempts');

        return [$dateFrom, $dateTo, $lastAttemptDate, $maxAttemptsCount, $limit];
    }
}
