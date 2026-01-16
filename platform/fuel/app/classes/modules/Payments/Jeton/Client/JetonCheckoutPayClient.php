<?php

namespace Modules\Payments\Jeton\Client;

use GuzzleHttp\Exception\GuzzleException;
use Modules\Payments\ClientFactoryContract;
use Modules\Payments\CustomOptionsAwareContract;
use Modules\Payments\PaymentLogger;
use Psr\Http\Message\ResponseInterface;
use Services\Shared\Logger\LoggerContract;

/**
 * Class JetonCheckoutPayClient
 * @url https://developer.jeton.com/doc/pay-checkout
 */
class JetonCheckoutPayClient
{
    public const URL = '/api/v3/integration/merchants/payments/pay';

    private ClientFactoryContract $factory;
    private LoggerContract $logger;

    public function __construct(ClientFactoryContract $factory, PaymentLogger $logger)
    {
        $this->factory = $factory;
        $this->logger = $logger;
    }

    /**
     * @param CustomOptionsAwareContract $transaction
     * @param float $amount
     * @param string $currency - The currency of transaction (ISO 4217)
     * @param string $returnUrl
     * @param string $language
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function request(
        CustomOptionsAwareContract $transaction,
        float $amount,
        string $currency,
        string $returnUrl,
        string $language = 'EN'
    ): ResponseInterface {
        $client = $this->factory->create($transaction);
        $requestData = ['json' => array_merge(
            compact('amount', 'currency', 'language', 'returnUrl'),
            ['method' => JetonPayMethod::CHECKOUT, 'orderId' => $transaction->getOrderId()]
        )
        ];
        $this->logger->logInfo('Payment request data', array_merge($requestData['json'], ['transaction' => $transaction]));
        return $client->request('POST', self::URL, $requestData);
    }
}
