<?php

namespace Modules\Payments\Astro;

use InvalidArgumentException;
use Modules\Payments\Astro\Client\AstroCheckStatusClient;
use Modules\Payments\PaymentStatus;
use Repositories\Orm\TransactionRepository;

class AstroStatusHandler
{
    private AstroCheckStatusClient $client;
    private TransactionRepository $repo;

    public function __construct(AstroCheckStatusClient $client, TransactionRepository $repo)
    {
        $this->client = $client;
        $this->repo = $repo;
    }

    public function getStatus(string $transactionPrefixedToken, int $whitelabelId): PaymentStatus
    {
        $transaction = $this->repo->getByToken($transactionPrefixedToken, $whitelabelId);
        $transactionAdditionalData = $transaction->getAdditionalData();
        $hasNoRequiredDataToCheckStatus = !array_key_exists(AstroCheckStatusClient::DEPOSIT_EXTERNAL_ID_KEY, $transactionAdditionalData);
        if ($hasNoRequiredDataToCheckStatus) {
            return PaymentStatus::CORRUPTED();
        }
        $response = $this->client->request($transaction);
        $data = json_decode($response->getBody()->getContents(), true);
        $status = $data['status'];

        switch ($status) {
            case AstroPaymentStatus::PENDING:
                return PaymentStatus::PENDING();
            case AstroPaymentStatus::APPROVED:
                return PaymentStatus::PAID();
            case AstroPaymentStatus::CANCELLED:
                return PaymentStatus::FAILED();
            default:
                throw new InvalidArgumentException("Unknown $status code received");
        }
    }
}
