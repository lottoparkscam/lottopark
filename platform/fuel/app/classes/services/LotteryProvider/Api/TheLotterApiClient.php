<?php

namespace Services\LotteryProvider\Api;

use Container;
use GuzzleHttp\Client;
use Helpers_General;
use Services\Logs\FileLoggerService;
use Throwable;

class TheLotterApiClient
{
    private const BASE_URL = 'https://api.global-services-pro.com/api/v1/';
    private const LOTTERY_DRAW_AVAILABLE_PATH = 'lottery/{lotteryId}/draws/isavailable';
    private const TICKETS_PURCHASE_PATH = 'tickets/purchase';

    private Client $httpClient;
    private FileLoggerService $logger;

    public function __construct()
    {
        $this->httpClient = new Client([
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'timeout' => Helpers_General::GUZZLE_TIMEOUT_IN_SECONDS,
        ]);
        $this->logger = Container::get(FileLoggerService::class);
        $this->logger->setSource('api');
    }

    public function setHttpClient(Client $httpClient): void
    {
        $this->httpClient = $httpClient;
    }

    public function isDrawAvailable(int $lotteryId, string $drawDate): bool
    {
        $url = $this->buildApiUrl(self::LOTTERY_DRAW_AVAILABLE_PATH, ['{lotteryId}' => $lotteryId]);
        $requestData = ['draw_local_date' => $drawDate];

        try {
            $response = $this->httpClient->post($url, ['body' => json_encode($requestData)]);

            $responseData = json_decode($response->getBody(), true);
            if ($responseData !== null && isset($responseData['is_available'])) {
                return $responseData['is_available'] === 'True';
            } else {
                $this->logger->error(
                    "[isDrawAvailable] Invalid response from TheLotter API. Response: {$response->getBody()}, Request data: " . json_encode($requestData)
                );
            }
        } catch (Throwable $throwable) {
            $this->logger->error("[isDrawAvailable] Error during communication with TheLotter API: {$throwable->getMessage()}");
        }

        return false;
    }

    public function purchaseTicket(array $requestData): array
    {
        $url = $this->buildApiUrl(self::TICKETS_PURCHASE_PATH);

        try {
            $response = $this->httpClient->post($url, ['body' => json_encode($requestData)]);
            return json_decode($response->getBody(), true);
        } catch (Throwable $throwable) {
            $this->logger->setSource('api');
            $this->logger->error(
                "[purchaseTicket] Error during communication with TheLotter API. Error: {$throwable->getMessage()}, Request data: " . json_encode($requestData)
            );

        }

        return [];
    }

    private function buildApiUrl(string $path, array $placeholders = []): string
    {
        $url = self::BASE_URL . $path;

        foreach ($placeholders as $placeholder => $value) {
            $url = str_replace($placeholder, $value, $url);
        }

        return $url;
    }
}
