<?php

namespace Modules\Payments\Trustpayments;

use Models\WhitelabelTransaction;
use Orm\RecordNotFound;
use Repositories\Orm\TransactionRepository;
use RuntimeException;
use Wrappers\Decorators\ConfigContract;

class TrustpaymentsCheckoutUrlHandler
{
    private TransactionRepository $repo;
    private ConfigContract $config;

    public function __construct(
        TransactionRepository $repo,
        ConfigContract $config
    ) {
        $this->repo = $repo;
        $this->config = $config;
    }

    /**
     * @throws RecordNotFound
     */
    public function processPayment(string $transactionPrefixedToken, int $whitelabelId): string
    {
        try {
            $this->getAndVerifyTransaction($transactionPrefixedToken, $whitelabelId);
        } catch (RecordNotFound $exception) {
            throw new RuntimeException("Transaction #$transactionPrefixedToken not found!");
        }
        return sprintf('%s%s', $this->config->get('payments.trustpayments.base_url'), 'process/payments/details');
    }

    /**
     * @throws RecordNotFound
     */
    private function getAndVerifyTransaction(string $transactionPrefixedToken, int $whitelabelId): WhitelabelTransaction
    {
        return $this->repo->getByToken($transactionPrefixedToken, $whitelabelId);
    }
}
