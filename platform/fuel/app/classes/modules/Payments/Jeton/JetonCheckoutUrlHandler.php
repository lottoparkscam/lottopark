<?php

namespace Modules\Payments\Jeton;

use Exception;
use GuzzleHttp\Exception\ClientException;
use Models\WhitelabelTransaction;
use Modules\Payments\Jeton\Client\JetonCheckoutPayClient;
use Modules\Payments\PaymentUrlHelper;
use Orm\RecordNotFound;
use Repositories\Orm\TransactionRepository;
use RuntimeException;
use Services\Shared\Logger\LoggerContract;
use Throwable;

class JetonCheckoutUrlHandler
{
    private JetonCheckoutPayClient $payClient;
    private LoggerContract $logger;
    private TransactionRepository $repo;
    private PaymentUrlHelper $urlHelper;

    public function __construct(
        JetonCheckoutPayClient $client,
        TransactionRepository $repo,
        LoggerContract $logger,
        PaymentUrlHelper $urlHelper
    ) {
        $this->payClient = $client;
        $this->logger = $logger;
        $this->repo = $repo;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param string $transactionPrefixedToken
     * @param float $amount
     * @param string $currencyCode
     * @param string $language
     * @return string
     *
     * @throws Exception
     * @throws RecordNotFound
     * @throws ClientException
     */
    public function processPayment(string $transactionPrefixedToken, int $whitelabelId, float $amount, string $currencyCode, string $language = 'EN'): string
    {
        try {
            $transaction = $this->getAndVerifyTransaction($transactionPrefixedToken, $whitelabelId);

            $returnUrl = $this->urlHelper->getConfirmationUrl($transaction);

            $response = $this->payClient->request(
                $transaction,
                $amount,
                $currencyCode,
                $returnUrl,
                $language
            );
        } catch (RecordNotFound $exception) {
            throw new RuntimeException("Transaction #$transactionPrefixedToken not found!");
        } catch (Throwable $exception) {
            $this->logger->logErrorFromException($exception, [
                'transaction' => $transaction
            ]);
            throw $exception;
        }

        $response = json_decode($response->getBody()->getContents(), true);
        return $response['checkout'];
    }

    /**
     * @throws RecordNotFound
     */
    private function getAndVerifyTransaction(string $transactionPrefixedToken, int $whitelabelId): WhitelabelTransaction
    {
        return $this->repo->getByToken($transactionPrefixedToken, $whitelabelId);
    }
}
