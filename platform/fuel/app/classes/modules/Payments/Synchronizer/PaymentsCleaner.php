<?php

namespace Modules\Payments\Synchronizer;

use Repositories\CleanerLogRepository;
use Repositories\Orm\TransactionRepository;
use Services\Logs\FileLoggerService;
use Services\Shared\System;
use Throwable;
use Wrappers\Decorators\ConfigContract;

final class PaymentsCleaner
{
    private const LOG_PREFIX = 'CLEANER |';

    private TransactionRepository $transactionRepository;
    private ConfigContract $config;
    private System $system;
    private CleanerLogRepository $cleanerLogRepository;
    private FileLoggerService $fileLoggerService;

    public function __construct(
        TransactionRepository $transactionRepository,
        ConfigContract $config,
        System $system,
        CleanerLogRepository $cleanerLogRepository,
        FileLoggerService $fileLoggerService
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->config = $config;
        $this->system = $system;
        $this->cleanerLogRepository = $cleanerLogRepository;
        $this->fileLoggerService = $fileLoggerService;
    }

    public function synchronize(): void
    {
        $transactions = $this->transactionRepository->getOutDatedTransactions(
            ...$this->getPreparedCriteria()
        );

        if (empty($transactions)) {
            echo sprintf('%s No transactions to check', self::LOG_PREFIX) . "\n";
            return;
        }

        foreach ($transactions as $transaction) {
            try {
                $transaction->setStatusAsErrorWithTicket();
                $this->transactionRepository->save($transaction);

                $this->cleanerLogRepository->addLog(
                    $transaction->whitelabel_id,
                    $transaction->id,
                    sprintf('%s Transaction %s status changed to failed', self::LOG_PREFIX, $transaction->prefixed_token)
                );
            } catch (Throwable $e) {
                $this->fileLoggerService->error(
                    self::LOG_PREFIX . " Log from exception: " . $e->getMessage()
                );
            }
        }
    }

    private function getPreparedCriteria(): array
    {
        $maxAge = $this->config->get('payments.synchronizer.max_age');
        $date = $this->system->date()->modify("- $maxAge");

        $limit = $this->config->get('payments.synchronizer.max_transactions_chunk', 100);

        return [$date, $limit];
    }
}
