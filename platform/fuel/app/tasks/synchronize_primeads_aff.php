<?php

namespace Fuel\Tasks;

use Container;
use Exception;
use GuzzleHttp\Client;
use Repositories\WhitelabelPluginLogRepository;
use Services\Logs\FileLoggerService;

final class synchronize_primeads_aff
{
    private WhitelabelPluginLogRepository $whitelabelPluginLogRepository;
    private FileLoggerService $fileLoggerService;
    private Client $client;

    public function __construct()
    {
        $this->whitelabelPluginLogRepository = Container::get(WhitelabelPluginLogRepository::class);
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        $this->client = Container::get(Client::class);
    }

    public function run(): void
    {
        $numbersOfSync = ceil($this->whitelabelPluginLogRepository->countPrimeadsRegisteryLog()
            + $this->whitelabelPluginLogRepository->countPrimeadsPurchaseLog() / 10);
        for ($i = 0; $i <= $numbersOfSync; $i++) {
            $lastTenLogs = $this->whitelabelPluginLogRepository->getLastTenPrimeadsRegisterLogs() ?: $this->whitelabelPluginLogRepository->getLastTenPrimeadsPurchaseLogs();
            foreach ($lastTenLogs as $lastLog) {
                $lastLogMessage = json_decode($lastLog['message']);
                if (isset($lastLogMessage->url)) {
                    try {
                        $request = $this->client->request('GET', $lastLogMessage->url, ['timeout' => 5]);
                        $requestNotSuccessful = !in_array($request->getStatusCode(), [200, 201], true);
                        if ($requestNotSuccessful) {
                            $this->fileLoggerService->error("Problem while synchronize primeads. Status: {$request->getStatusCode()} Response: {$request->getBody()}");
                            return;
                        }
                    } catch (Exception $exception) {
                        $this->fileLoggerService->error("Problem while synchronize primeads. {$exception->getMessage()}.");
                        return;
                    }
                    $removeLogResults = $this->whitelabelPluginLogRepository->removeLogById($lastLog['id']);
                    $logIsNotRemoved = $removeLogResults === 0;
                    if ($logIsNotRemoved) {
                        $this->fileLoggerService->error("Synchronize primeads cannot remove log: {$lastLog}");
                    }
                }
            }
            /** ten logs per seconds is limit which we received from primeads */
            sleep(5);
        }
    }
}
