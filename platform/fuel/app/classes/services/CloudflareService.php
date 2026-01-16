<?php

namespace Services;

use Core\App;
use Exception;
use GuzzleHttp\Client;
use Repositories\WhitelabelRepository;
use Services\Logs\FileLoggerService;
use Throwable;

class CloudflareService
{
    private Client $httpClient;
    private WhitelabelRepository $whitelabelRepository;
    private FileLoggerService $logger;
    private App $app;

    public const CLOUDFLARE_PURGE_CACHE_TOKEN_ENV_KEY = 'CLOUDFLARE_PURGE_CACHE_TOKEN';
    public const CLOUDFLARE_ZONE_IDS_ENV_KEY = 'CLOUDFLARE_ZONE_IDS_TO_CLEAR_CACHE';

    public function __construct(
        Client $client,
        WhitelabelRepository $whitelabelRepository,
        FileLoggerService $logger,
        App $app
    ) {
        $this->httpClient = $client;
        $this->whitelabelRepository = $whitelabelRepository;
        $this->logger = $logger;
        $this->app = $app;
    }

    public function clearCacheByWhitelabel(string $domain): bool
    {
        if ($this->app->isNotProduction()) {
            // Cloudflare works only on production
            return true;
        }

        $whitelabel = $this->whitelabelRepository->findOneByDomain($domain);

        if (is_null($whitelabel->cloudflareZone)) {
            $this->logger->info("Clear Cloudflare page cache omitted, because there are no credentials for domain: $domain");
            return false;
        }

        $cloudflareZone = $whitelabel->cloudflareZone;
        $url = $this->getRequestUrl($cloudflareZone->identifier);

        try {
            $options = $this->getRequestOptions();
            $response = $this->httpClient->post($url, $options);
        } catch (Throwable $exception) {
            $this->logger->setSource('api');
            $this->logger->error(
                "Error while sending 'purge cache' request to Cloudflare. Error message: " . $exception->getMessage()
            );
            return false;
        }

        $statusCode = $response->getStatusCode();
        $isNotSentSuccessfully = $statusCode !== 200;

        if ($isNotSentSuccessfully) {
            $responseBody = $response->getBody();
            $this->logger->setSource('api');
            $this->logger->error("Error while sending 'purge cache' request to Cloudflare. Error code: $statusCode, body: $responseBody");
            return false;
        }

        return true;
    }

    public function clearCacheByZoneId(string $zoneId): bool
    {
        if ($this->app->isNotProduction()) {
            // Cloudflare works only on production
            return true;
        }

        $url = $this->getRequestUrl($zoneId);
        try {
            $options = $this->getRequestOptions();
            $response = $this->httpClient->post($url, $options);
        } catch (Throwable $exception) {
            $this->logger->setSource('api');
            $this->logger->error(
                "Error while sending 'purge cache' request to Cloudflare. Error message: " . $exception->getMessage()
            );
            return false;
        }

        $statusCode = $response->getStatusCode();
        $isNotSentSuccessfully = $statusCode !== 200;

        if ($isNotSentSuccessfully) {
            $responseBody = $response->getBody();
            $this->logger->setSource('api');
            $this->logger->error("Error while sending 'purge cache' request to Cloudflare. Error code: $statusCode, body: $responseBody");
            return false;
        }

        return true;
    }

    private function getRequestUrl(string $zoneId): string
    {
        return "https://api.cloudflare.com/client/v4/zones/$zoneId/purge_cache";
    }

    /**
     * @throws Exception
     */
    private function getRequestOptions(): array
    {
        $token = $this->getPurgeCacheToken();
        return [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer $token",
            ],
            'body' => json_encode([
                'purge_everything' => true
            ]),
        ];
    }

    /**
     * @throws Exception
     */
    public function getPurgeCacheToken(): string
    {
        try {
            return $_ENV[self::CLOUDFLARE_PURGE_CACHE_TOKEN_ENV_KEY];
        } catch (Throwable) {
            throw new Exception(
                'CLOUDFLARE_PURGE_CACHE_TOKEN not exist in platform .env setting. 
                Ask the devops team which token you should set.'
            );
        }
    }

    public function getZoneIdsToClear(): array
    {
        if (isset($_ENV[self::CLOUDFLARE_ZONE_IDS_ENV_KEY])) {
            return explode(',', $_ENV[self::CLOUDFLARE_ZONE_IDS_ENV_KEY]);
        }
        return [];
    }
}
