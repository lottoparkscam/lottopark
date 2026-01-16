<?php

use Abstracts\Controllers\Internal\AbstractPublicController;
use Fuel\Core\CacheNotFoundException;
use Fuel\Core\Cache;
use Fuel\Core\Input;
use Fuel\Core\Response;
use Helpers\SlotHelper;
use Models\SlotGame;
use Repositories\SlotGameRepository;
use Repositories\WhitelabelSlotProviderRepository;
use Helpers\DeviceHelper;
use Helpers\UrlHelper;
use Helpers\SanitizerHelper;
use Services\Api\Slots\SlotCacheService;

class Controller_Api_Internal_Casino_Slider extends AbstractPublicController
{
    private SlotGameRepository $slotGameRepository;
    private WhitelabelSlotProviderRepository $whitelabelSlotProviderRepository;
    private ?int $whitelabelId;
    private bool $isMobile;
    private SlotCacheService $slotCacheService;
    public function before()
    {
        parent::before();
        $whitelabel = Container::get('whitelabel');

        $this->whitelabelId = $whitelabel->id;
        $this->whitelabelSlotProviderRepository = Container::get(WhitelabelSlotProviderRepository::class);
        $this->slotGameRepository = Container::get(SlotGameRepository::class);
        $this->slotCacheService = Container::get(SlotCacheService::class);
        $this->whitelabelSlotProvidersIds = $this->whitelabelSlotProviderRepository->findEnabledSlotProvidersIdsByWhitelabelId($this->whitelabelId) ?? [];
        $this->isMobile = DeviceHelper::isMobile();
    }

    public function get_data(): Response
    {
        $inputGamesCount = (int)SanitizerHelper::sanitizeString(Input::get('gamesCount') ?: '');
        $inputInternalInitUrl = SanitizerHelper::sanitizeString(Input::get('internalInitUrl') ?: '');
        $inputLobbyInitUrl = SanitizerHelper::sanitizeString(Input::get('lobbyInitUrl') ?: '');
        $inputLanguage = SanitizerHelper::sanitizeString(Input::get('lang') ?: 'en');
        $inputCasinoExists = (int)SanitizerHelper::sanitizeString(Input::get('casinoExists') ?: '');

        $gamesToDisplay = !empty($inputGamesCount) ? $inputGamesCount : SlotGame::NUMBER_OF_GAMES_PER_PAGE;
        $platform = $this->isMobile ? 'mobile' : 'desktop';
        $cacheKey = $this->slotCacheService->getSliderCasinoCacheKey($platform, $this->whitelabelId, $inputLanguage, $gamesToDisplay);

        if (!empty($this->whitelabelSlotProvidersIds)) {
            try {
                $games = Cache::get($cacheKey);
            } catch (CacheNotFoundException $e) {
                $games = $this->slotGameRepository->findEnabledGamesForSlotProviders($this->whitelabelId, $this->whitelabelSlotProvidersIds, $this->isMobile, $gamesToDisplay);
                $games = SlotHelper::prepareGameData($games);

                foreach ($games as $uuid => $gameData) {
                    $isGameWithoutLobby = (bool) !$gameData['has_lobby'];
                    $initUrl = $isGameWithoutLobby ? $inputInternalInitUrl : $inputLobbyInitUrl;
                    $getParamsInit = $initUrl . "?game_uuid=$uuid";
                    if ($isGameWithoutLobby && $gameData['has_demo']) {
                        $getParamsInitDemo = $getParamsInit . "&mode=demo";
                    }
                    $links = [
                        'init_link' => $getParamsInit,
                        'init_demo_link' => $getParamsInitDemo ?? '',
                    ];
                    $games[$uuid] += $links;
                }
                Cache::set($cacheKey, $games, Helpers_Time::DAY_IN_SECONDS);
            }
        } else {
            $games = [];
        }

        $isVisible = !empty($games) && !empty($this->whitelabelSlotProvidersIds) && $inputCasinoExists === 1;
        $casinoUrl = $isVisible ? UrlHelper::changeAbsoluteUrlToCasinoUrl(UrlHelper::getHomeUrlWithoutLanguage(), true) : '#';

        return $this->returnResponse([
            'games' => $games,
            'isVisible' => $isVisible,
            'casinoUrl' => $casinoUrl
        ]);
    }
}
