<?php

namespace Modules\Payments\Astro;

use GuzzleHttp\Exception\GuzzleException;
use Models\WhitelabelTransaction;
use Modules\Payments\Astro\Client\AstroCheckStatusClient;
use Modules\Payments\Astro\Client\AstroDepositClient;
use Modules\Payments\PaymentUrlHelper;
use Orm\RecordNotFound;
use Repositories\Orm\TransactionRepository;
use RuntimeException;
use Services\Shared\Logger\LoggerContract;
use Throwable;
use Wrappers\Decorators\ConfigContract;

class AstroCheckoutUrlHandler
{
    private AstroDepositClient $payClient;
    private LoggerContract $logger;
    private TransactionRepository $repo;
    private PaymentUrlHelper $urlHelper;
    private ConfigContract $config;

    public function __construct(
        AstroDepositClient $client,
        TransactionRepository $repo,
        LoggerContract $logger,
        PaymentUrlHelper $urlHelper,
        ConfigContract $config
    ) {
        $this->payClient = $client;
        $this->logger = $logger;
        $this->repo = $repo;
        $this->urlHelper = $urlHelper;
        $this->config = $config;
    }

    /**
     * @param string $transactionPrefixedToken
     * @param float $amount
     * @param string $currencyCode
     * @param string|null $country
     * @return string
     *
     * @throws GuzzleException
     * @throws Throwable
     */
    public function processPayment(
        string $transactionPrefixedToken,
        int $whitelabelId,
        float $amount,
        string $currencyCode,
        ?string $country = null
    ): string {
        try {
            $transaction = $this->getAndVerifyTransaction($transactionPrefixedToken, $whitelabelId);
            $country = $country ?: $transaction->user->country ?: $this->config->get('payments.astro.astro_default_country');

            $confirmationUrl = $this->urlHelper->getConfirmationUrl($transaction);
            $returnUrl = $this->urlHelper->getResultUrl($transaction);

            $user = ['merchant_user_id' => $transaction->whitelabel_user_id];
            $product = [
                'mcc' => $this->config->get('payments.astro.mcc'),
                'merchant_code' => 'Gambling',
                'description' => 'Gambling ticket',
            ];

            $response = $this->payClient->request(
                $transaction,
                $amount,
                $currencyCode,
                $country,
                $user,
                $product,
                $confirmationUrl,
                $returnUrl
            );

            $data = json_decode($response->getBody()->getContents(), true);

            $this->saveExternalIdToTransaction($transaction, $data);
        } catch (RecordNotFound $exception) {
            throw new RuntimeException("Transaction #$transactionPrefixedToken not found!");
        } catch (Throwable $exception) {
            $this->logger->logErrorFromException($exception, [
                'transaction' => $transaction
            ]);
            throw $exception;
        }

        return $data['url'];
    }

    /**
     * @throws RecordNotFound
     */
    private function getAndVerifyTransaction(string $transactionPrefixedToken, int $whitelabelId): WhitelabelTransaction
    {
        return $this->repo->getByToken($transactionPrefixedToken, $whitelabelId);
    }

    private function saveExternalIdToTransaction(WhitelabelTransaction $transaction, array $data): void
    {
        $transaction->setAdditionalData(AstroCheckStatusClient::DEPOSIT_EXTERNAL_ID_KEY, $data[AstroCheckStatusClient::DEPOSIT_EXTERNAL_ID_KEY]);
        $this->repo->save($transaction, true);
    }
}
