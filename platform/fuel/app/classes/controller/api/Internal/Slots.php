<?php

use Abstracts\Controllers\Internal\AbstractPublicController;
use Fuel\Core\Cache;
use Fuel\Core\CacheNotFoundException;
use Fuel\Core\Database_Exception;
use Fuel\Core\Input;
use Fuel\Core\Pagination;
use Helpers\ArrayHelper;
use Helpers\CountryHelper;
use Helpers\DeviceHelper;
use Helpers\SanitizerHelper;
use Helpers\SlotHelper;
use Helpers\StringHelper;
use Models\SlotGame;
use Models\Whitelabel;
use Presenters\Wordpress\Base\Slots\GameListPresenter;
use Repositories\SlotGameRepository;
use Repositories\WhitelabelRepository;
use Repositories\WhitelabelSlotProviderRepository;
use Services\Api\Slots\SlotCacheService;

class Controller_Api_Internal_Slots extends AbstractPublicController
{
    public const LOBBY_GAMES_FILTER = 'live games';

    private WhitelabelRepository $whitelabelRepository;
    private WhitelabelSlotProviderRepository $whitelabelSlotProviderRepository;
    private SlotGameRepository $slotGameRepository;
    private Whitelabel $whitelabel;
    private int $whitelabelId;
    private string $domain;
    private bool $isMobile;
    private SlotCacheService $slotCacheService;

    /**
     * It doesn't catch exception because default catch is enough.
     * When Db is down - site returns error 500
     * With message: Error establishing a database connection
     * @throws Database_Exception
     */
    public function before()
    {
        parent::before();
        $this->whitelabelSlotProviderRepository = Container::get(WhitelabelSlotProviderRepository::class);
        $this->whitelabelRepository = Container::get(WhitelabelRepository::class);
        $this->slotGameRepository = Container::get(SlotGameRepository::class);
        $this->whitelabel = Container::get('whitelabel');
        $this->domain = $this->whitelabel->domain;
        $this->whitelabelId = $this->whitelabel->id;
        $this->isMobile = DeviceHelper::isMobile();
        $this->slotCacheService = Container::get(SlotCacheService::class);
    }

    public function get_enabledGames()
    {
        $slotProvidersIds = $this->whitelabelSlotProviderRepository->findEnabledSlotProvidersIdsByWhitelabelId($this->whitelabelId);
        $platform = $this->isMobile ? 'mobile' : 'desktop';
        $userCountry = CountryHelper::iso() ?: 'UK';
        $currentPageNumber = SanitizerHelper::sanitizeString(Input::get('site', 1) ?? '');

        $cacheKey = $this->slotCacheService->getPageGameCacheKey(
            $this->whitelabel,
            $currentPageNumber,
            $slotProvidersIds,
            $platform,
            $userCountry,
        );

        try {
            $slotGames = Cache::get($cacheKey);
        } catch (CacheNotFoundException $exception) {
            $slotGamesLink = 'https://' . $this->domain;
            $slotGamesCount = $this->slotGameRepository->findEnabledGamesCountForWhitelabelBySlotProvidersIds($this->whitelabelId, $slotProvidersIds, $this->isMobile);

            $paginationConfig = [
                'pagination_url' => $slotGamesLink . '?' . http_build_query(Input::get()),
                'total_items' => $slotGamesCount,
                'per_page' => SlotGame::NUMBER_OF_GAMES_PER_PAGE,
                'uri_segment' => 'site',
            ];

            $pagination = Pagination::forge('enabledGamesPagination', $paginationConfig);

            $slotGames['games'] =
                $this->slotGameRepository->findEnabledGamesForSlotProviders(
                    $this->whitelabelId,
                    $slotProvidersIds,
                    $this->isMobile,
                    $pagination->per_page,
                    $pagination->offset
                );
            $slotGames['pagination'] = $pagination;
            Cache::set($cacheKey, $slotGames, Helpers_Time::DAY_IN_SECONDS);
        }

        $allPagesCount = $slotGames['pagination']->total_pages;
        $isLastPage = $allPagesCount <= $currentPageNumber;
        $games = SlotHelper::prepareGameData($slotGames['games']);
        $games['isLastPage'] = $isLastPage ? 1 : 0;
        return $this->returnResponse($games);
    }

    /**
     * Slot games cache is separated in order to avoid problems with cache memory limit.
     * cacheKey example: slot_games_all_partNumber_slotProviderId_slotProviderId..
     * ##### NOTE: cacheKey is defined by slot providers because other whitelabels can have the same slot providers so it avoids duplicates
     */
    public function get_enabledGamesFilters()
    {
        $platform = $this->isMobile ? 'mobile' : 'desktop';
        $userCountry = CountryHelper::iso() ?: 'UK';

        $slotProvidersIds = $this->whitelabelSlotProviderRepository->findEnabledSlotProvidersIdsByWhitelabelId($this->whitelabelId);
        $firstCacheKey = $this->slotCacheService->getAllGameCacheFirstKey(
            $slotProvidersIds,
            $platform,
            $userCountry,
            $this->whitelabelId,
        );
        $secondCacheKey = $this->slotCacheService->getAllGameCacheSecondKey(
            $slotProvidersIds,
            $platform,
            $userCountry,
            $this->whitelabelId,
        );

        try {
            $firstPartOfSlotGames = Cache::get($firstCacheKey);
            $secondPartOfSlotGames = Cache::get($secondCacheKey);
            $allSlotGames = array_merge($firstPartOfSlotGames, $secondPartOfSlotGames);
        } catch (CacheNotFoundException $exception) {
            $allSlotGames = $this->slotGameRepository->findEnabledGamesForSlotProviders(
                $this->whitelabelId,
                $slotProvidersIds,
                $this->isMobile
            );
            $gamesCount = count($allSlotGames);
            $firstPartOfSlotGames = array_slice($allSlotGames, 0, $gamesCount / 2);
            $secondPartOfSlotGames = array_slice($allSlotGames, $gamesCount / 2);
            Cache::set($firstCacheKey, $firstPartOfSlotGames, Helpers_Time::DAY_IN_SECONDS);
            Cache::set($secondCacheKey, $secondPartOfSlotGames, Helpers_Time::DAY_IN_SECONDS);
        }
        $filters = Input::get();
        $filters = array_filter($filters);

        if (!empty($filters)) {
            $filteredGames = $this->gamesFilters($allSlotGames, $filters);
            $allSlotGames = SlotHelper::prepareGameData($filteredGames);
            return $this->returnResponse($allSlotGames);
        }

        return $this->returnResponse([]);
    }

    /**
     * @param SlotGame[] $allSlotGames
     * @param array $filters example ['slot_game_name' => 'game name']
     */
    private function gamesFilters(array $allSlotGames, array $filters): array
    {
        $games = [];
        $filters = ArrayHelper::arrayValuesToLowerCase($filters);

        $gameName = $filters['slot_game_name'] ?? '';
        $gameProvider = $filters['provider'] ?? '';
        $gameType = $filters['type'] ?? '';

        $allowedUuids = SlotHelper::getAllowedUuidsPerType($gameType);
        $whitelabelHasOwnTypes = is_array($allowedUuids);

        $availableTypes = ArrayHelper::arrayValuesToLowerCase(GameListPresenter::AVAILABLE_GAMES_TYPES);

        foreach ($allSlotGames as $game) {
            $isGameNameInvalid = !empty($gameName) && !StringHelper::strContainsLower($game->name, $gameName);
            $isGameProviderInvalid = !empty($gameProvider) && !StringHelper::strContainsLower(
                $game->provider,
                $gameProvider
            );

            if ($isGameNameInvalid) {
                continue;
            }
            if ($isGameProviderInvalid) {
                continue;
            }

            if ($whitelabelHasOwnTypes) {
                $isUuidAllowed = in_array($game->uuid, $allowedUuids);
                if ($isUuidAllowed) {
                    $games[$game->uuid] = $game;
                }
                continue;
            }

            if (!empty($gameType)) {
                $isGameTypeLike = in_array($gameType, $availableTypes) ?
                    StringHelper::strContainsLower($game->type, $gameType) :
                    false;

                if ($gameType == 'other') {
                    $isGameTypeLike = !in_array(strtolower($game->type), $availableTypes);
                }

                $isLobbyGameType = $gameType === self::LOBBY_GAMES_FILTER;
                if ($isLobbyGameType) {
                    $isGameTypeLike = $game->hasLobby;
                }

                if (!$isGameTypeLike) {
                    continue;
                }
            }
            $games[$game->uuid] = $game;
        }

        return $games;
    }
}
