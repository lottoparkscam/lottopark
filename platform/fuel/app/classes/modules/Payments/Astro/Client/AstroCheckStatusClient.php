<?php

namespace Modules\Payments\Astro\Client;

use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use Modules\Payments\CustomOptionsAwareContract;
use Psr\Http\Message\ResponseInterface;
use Webmozart\Assert\Assert;

class AstroCheckStatusClient
{
    public const URL = '/merchant/v1/deposit/' . self::DEPOSIT_EXTERNAL_ID_KEY . '/status';
    public const DEPOSIT_EXTERNAL_ID_KEY = 'deposit_external_id';

    private AstroClientFactory $factory;

    public function __construct(AstroClientFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @url https://developers-wallet.astropay.com/#api-endpoint-2
     *
     * @param CustomOptionsAwareContract $order
     * @return ResponseInterface
     *
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function request(
        CustomOptionsAwareContract $order
    ): ResponseInterface {
        $client = $this->factory->create($order);
        $additionalData = $order->getAdditionalData();
        Assert::keyExists($additionalData, self::DEPOSIT_EXTERNAL_ID_KEY);
        $externalId = $additionalData[self::DEPOSIT_EXTERNAL_ID_KEY];
        $finalUrl = str_replace(self::DEPOSIT_EXTERNAL_ID_KEY, $externalId, self::URL);
        return $client->request('GET', $finalUrl);
    }
}
