<?php

declare(strict_types=1);

namespace Services;

use Helpers_General;
use Helpers\TransactionHelper;
use Models\WhitelabelTransaction;

class TransactionService
{
    public function getTransactionType(WhitelabelTransaction $transaction): string
    {
        $transactionTypes = TransactionHelper::getTypes(false);

        return $transactionTypes[$transaction->type];
    }

    public function getTransactionStatus(WhitelabelTransaction $transaction): string
    {
        $transactionStatuses = TransactionHelper::getStatuses(false);

        return $transactionStatuses[$transaction->status] ?? $transactionStatuses[Helpers_General::STATUS_TRANSACTION_PENDING];
    }
}
