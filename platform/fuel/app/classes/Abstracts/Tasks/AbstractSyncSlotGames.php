<?php

namespace Abstracts\Tasks;

use Container;
use Fuel\Core\Database_Exception;
use Fuel\Core\DB;
use Fuel\Core\Fuel;
use GuzzleHttp\Exception\GuzzleException;
use Helpers\ArrayHelper;
use Models\SlotGame;
use Models\SlotSubprovider;
use Models\WhitelabelSlotProviderSubprovider;
use Repositories\SlotGameRepository;
use Repositories\SlotSubproviderRepository;
use Repositories\WhitelabelSlotProviderRepository;
use Repositories\WhitelabelSlotProviderSubproviderRepository;
use Services\Api\Slots\SlotCacheService;
use Services\Logs\FileLoggerService;
use Stwarog\UowFuel\FuelEntityManager;
use Throwable;
use Wrappers\Decorators\ConfigContract;

abstract class AbstractSyncSlotGames
{
    private WhitelabelSlotProviderRepository $whitelabelSlotProviderRepository;
    private SlotGameRepository $slotGameRepository;
    private FuelEntityManager $entityManager;
    private string $wordpressPath;
    private SlotSubproviderRepository $slotSubproviderRepository;
    private array $receivedSubprovidersNamesFromApi = [];
    private WhitelabelSlotProviderSubproviderRepository $whitelabelSlotProviderSubproviderRepository;
    protected FileLoggerService $fileLoggerService;

    protected const PROVIDERS_NAMES_WITHOUT_DEMO = [
        'super spade gaming',
        'vivogaming',
        'xpg',
        'xprogaming',
        'ezugi'
    ];

    /**
     * set_time_limit updates max execution time - saving all images takes a while
     * It will have main impact on first start.
     * For instance Slotegrator has to download ~4k images
     */
    public function __construct()
    {
        $this->whitelabelSlotProviderRepository = Container::get(WhitelabelSlotProviderRepository::class);
        $this->slotGameRepository = Container::get(SlotGameRepository::class);
        $this->entityManager = Container::get(FuelEntityManager::class);
        $this->wordpressPath = Container::get(ConfigContract::class)->get('wordpress.path');
        $this->slotSubproviderRepository = Container::get(SlotSubproviderRepository::class);
        $this->whitelabelSlotProviderSubproviderRepository = Container::get(WhitelabelSlotProviderSubproviderRepository::class);
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        set_time_limit(600);
    }

    /**
     * This function gets slot providers per whitelabel from db and based on it
     * calls specific api to insert/update data in table slot_games
     */
    public function run(): void
    {
        $whitelabelSlotProviders = $this->whitelabelSlotProviderRepository->findAllSlotProviders();
        if (empty($whitelabelSlotProviders)) {
            $message = "Cannot find any whitelabel enabled slot providers. \n";
            echo $message;
            $this->fileLoggerService->error(
                $message
            );
            die;
        }

        foreach ($whitelabelSlotProviders as $whitelabelSlotProvider) {
            $slotProvider = $whitelabelSlotProvider->slotProvider;
            $slotProviderId = $slotProvider->id;
            $gameListPath = $slotProvider->gameListPath;
            $providerSlug = $slotProvider->slug;

            if (empty($gameListPath)) {
                $message = "Empty gameListPath for $providerSlug with id: $slotProviderId . Skipping.... \n";
                echo $message;
                $this->fileLoggerService->error(
                    $message
                );
                continue;
            }

            $gameEndpoint = $gameListPath;
            $apiCredentials = $slotProvider->apiCredentials;
            if (empty($apiCredentials)) {
                $message = "Empty apiCredentials for $providerSlug with id: $slotProviderId \n";
                echo $message;
                $this->fileLoggerService->error(
                    $message
                );
                continue;
            }

            $gamesFromApi = $this->getGamesFromApi($gameEndpoint, $slotProviderId, $whitelabelSlotProvider->whitelabelId);

            if (empty($gamesFromApi)) {
                $message = "$providerSlug with id: $slotProviderId didn't return any games. \n";
                echo $message;
                $this->fileLoggerService->error(
                    $message
                );
                continue;
            }

            $this->setReceivedSubprovidersNames($gamesFromApi);
            $currentGamesForThisProvider = $this->slotGameRepository->findGamesBySlotProviderId($slotProviderId, $this->receivedSubprovidersNamesFromApi);

            echo "Checking if there are new games... \n";
            $this->compareCurrentGamesWithGamesFromApiAndUpdateThem($currentGamesForThisProvider, $gamesFromApi, $slotProviderId);
            echo "Saving subproviders... \n";
            $this->saveReceivedSubprovidersFromApi($whitelabelSlotProvider->id);
            echo "Saving whitelabel subproviders... \n";
            $this->saveWhitelabelSlotProviderSubprovider($whitelabelSlotProvider->id);
        }
        /** @var SlotCacheService $slotCacheService */
        $slotCacheService = Container::get(SlotCacheService::Class);
        $slotCacheService->clearWholeSlotCache();
    }

    private function saveWhitelabelSlotProviderSubprovider(int $whitelabelSlotProviderId): void
    {
        $subProviderIds = $this->slotSubproviderRepository->getIdsByNames($this->receivedSubprovidersNamesFromApi);

        if (empty($subProviderIds)) {
            return;
        }

        $currentWhitelabelSubProviders = $this->whitelabelSlotProviderSubproviderRepository
            ->getSubproviderIdsByWhitelabelSlotProviderId(
                $whitelabelSlotProviderId
            );

        if (empty($currentWhitelabelSubProviders)) {
            $newSubProviderIds = $subProviderIds;
        } else {
            $newSubProviderIds = array_diff($subProviderIds, $currentWhitelabelSubProviders);
        }

        foreach ($newSubProviderIds as $newSubProviderId) {
            $newWhitelabelSubprovider = new WhitelabelSlotProviderSubprovider();
            $newWhitelabelSubprovider->whitelabelSlotProviderId = $whitelabelSlotProviderId;
            $newWhitelabelSubprovider->slotSubproviderId = $newSubProviderId;
            $this->entityManager->save($newWhitelabelSubprovider);
        }

        $this->entityManager->flush();
    }

    private function setReceivedSubprovidersNames(array $gamesFromApi): void
    {
        $receivedSubprovidersNames = [];

        foreach ($gamesFromApi as $game) {
            $receivedSubprovidersNames[] = $game['provider'];
        }

        $this->receivedSubprovidersNamesFromApi = array_unique($receivedSubprovidersNames);
    }

    private function saveReceivedSubprovidersFromApi(int $whitelabelSlotProviderId): void
    {
        $receivedSubprovidersNames = $this->receivedSubprovidersNamesFromApi;
        $currentSubprovidersNames = $this->slotSubproviderRepository->getAllNames();

        if (empty($currentSubprovidersNames)) {
            $newSubprovidersNames = $receivedSubprovidersNames;
        } else {
            $newSubprovidersNames = array_diff($receivedSubprovidersNames, $currentSubprovidersNames);
        }

        if (!empty($newSubprovidersNames)) {
            foreach ($newSubprovidersNames as $newSubproviderName) {
                try {
                    $newSubprovider = new SlotSubprovider();
                    $newSubprovider->name = $newSubproviderName;
                    $newSubprovider->save();
                } catch (Throwable $e) {
                    //duplcate entry; ignore
                }
            }
        }

        $subProviderIds = $this->slotSubproviderRepository->getIdsByNames($receivedSubprovidersNames);
        $this->whitelabelSlotProviderSubproviderRepository->enable($whitelabelSlotProviderId, $subProviderIds);
        $this->whitelabelSlotProviderSubproviderRepository->disable($whitelabelSlotProviderId, $subProviderIds);
    }

    /** @throws GuzzleException */
    abstract protected function getGamesFromApi(string $gameEndpoint, int $slotProviderId, int $whitelabelId): array;

    /**
     * This function based on compare handle database operations [update/insert] for games.
     */
    private function compareCurrentGamesWithGamesFromApiAndUpdateThem(array $currentGames, array $gamesFromApi, $slotProviderId): void
    {
        $uuidsFromApi = array_column($gamesFromApi, 'uuid');
        $uuidsFromDb = array_column($currentGames, 'uuid');

        $newGamesUuids = array_diff($uuidsFromApi, $uuidsFromDb);

        if (!empty($newGamesUuids)) {
            $this->addNewGames($newGamesUuids, $gamesFromApi);
        }

        $actualUuids = array_intersect($uuidsFromDb, $uuidsFromApi);
        $thereAreGamesToChange = !empty($actualUuids) && !empty($uuidsFromDb);

        if ($thereAreGamesToChange) {
            $gamesToEnable = $this->slotGameRepository->findGamesToEnable($actualUuids, $slotProviderId, $this->receivedSubprovidersNamesFromApi);

            if (!empty($gamesToEnable)) {
                $uuidsToEnable = array_column($gamesToEnable, 'uuid');
                $this->changeGameStatus($uuidsToEnable, $slotProviderId, 0, $this->receivedSubprovidersNamesFromApi);
            }

            $gamesToDisable = $this->slotGameRepository->findGamesToDisable($actualUuids, $slotProviderId, $this->receivedSubprovidersNamesFromApi);

            if (!empty($gamesToDisable)) {
                $uuidsToDisable = array_column($gamesToDisable, 'uuid');
                $this->changeGameStatus($uuidsToDisable, $slotProviderId, 1, $this->receivedSubprovidersNamesFromApi);
            }
        }
    }

    /** @throws Database_Exception */
    private function addNewGames(array $newGamesUuids, array $gamesFromApi): void
    {
        $gamesToInsert = [];
        foreach ($newGamesUuids as $uuid) {
            $gameDataKey = array_search($uuid, array_column($gamesFromApi, 'uuid'));
            $gameData = $gamesFromApi[$gameDataKey];
            $gamesToInsert[$gameDataKey] = $gameData;
        }
        $providerNamesWithoutDemo = ArrayHelper::arrayValuesToLowerCase(self::PROVIDERS_NAMES_WITHOUT_DEMO);
        $countOfDownloadedGames = 0;

        foreach ($gamesToInsert as $game) {
            // we don't have to save all images on dev env; we can use provider's link in order to save some time on 1st run
            $imagePath = Fuel::$env === Fuel::PRODUCTION ? $this->saveImageFile($game['uuid'], $game['image']) : $game['image'];
            if (empty($imagePath)) {
                continue;
            }

            $gameModel = new SlotGame();
            $hasDemo = !in_array(strtolower($game['provider']), $providerNamesWithoutDemo);
            $gameModel->slotProviderId = $game['slot_provider_id'];
            $gameModel->uuid = $game['uuid'];
            $gameModel->isDeleted = 0;
            $gameModel->name = $game['name'];
            $gameModel->image = $imagePath;
            $gameModel->type = $game['type'];
            $gameModel->provider = $game['provider'];
            $gameModel->technology = $game['technology'];
            $gameModel->hasDemo = $hasDemo;
            $gameModel->hasLobby = $game['has_lobby'];
            $gameModel->isMobile = $game['is_mobile'];
            $gameModel->hasFreespins = $game['has_freespins'];
            $gameModel->freespinValidUntilFullDay = $game['freespin_valid_until_full_day'];
            $this->entityManager->save($gameModel);

            $countOfDownloadedGames++;
            if ($countOfDownloadedGames % 10 === 0) {
                echo "Downloaded games count: $countOfDownloadedGames \n";
            }
        }
        $this->entityManager->flush();
    }

    private function saveImageFile(string $uuid, string $imageUrl): ?string
    {
        /** @var array $imageUrlAsArray is temporary to get $imageExtension, explode inside end returns warning */
        $imageUrlAsArray = explode(".", $imageUrl);
        $imageExtension = end($imageUrlAsArray);

        $allowedExtensions = [
            'png',
            'jpeg',
            'jpg',
            'webp'
        ];

        if (!in_array($imageExtension, $allowedExtensions)) {
            return '';
        }

        $dir = $this->wordpressPath . '/wp-content/slots/games/';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $imageName = $uuid . "." . $imageExtension;
        $imageLocation =  $dir . $imageName;
        $imageLink = str_replace($this->wordpressPath, '', $imageLocation);

        if (file_exists($imageLocation)) {
            return $imageLink;
        }
        try {
            $imageHeaders = get_headers($imageUrl, 1);
            $is404 = is_array($imageHeaders) && key_exists(0, $imageHeaders) && $imageHeaders[0] === 'HTTP/1.1 404 Not Found';
            if ($is404) {
                return '';
            }

            $isImageAdded = copy($imageUrl, $imageLocation);
        } catch (Throwable $e) {
            return '';
        }
        return $isImageAdded ? $imageLink : '';
    }

    private function changeGameStatus(array $uuids, int $slotProviderId, int $status, array $subProvidersNames): void
    {
        try {
            DB::update('slot_game')
                ->set(['is_deleted' => $status])
                ->where('uuid', 'IN', $uuids)
                ->and_where('slot_provider_id', '=', $slotProviderId)
                ->and_where('provider', 'IN', $subProvidersNames)
                ->execute();
        } catch (Database_Exception $e) {
            $this->fileLoggerService->error(
                "Error during slot games status update for 
                provider id: $slotProviderId "
            );
        }
    }
}
