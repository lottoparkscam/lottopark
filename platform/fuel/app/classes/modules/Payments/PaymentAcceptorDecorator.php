<?php

namespace Modules\Payments;

use Exception;
use Models\WhitelabelTransaction;
use Repositories\Orm\TransactionRepository;
use Services\Shared\Logger\LoggerContract;
use Throwable;
use Wrappers\Db;

/**
 * Class PaymentAcceptorDecorator
 * Wraps, legacy acceptor and adds transaction pay method.
 */
class PaymentAcceptorDecorator
{
    private TransactionRepository $repository;
    private PaymentAcceptorContract $acceptor;
    private Db $db;
    private LoggerContract $logger;

    public function __construct(TransactionRepository $repository, PaymentAcceptorContract $acceptor, Db $db, LoggerContract $logger)
    {
        $this->repository = $repository;
        $this->acceptor = $acceptor;
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * @param array<string, mixed> $details
     * @throws Throwable
     */
    public function confirm(string $transactionPrefixedToken, int $whitelabelId, array $details = []): void
    {
        $transaction = $this->repository->getByToken($transactionPrefixedToken, $whitelabelId);

        $loggerException = null;
        try {
            $this->logger->logInfo('Confirm request received', $details);
            $this->addAdditionalDataToTransaction($transaction, $details);
        } catch (Throwable $e) {
            $loggerException = $e;
        }

        $this->db->start_transaction();
        try {
            $this->acceptor->accept($transactionPrefixedToken, $whitelabelId);
            $transaction->pay();
            $this->repository->save($transaction, true);
            $this->logger->logSuccess('Successfully paid', ['transaction' => $transaction]);
            $this->db->commit_transaction();
        } catch (Throwable $exception) {
            $this->db->rollback_transaction();
            $this->logger->logErrorFromException($exception, ['transaction' => $transaction]);
            throw $exception;
        }

        if ($loggerException) {
            throw $loggerException;
        }
    }

    /**
     * @param WhitelabelTransaction $transaction
     * @param array $data
     * @throws Exception
     */
    private function addAdditionalDataToTransaction(WhitelabelTransaction $transaction, array $data): void
    {
        foreach ($data as $key => $value) {
            $transaction->setAdditionalData($key, $value);
        }
        $this->repository->save($transaction, true);
    }
}
