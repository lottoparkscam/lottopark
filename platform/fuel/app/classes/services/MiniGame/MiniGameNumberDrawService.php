<?php

namespace Services\MiniGame;

use Exception;
use GuzzleHttp\Client;
use Models\MiniGame;
use Services\Logs\FileLoggerService;
use Throwable;

class MiniGameNumberDrawService
{
    private const RNG_API_URL = 'https://rng.gg.international/draw';

    private Client $client;
    private FileLoggerService $loggerService;

    public function __construct(Client $client, FileLoggerService $loggerService)
    {
        $this->client = $client;
        $this->loggerService = $loggerService;

        $this->loggerService->setSource('api');
    }

    /** @throws Exception */
    public function fetchSystemDrawnNumber(MiniGame $miniGame): int
    {
        $url = self::RNG_API_URL . "?count[0]={$miniGame->numDraws}&min[0]={$miniGame->drawRangeStart}&max[0]={$miniGame->drawRangeEnd}";

        try {
            $response = $this->client->request('GET', $url);
            $responseData = json_decode($response->getBody(), true);

            if (!isset($responseData['numbers'][0][0])) {
                throw new Exception('Invalid response from RNG service');
            }

            return (int)$responseData['numbers'][0][0];
        } catch (Throwable $throwable) {
            $this->loggerService->error('Error fetching system number: ' . $throwable->getMessage());
            throw new Exception('System error');
        }
    }
}
