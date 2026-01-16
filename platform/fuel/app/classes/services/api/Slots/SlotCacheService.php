<?php

namespace Services\Api\Slots;

use Helpers\CountryHelper;
use Helpers\Wordpress\LanguageHelper;
use Helpers_Cache;
use Models\SlotGame;
use Models\Whitelabel;
use Repositories\SlotGameRepository;
use Repositories\SlotLogRepository;
use Repositories\SlotOpenGameRepository;
use Repositories\SlotProviderRepository;
use Repositories\SlotSubproviderRepository;
use Repositories\SlotTransactionRepository;
use Repositories\SlotWhitelistIpRepository;
use Repositories\WhitelabelAffCasinoGroupRepository;
use Repositories\WhitelabelAffSlotCommissionRepository;
use Repositories\WhitelabelRepository;
use Repositories\WhitelabelSlotGameOrderRepository;
use Repositories\WhitelabelSlotProviderRepository;
use Repositories\WhitelabelSlotProviderSubproviderRepository;
use Services\CacheService;

class SlotCacheService
{
    private SlotGameRepository $slotGameRepository;
    private SlotProviderRepository $slotProviderRepository;
    private SlotSubproviderRepository $slotSubProviderRepository;
    private WhitelabelSlotProviderRepository $whitelabelSlotProviderRepository;
    private WhitelabelSlotProviderSubproviderRepository $whitelabelSlotProviderSubProviderRepository;
    private CacheService $cacheService;
    private WhitelabelRepository $whitelabelRepository;
    private SlotLogRepository $slotLogRepository;
    private SlotOpenGameRepository $slotOpenGameRepository;
    private SlotTransactionRepository $slotTransactionRepository;
    private SlotWhitelistIpRepository $slotWhitelistIpRepository;
    private WhitelabelSlotGameOrderRepository $whitelabelSlotGameOrderRepository;
    private WhitelabelAffSlotCommissionRepository $whitelabelAffSlotCommissionRepository;
    private WhitelabelAffCasinoGroupRepository $whitelabelAffCasinoGroupRepository;
    private array $devices = ['desktop', 'mobile'];

    public function __construct(
        SlotGameRepository $slotGameRepository,
        WhitelabelSlotProviderRepository $whitelabelSlotProviderRepository,
        WhitelabelSlotProviderSubproviderRepository $whitelabelSlotProviderSubProviderRepository,
        SlotProviderRepository $slotProviderRepository,
        SlotSubproviderRepository $slotSubProviderRepository,
        CacheService $cacheService,
        WhitelabelRepository $whitelabelRepository,
        SlotLogRepository $slotLogRepository,
        SlotOpenGameRepository $slotOpenGameRepository,
        SlotTransactionRepository $slotTransactionRepository,
        SlotWhitelistIpRepository $slotWhitelistIpRepository,
        WhitelabelSlotGameOrderRepository $whitelabelSlotGameOrderRepository,
        WhitelabelAffSlotCommissionRepository $whitelabelAffSlotCommissionRepository,
        WhitelabelAffCasinoGroupRepository $whitelabelAffCasinoGroupRepository,
    ) {
        $this->slotGameRepository = $slotGameRepository;
        $this->whitelabelSlotProviderRepository = $whitelabelSlotProviderRepository;
        $this->whitelabelSlotProviderSubProviderRepository = $whitelabelSlotProviderSubProviderRepository;
        $this->slotProviderRepository = $slotProviderRepository;
        $this->slotSubProviderRepository = $slotSubProviderRepository;
        $this->cacheService = $cacheService;
        $this->whitelabelRepository = $whitelabelRepository;
        $this->slotLogRepository = $slotLogRepository;
        $this->slotOpenGameRepository = $slotOpenGameRepository;
        $this->slotTransactionRepository = $slotTransactionRepository;
        $this->slotWhitelistIpRepository = $slotWhitelistIpRepository;
        $this->whitelabelSlotGameOrderRepository = $whitelabelSlotGameOrderRepository;
        $this->whitelabelAffSlotCommissionRepository = $whitelabelAffSlotCommissionRepository;
        $this->whitelabelAffCasinoGroupRepository = $whitelabelAffCasinoGroupRepository;
    }

    public function clearWholeSlotCache(): void
    {
        $this->slotSubProviderRepository->clearCache();
        $this->slotGameRepository->clearCache();
        $this->slotProviderRepository->clearCache();
        $this->slotLogRepository->clearCache();
        $this->slotOpenGameRepository->clearCache();
        $this->slotTransactionRepository->clearCache();
        $this->slotWhitelistIpRepository->clearCache();
        $this->whitelabelAffSlotCommissionRepository->clearCache();
        $this->whitelabelAffCasinoGroupRepository->clearCache();
        $this->whitelabelSlotGameOrderRepository->clearCache();
        $this->whitelabelSlotProviderRepository->clearCache();
        $this->whitelabelSlotProviderSubProviderRepository->clearCache();
        $this->clearGamePagesInCache();
        $this->clearAllGamesInCache();
        $this->clearSliderCache();
        $this->clearProvidersCache();
    }

    public function clearProvidersCache(): void
    {
        $whitelabels = $this->whitelabelRepository->getAllActiveWhitelabels();
        /** @var Whitelabel $whitelabel */
        foreach ($whitelabels as $whitelabel) {
            foreach ($whitelabel->whitelabelLanguages as $whitelabelLanguage) {
                $lang = LanguageHelper::getShortcodeLanguage($whitelabelLanguage->language->code);
                $this->cacheService->deleteGlobalCache($this->getProvidersCacheKey(
                    $whitelabel->id,
                    $lang,
                ));
            }
        }
    }

    public function clearSliderCache(): void
    {
        $whitelabels = $this->whitelabelRepository->getAllActiveWhitelabels();
        /** @var Whitelabel $whitelabel */
        foreach ($whitelabels as $whitelabel) {
            foreach ($whitelabel->whitelabelLanguages as $whitelabelLanguage) {
                $lang = LanguageHelper::getShortcodeLanguage($whitelabelLanguage->language->code);
                foreach ($this->devices as $device) {
                    $NumberOfGamePagesInCache = $this->slotGameRepository->getNumberOfGames();
                    for ($i = 0; $i <= $NumberOfGamePagesInCache; $i++) {
                        $this->cacheService->deleteGlobalCache($this->getSliderCasinoCacheKey(
                            $device,
                            $whitelabel->id,
                            $lang,
                            $i,
                        ));
                    }
                }
            }
        }
    }

    public function clearGamePagesInCache(): void
    {
        $whitelabels = $this->whitelabelRepository->getAllActiveWhitelabels();
        /** @var Whitelabel $whitelabel */
        foreach ($whitelabels as $whitelabel) {
            foreach (CountryHelper::COUNTRY_CODES as $countryCode => $countryName) {
                foreach ($this->devices as $device) {
                    $slotProvidersIds = $this->whitelabelSlotProviderRepository->findEnabledSlotProvidersIdsByWhitelabelId($whitelabel->id);
                    $NumberOfGamePagesInCache = $this->getNumberOfGamePagesInCache();
                    for ($i = 0; $i <= $NumberOfGamePagesInCache; $i++) {
                        $this->cacheService->deleteGlobalCache($this->getPageGameCacheKey(
                            $whitelabel,
                            $i,
                            $slotProvidersIds,
                            $device,
                            $countryCode
                        ));
                    }
                }
            }
        }
    }

    public function clearAllGamesInCache(): void
    {
        $whitelabels = $this->whitelabelRepository->getAllActiveWhitelabels();
        /** @var Whitelabel $whitelabel */
        foreach ($whitelabels as $whitelabel) {
            foreach (CountryHelper::COUNTRY_CODES as $countryCode => $countryName) {
                foreach ($this->devices as $device) {
                    $slotProvidersIds = $this->whitelabelSlotProviderRepository->findEnabledSlotProvidersIdsByWhitelabelId($whitelabel->id);
                    $this->cacheService->deleteGlobalCache($this->getAllGameCacheFirstKey(
                        $slotProvidersIds,
                        $device,
                        $countryCode,
                        $whitelabel->id
                    ));

                    $this->cacheService->deleteGlobalCache($this->getAllGameCacheFirstKey(
                        $slotProvidersIds,
                        $device,
                        $countryCode,
                        $whitelabel->id
                    ));
                }
            }
        }
    }

    public function getSliderCasinoCacheKey(string $platform, int $whitelabelId, string $language, int $numberOfGamesToDisplay): string
    {
        $cacheKey = $platform . '_' . $whitelabelId . 'casinoSliderWidget_' . strtolower($language) . '_' . $numberOfGamesToDisplay;
        return Helpers_Cache::changeNumbersInCacheKeyToLetters($cacheKey);
    }

    public function getProvidersCacheKey(int $whitelabelId, string $userCountry,): string
    {
        return strtoupper($userCountry) . '_slot_game_providers_' . $whitelabelId;
    }

    /** @param int[] $slotProvidersIds */
    public function getPageGameCacheKey(
        Whitelabel $whitelabel,
        int|string $pageNumber,
        array $slotProvidersIds,
        string $platform,
        string $userCountry
    ): string {
        $slotProvidersString = implode('_', $slotProvidersIds);
        $cacheKey = $whitelabel->theme . '.slots.games.' . strtoupper($userCountry) . '_' .
            $platform . '_slot_games_page_' .
            $whitelabel->id . '_' .
            $slotProvidersString . '_' .
            $pageNumber;
        return Helpers_Cache::changeNumbersInCacheKeyToLetters($cacheKey);
    }

    public function getAllGameCacheFirstKey(
        array $slotProvidersIds,
        string $platform,
        string $userCountry,
        int $whitelabelId
    ): string {
        return $this->getAllGameCacheKey($slotProvidersIds, 1, $platform, $userCountry, $whitelabelId);
    }

    public function getAllGameCacheSecondKey(
        array $slotProvidersIds,
        string $platform,
        string $userCountry,
        int $whitelabelId
    ): string {
        return $this->getAllGameCacheKey($slotProvidersIds, 2, $platform, $userCountry, $whitelabelId);
    }

    private function getAllGameCacheKey(
        array $slotProvidersIds,
        int $cacheKeyId,
        string $platform,
        string $userCountry,
        int $whitelabelId
    ): string {
        $slotProvidersString = implode('_', $slotProvidersIds);
        $cacheKey = strtoupper($userCountry) . '_' . $platform . '_slot_games_all_' . $cacheKeyId . '_' .
            $whitelabelId . '_' . $slotProvidersString;
        return Helpers_Cache::changeNumbersInCacheKeyToLetters($cacheKey);
    }

    /**
     * We add slot games to cache in enabledGames endpoint.
     * Path: platform/fuel/app/classes/controller/api/Internal/Slots.php
     * Line: 94.
     * This method can calculate how many pages was cached with games.
     * Calculation example:
     * 7366 games divided by 32 games per page should return 231 cached pages
     */
    public function getNumberOfGamePagesInCache(): int
    {
        $slotGamesCount = $this->slotGameRepository->getNumberOfGames();
        return ceil($slotGamesCount / SlotGame::NUMBER_OF_GAMES_PER_PAGE);
    }
}
