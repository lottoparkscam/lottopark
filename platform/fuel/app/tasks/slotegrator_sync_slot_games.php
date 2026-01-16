<?php

namespace Fuel\Tasks;

use Container;
use Services\Api\Slots\Providers\SlotegratorApiService;
use Abstracts\Tasks\AbstractSyncSlotGames;
use Fuel\Core\Fuel;

final class Slotegrator_Sync_Slot_Games extends AbstractSyncSlotGames
{
    private SlotegratorApiService $slotegratorApiService;

    public function __construct()
    {
        parent::__construct();
        $this->slotegratorApiService = Container::get(SlotegratorApiService::class);
    }

    /**
     * Request is in do while loop due to forced pagination from Slotegrator.
     * @var array $gamesFromApi stores all games from api
     * @var array $receivedGamesFromApi stores games from specific pagination site
     */
    protected function getGamesFromApi(string $gameEndpoint, int $slotProviderId, int $whitelabelId): array
    {
        $currentPage = 1;
        $baseGameEndPoint = $gameEndpoint;
        $gamesFromApi = [];

        do {
            $params = [
                'page' => $currentPage
            ];

            if (Fuel::$env !== Fuel::PRODUCTION) {
                // we have to put here sleep for 1s because Slotegrator limited staging to 1 request per 1s
                sleep(1);
            }

            $receivedGamesFromApi = $this->slotegratorApiService->sendRequest($gameEndpoint, $params, 'GET', $whitelabelId);
            $slototegratorDidntReturnMoreGames = empty($receivedGamesFromApi) || empty($receivedGamesFromApi['_meta']['currentPage']) || empty($receivedGamesFromApi['items']);
            if ($slototegratorDidntReturnMoreGames) {
                break;
            }

            $currentPage = $receivedGamesFromApi['_meta']['currentPage'];
            $totalPagesCount = $receivedGamesFromApi['_meta']['pageCount'];

            echo "Received page nr $currentPage from $totalPagesCount \n";

            $currentPage++;
            $gameEndpoint = "$baseGameEndPoint/index?page=$currentPage";

            $gamesFromApi = array_merge($gamesFromApi, $receivedGamesFromApi['items']);
        } while (!empty($receivedGamesFromApi) && $currentPage !== $receivedGamesFromApi['_meta']['pageCount']);

        $gamesFromApi = array_values($gamesFromApi);
        $gamesFromApiKeys = array_keys($gamesFromApi);

        foreach ($gamesFromApiKeys as $key) {
            $gamesFromApi[$key]['slot_provider_id'] = $slotProviderId;
        }

        return array_values($gamesFromApi);
    }
}
