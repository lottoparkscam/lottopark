<?php

namespace Modules\Payments\Jeton;

use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use Models\WhitelabelTransaction;
use Modules\Payments\Jeton\Client\JetonStatusCheckClient;
use Modules\Payments\Jeton\Client\JetonTransactionType;
use Modules\Payments\PaymentStatus;
use Orm\RecordNotFound;
use Psr\Http\Message\ResponseInterface;
use Repositories\Orm\TransactionRepository;

/**
 * Class JetonTransactionHandler
 * Determines if requested order was successfully handled by payment provider.
 * If not, then this class should call Instant Payment Callback action.
 */
class JetonTransactionHandler
{
    private TransactionRepository $repo;
    private JetonStatusCheckClient $statusCheckClient;

    public function __construct(
        TransactionRepository $repo,
        JetonStatusCheckClient $client
    ) {
        $this->repo = $repo;
        $this->statusCheckClient = $client;
    }

    /**
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getPaymentStatus(string $orderId, int $whitelabelId): PaymentStatus
    {
        $transaction = $this->getAndVerifyTransactionExists($orderId, $whitelabelId);

        try {
            $response = $this->statusCheckClient->request(
                $transaction,
                JetonTransactionType::PAY()
            );
        } catch (GuzzleException $exception) {
            return PaymentStatus::PENDING();
        }

        $status = $this->extractStatus($response);

        switch ($status) {
            case 'SUCCESS':
                return PaymentStatus::PAID();
            case 'ERROR':
                return PaymentStatus::FAILED();
            default:
                return PaymentStatus::PENDING();
        }
    }

    private function extractStatus(ResponseInterface $response): ?string
    {
        $response = json_decode($response->getBody()->getContents(), true);

        if (empty($response['status'])) {
            return null;
        }

        return $response['status'];
    }

    /**
     * @throws RecordNotFound
     */
    private function getAndVerifyTransactionExists(string $orderId, int $whitelabelId): WhitelabelTransaction
    {
        return $this->repo->getByToken($orderId, $whitelabelId, ['whitelabel_tickets']);
    }
}
