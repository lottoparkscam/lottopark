<?php

namespace Services;

use Exception;
use GGLib\Lcs\Client\Http\HttpLcsClient;
use GGLib\Lcs\Client\Request\GetLotteryDrawsRequest;
use GGLib\Lcs\Client\Request\GetLotteryTicketImageRequest;
use Wrappers\Decorators\ConfigContract;

class LcsService
{
    private HttpLcsClient $httpLcsClient;
    private ConfigContract $config;

    public const TIMEOUT_IN_SECONDS = 2;

    /** @throws Exception */
    public function __construct(HttpLcsClient $httpLcsClient, ConfigContract $config)
    {
        $this->httpLcsClient = $httpLcsClient;
        $this->config = $config;
        $baseUri = $this->config->get('lottery_central_server.url.base');
        $apiKey = $this->config->get('lottery_central_server.sale_point.key');
        $secretKey = $this->config->get('lottery_central_server.sale_point.secret');

        $lcsNotExists = empty($baseUri) || empty($apiKey) || empty($secretKey);
        if ($lcsNotExists) {
            throw new Exception('lcs configuration not exists');
        }
    }

    public function getLotteryScansFromLcs(GetLotteryTicketImageRequest $lotteryTicketImageRequest): string
    {
        return $this->httpLcsClient->getLotteryTicketImage($lotteryTicketImageRequest)->getImage()->getData();
    }

    public function getLotteryDrawsFromLcs(string $lotterySlug, string $currencyCode): array
    {
        $request = new GetLotteryDrawsRequest($lotterySlug, $currencyCode, 3);
        $response = $this->httpLcsClient->getLotteryDraws($request);
    
        return $response->getLotteryDraws();
    }
}
