<?php

namespace Modules\Payments\Jeton\Client;

use GuzzleHttp\Exception\GuzzleException;
use Modules\Payments\ClientFactoryContract;
use Modules\Payments\CustomOptionsAwareContract;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JetonStatusCheckClient
 * @url https://developer.jeton.com/doc/status-check
 */
class JetonStatusCheckClient
{
    public const URL = '/api/v3/integration/merchants/payments/status';

    private ClientFactoryContract $factory;

    public function __construct(ClientFactoryContract $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param CustomOptionsAwareContract $order
     * @param JetonTransactionType $transactionType
     *
     * @return ResponseInterface
     *
     * @throws GuzzleException
     */
    public function request(CustomOptionsAwareContract $order, JetonTransactionType $transactionType): ResponseInterface
    {
        $client = $this->factory->create($order);

        return $client->request('POST', self::URL, [
            'json' => [
                'orderId' => $order->getOrderId(),
                'type' => (string)$transactionType
            ]
        ]);
    }
}
