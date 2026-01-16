<?php

namespace Services\Api\Slots\Providers;

use Helpers\CaseHelper;
use Repositories\SlotProviderRepository;
use Models\SlotProvider;

class SlotegratorSecurityService
{
    private SlotProviderRepository $slotProviderRepository;
    public SlotProvider $slotProviderData;
    public const PROVIDER_SLUG = 'slotegrator';

    public function __construct(SlotProviderRepository $slotProviderRepository)
    {
        $this->slotProviderRepository = $slotProviderRepository;
        $this->slotProviderData = $this->slotProviderRepository->findOneBySlug(self::PROVIDER_SLUG);
    }

    /**
     * Slotegrator requires all params to generate X- headers.
     * Pagination check is here to avoid repeating the code before every call of this func. 
     */
    public function prepareAccessHeaders(
        string $whitelabelTheme,
        array $params = [],
        int $timestamp = null,
        string $nonce = null
    ): array {
        $providerData = $this->slotProviderData;
        $apiCredentials = $providerData->apiCredentials;
        $whitelabelTheme = CaseHelper::kebabToSnake($whitelabelTheme);

        $merchantKey = $apiCredentials["{$whitelabelTheme}_merchant_key"];
        $merchantId = $apiCredentials["{$whitelabelTheme}_merchant_id"];

        $headers = [
            'X-Merchant-Id' => $merchantId,
            'X-Nonce' => $nonce ?? md5(uniqid(mt_rand(), true)),
            'X-Timestamp' => $timestamp ?? time()
        ];

        $isFirstPage = (isset($params['page']) && $params['page'] === 1);

        if ($isFirstPage) {
            unset($params['page']);
        }

        $mergedHeadersAndParams = array_merge($headers, $params);

        ksort($mergedHeadersAndParams);
        $hashString = http_build_query($mergedHeadersAndParams);

        $XSign = hash_hmac('sha1', $hashString, $merchantKey);
        $headers['X-Sign'] = $XSign;
        return $headers;
    }
}
