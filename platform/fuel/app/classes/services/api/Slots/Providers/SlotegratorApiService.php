<?php

namespace Services\Api\Slots\Providers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Container;
use Helpers_Time;
use Models\Whitelabel;
use Repositories\SlotLogRepository;
use Repositories\SlotProviderRepository;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use Repositories\WhitelabelRepository;
use Services\CacheService;
use Services\Logs\FileLoggerService;
use Throwable;

class SlotegratorApiService extends SlotegratorSecurityService
{
    private SlotProviderRepository $slotProviderRepository;
    private string $whitelabelTheme;
    private WhitelabelRepository $whitelabelRepository;
    private FileLoggerService $fileLoggerService;
    private SlotLogRepository $slotLogRepository;
    private CacheService $cacheService;

    /** 
     * Container::get('whitelabel') doesn't work on cli
     * so we have to pass $whitelabelId to get proper wl from cli in synchro games tasks
     */
    public function __construct()
    {
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        $this->fileLoggerService->setSource('api');
        $this->slotProviderRepository = Container::get(SlotProviderRepository::class);
        $this->slotLogRepository = Container::get(SlotLogRepository::class);
        $this->cacheService = Container::get(CacheService::class);
        parent::__construct($this->slotProviderRepository);
    }

    private function setWhitelabelTheme(?int $whitelabelId = null): void
    {
        if (!empty($whitelabelId)) {
            $this->whitelabelRepository = Container::get(WhitelabelRepository::class);
            $this->whitelabelTheme = $this->whitelabelRepository->getThemeById($whitelabelId);
        } else {
            $this->whitelabelTheme = Container::get('theme') ?? null;
        }

        $this->whitelabelTheme = strtolower($this->whitelabelTheme);
    }

    /**
     * We return response with error because we use it for saving logs.
     * It's our function to send requests to slotegrator.
     * They don't receive that response.
     */
    public function sendRequest(string $endpoint, array $params, string $method = 'POST', ?int $whitelabelId = null): ?array
    {
        $providerData = $this->slotProviderData;
        $apiUrl = $providerData->apiUrl . $endpoint;
        $this->setWhitelabelTheme($whitelabelId);

        $headers = $this->prepareAccessHeaders($this->whitelabelTheme, $params);
        $apiUrl = rtrim($apiUrl, "/");
        $guzzle = new Client();
        try {
            $request = $guzzle->request(
                $method,
                $apiUrl,
                [
                    'headers' => $headers,
                    'form_params' => $params,
                    'connect_timeout' => 3
                ]
            );
            $responseData = json_decode($request->getBody(), true);
        } catch (ConnectException $e) {
            $this->fileLoggerService->error(
                static::PROVIDER_SLUG . " $apiUrl connection error: {$e->getMessage()}"
            );
            $responseData['error']['connection'] = json_encode('Internet, DNS, or other connection error');
        } catch (RequestException $e) {
            $isInitPath = str_contains($endpoint, 'init');
            $shouldLogErrors = true;
            $hourAgo = Carbon::now('UTC')->subHour();

            if ($isInitPath) {
                $lastSucceedInitCacheKey = 'slotegrator_last_succeed_init_log';

                /** @var Whitelabel $whitelabel */
                $whitelabel = Container::get('whitelabel');
                $whitelabelId = $whitelabelId ?? $whitelabel->id;

                $lastSucceedInit = $this->cacheService->getAndSaveCacheForWhitelabelByIdWithHandleException(
                    $whitelabelId,
                    $lastSucceedInitCacheKey,
                    fn() => $this->slotLogRepository->findLastSucceedInitByWhitelabelId($whitelabelId),
                    [],
                    Helpers_Time::HALF_HOUR_IN_SECONDS
                );
                $shouldLogErrors = !$lastSucceedInit || $lastSucceedInit->createdAt->lessThan($hourAgo);
            }

            $isNotGameNotFoundError = $e->getCode() !== 404 && !str_contains(strtolower($e->getMessage()), 'game not found');
            $isServerErrorFromApi = $e->getCode() === 500;
            $isBadGatewayError = $e->getCode() === 502;
            $isNotBadGatewayError = !$isBadGatewayError;
            $lastSucceedLog = $this->slotLogRepository->findLastSucceedLogByWhitelabelId($whitelabelId);

            // If we receive a 500 error from the API and an hour has passed since the last successful log,
            // we want to receive information that something is wrong
            if ($isServerErrorFromApi && $lastSucceedLog && $lastSucceedLog->createdAt->lessThan($hourAgo)) {
                $shouldLogErrors = true;
            }

            if ($isNotGameNotFoundError && $isNotBadGatewayError && $shouldLogErrors) {
                $message = static::PROVIDER_SLUG . " Api guzzle error url: $apiUrl.
                 Slotgrator api has not been working for an hour. If it is 500 error code send this message to slotegrator: 
                 Hello your api $apiUrl return internal error with code 500 for an hour. Can you check what happened?
                 Error: {$e->getMessage()}";
                $this->fileLoggerService->shouldSendLogWhenProblemExistsAfterGivenTime(
                    1,
                    $message,
                    'slotegratorApiError',
                    FileLoggerService::LOG_TYPE_ERROR
                );
            }

            if ($isBadGatewayError && $shouldLogErrors) {
                $this->fileLoggerService->warning(
                    static::PROVIDER_SLUG . " 502 received: $apiUrl error: {$e->getMessage()}"
                );
            }

            $responseData = null;
            $responseData['error']['response'] = json_decode($e->getResponse()->getBody(), true);
            $responseData['error']['request'] = $e->getRequest();
        } catch (Throwable $e) {
            $this->fileLoggerService->error(
                static::PROVIDER_SLUG . " Throwable guzzle error url: $apiUrl error: {$e->getMessage()}"
            );
            $responseData['error'] = $e->getMessage();
        }
        return $responseData;
    }
}