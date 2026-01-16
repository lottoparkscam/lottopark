<?php

namespace Modules\Payments;

use Model_Whitelabel_Transaction;
use Repositories\Orm\TransactionRepository;
use Wrappers\TransactionAcceptHelper;

/**
 * @deprecated
 * @codeCoverageIgnore
 *
 * Class PaymentAcceptor
 * Wrapper for ugly, legacy code, responsible for transaction acceptation.
 * Should be refactored asap.
 */
final class PaymentAcceptor implements PaymentAcceptorContract
{
    private TransactionAcceptHelper $acceptHelper;
    private TransactionRepository $repo;

    public function __construct(TransactionAcceptHelper $acceptHelper, TransactionRepository $repo)
    {
        $this->acceptHelper = $acceptHelper;
        $this->repo = $repo;
    }

    public function accept(string $transactionPrefixedToken, int $whitelabelId): void
    {
        $transaction = $this->repo->getByToken($transactionPrefixedToken, $whitelabelId);

        $this->acceptHelper->accept(
            Model_Whitelabel_Transaction::fromOrm($transaction),
            null,
            [],
            /** @var object $whitelabel */
            $transaction->whitelabel->to_array()
        );
    }
}
