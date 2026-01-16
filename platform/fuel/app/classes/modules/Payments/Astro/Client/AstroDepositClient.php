<?php

namespace Modules\Payments\Astro\Client;

use GuzzleHttp\Exception\GuzzleException;
use Modules\Payments\CustomOptionsAwareContract;
use Modules\Payments\PaymentLogger;
use Psr\Http\Message\ResponseInterface;
use Services\Shared\Logger\LoggerContract;

class AstroDepositClient
{
    public const URL = '/merchant/v1/deposit/init';

    private AstroClientFactory $factory;
    private LoggerContract $logger;

    public function __construct(AstroClientFactory $factory, PaymentLogger $logger)
    {
        $this->factory = $factory;
        $this->logger = $logger;
    }

    /**
     * @url https://developers-wallet.astropay.com/#do-a-deposit
     * @url https://developers-wallet.astropay.com/#api-endpoint
     *
     * @param CustomOptionsAwareContract $transaction
     * @param float $amount
     * @param string $currency - for example USD
     * @param string $country - for example US
     * @param array $user
     * @param array $product
     * @param string $callbackUrl - To be provided if the notification URL is different from the notification
     * @param string|null $redirectUrl - URL to redirect the user after the deposit flow
     * @return ResponseInterface
     *
     * @throws GuzzleException
     */
    public function request(
        CustomOptionsAwareContract $transaction,
        float $amount,
        string $currency,
        string $country,
        array $user,
        array $product,
        string $callbackUrl,
        ?string $redirectUrl = null
    ): ResponseInterface {
        $payload = [
            'amount' => $amount,
            'currency' => $currency,
            'country' => $country,
            'merchant_deposit_id' => $transaction->getOrderId(),
            'callback_url' => $callbackUrl,
            'redirect_url' => $redirectUrl,
            'user' => $user,
            'product' => $product,
            'payment_method_code' => 'UI'
        ];

        $client = $this->factory->create($transaction, $payload);
        $response = $client->request('POST', self::URL, [
            'json' => $payload,
        ]);

        $payloadForLog = $payload;
        $payloadForLog['user_email'] = $payload['user']['email'] ?? '';
        $payloadForLog['user_login'] = $payload['user']['login'] ?? '';
        unset($payloadForLog['user']);

        $this->logger->logInfo('Payment request data', array_merge(
            $payloadForLog,
            ['transaction' => $transaction]
        ));

        return $response;
    }
}
