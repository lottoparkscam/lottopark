<?php

namespace feature\Service;

use Container;
use Fuel\Core\CacheNotFoundException;
use Helpers_Time;
use Models\SlotProvider;
use Models\Whitelabel;
use Models\WhitelabelLanguage;
use Models\WhitelabelSlotProvider;
use Repositories\SlotGameRepository;
use Repositories\WhitelabelLanguageRepository;
use Repositories\WhitelabelRepository;
use Repositories\WhitelabelSlotProviderRepository;
use Services\Api\Slots\SlotCacheService;
use Services\CacheService;
use Test_Feature;
use Tests\Fixtures\SlotGameFixture;
use Tests\Fixtures\WhitelabelFixture;

class SlotCacheServiceTest extends Test_Feature
{
    private CacheService $cacheService;
    private SlotCacheService $slotCacheService;
    private WhitelabelFixture $whitelabelFixture;
    private Whitelabel $lottoparkWhitelabel;
    private Whitelabel $lottobazarWhitelabel;
    private WhitelabelRepository $whitelabelRepository;
    private WhitelabelSlotProviderRepository $whitelabelSlotProviderRepository;
    private SlotGameFixture $slotGameFixture;
    private SlotGameRepository $slotGameRepository;
    private WhitelabelLanguageRepository $whitelabelLanguageRepository;
    private array $providersIdsLottopark;
    private array $providersIdsLottobazar;

    public function setUp(): void
    {
        parent::setUp();
        $this->cacheService = Container::get(CacheService::class);
        $this->slotCacheService = Container::get(SlotCacheService::class);
        $this->whitelabelFixture = $this->container->get(WhitelabelFixture::class);
        $this->whitelabelRepository = Container::get(WhitelabelRepository::class);
        $this->whitelabelSlotProviderRepository = Container::get(WhitelabelSlotProviderRepository::class);
        $this->slotGameFixture = Container::get(SlotGameFixture::class);
        $this->slotGameRepository = Container::get(SlotGameRepository::class);
        $this->whitelabelLanguageRepository = Container::get(WhitelabelLanguageRepository::class);
        $slotProvider = $this->createSlotProvider();
        $this->setTestLottobazarCasino($slotProvider);
        $this->setTestLottoparkCasino($slotProvider);
    }

    /** @test */
    public function clearCacheWithGames(): void
    {
        /** Lottopark test config */
        $lottoparkMobileTestData = 'lottopark-mobile-games-test';
        $lottoparkDesktopTestData = 'lottopark-desktop-games-test';
        // Page games cache
        $keyCacheWithLottoparkDesktopGames = $this->getCachePageGameKey($this->providersIdsLottopark, $this->lottoparkWhitelabel);
        $keyCacheWithLottoparkMobileGames = $this->getCachePageGameKey($this->providersIdsLottopark, $this->lottoparkWhitelabel, true);
        $this->setTestGamesToCache($lottoparkMobileTestData, $keyCacheWithLottoparkMobileGames);
        $this->setTestGamesToCache($lottoparkDesktopTestData, $keyCacheWithLottoparkDesktopGames);
        $this->checkIfCacheHasBeenCorrectlySet($keyCacheWithLottoparkDesktopGames, $lottoparkDesktopTestData);
        $this->checkIfCacheHasBeenCorrectlySet($keyCacheWithLottoparkMobileGames, $lottoparkMobileTestData);

        // All games cache
        $keyCacheWithAllLottoparkDesktopGamesFirstSource = $this->getAllGameCacheKeyFirst($this->providersIdsLottopark, $this->lottoparkWhitelabel->id);
        $keyCacheWithAllLottoparkDesktopGamesSecondSource = $this->getAllGameCacheKeySecond($this->providersIdsLottopark, $this->lottoparkWhitelabel->id);
        $keyCacheWithAllLottoparkMobileGamesFirstSource = $this->getAllGameCacheKeyFirst($this->providersIdsLottopark, $this->lottoparkWhitelabel->id, true);
        $keyCacheWithAllLottoparkMobileGamesSecondSource = $this->getAllGameCacheKeySecond($this->providersIdsLottopark, $this->lottoparkWhitelabel->id, true);
        $this->setTestGamesToCache($lottoparkDesktopTestData, $keyCacheWithAllLottoparkDesktopGamesFirstSource);
        $this->setTestGamesToCache($lottoparkDesktopTestData, $keyCacheWithAllLottoparkDesktopGamesSecondSource);
        $this->setTestGamesToCache($lottoparkMobileTestData, $keyCacheWithAllLottoparkMobileGamesFirstSource);
        $this->setTestGamesToCache($lottoparkMobileTestData, $keyCacheWithAllLottoparkMobileGamesSecondSource);
        $this->checkIfCacheHasBeenCorrectlySet($keyCacheWithAllLottoparkDesktopGamesFirstSource, $lottoparkDesktopTestData);
        $this->checkIfCacheHasBeenCorrectlySet($keyCacheWithAllLottoparkDesktopGamesSecondSource, $lottoparkDesktopTestData);
        $this->checkIfCacheHasBeenCorrectlySet($keyCacheWithAllLottoparkMobileGamesFirstSource, $lottoparkMobileTestData);
        $this->checkIfCacheHasBeenCorrectlySet($keyCacheWithAllLottoparkMobileGamesSecondSource, $lottoparkMobileTestData);

        //Providers
        $keyCacheProvidersLottopark = $this->slotCacheService->getProvidersCacheKey($this->lottoparkWhitelabel->id, 'PL');
        $this->setTestGamesToCache($lottoparkMobileTestData, $keyCacheProvidersLottopark);
        $this->checkIfCacheHasBeenCorrectlySet($keyCacheProvidersLottopark, $lottoparkMobileTestData);

        // Casino slider cache
        $keyCacheSliderLottoparkDesktop = $this->getSliderCasinoKeyCache($this->lottoparkWhitelabel->id);
        $keyCacheSliderLottoparkMobile = $this->getSliderCasinoKeyCache($this->lottoparkWhitelabel->id, true);
        $this->setTestGamesToCache($lottoparkMobileTestData, $keyCacheSliderLottoparkDesktop);
        $this->setTestGamesToCache($lottoparkMobileTestData, $keyCacheSliderLottoparkMobile);
        $this->checkIfCacheHasBeenCorrectlySet($keyCacheSliderLottoparkDesktop, $lottoparkMobileTestData);
        $this->checkIfCacheHasBeenCorrectlySet($keyCacheSliderLottoparkMobile, $lottoparkMobileTestData);

        /** Lottobazar test config */
        $lottobazarMobileTestData = 'lottobazar-mobile-games-test';
        $lottobazarDesktopTestData = 'lottobazar-desktop-games-test';
        // Page games cache
        $keyCacheWithLottobazarDesktopGames = $this->getCachePageGameKey($this->providersIdsLottobazar, $this->lottobazarWhitelabel);
        $keyCacheWithLottobazarMobileGames = $this->getCachePageGameKey($this->providersIdsLottobazar, $this->lottobazarWhitelabel, true);
        $this->setTestGamesToCache($lottobazarDesktopTestData, $keyCacheWithLottobazarDesktopGames);
        $this->setTestGamesToCache($lottobazarMobileTestData, $keyCacheWithLottobazarMobileGames);
        $this->checkIfCacheHasBeenCorrectlySet($keyCacheWithLottobazarDesktopGames, $lottobazarDesktopTestData);
        $this->checkIfCacheHasBeenCorrectlySet($keyCacheWithLottobazarMobileGames, $lottobazarMobileTestData);

        // All games cache
        $keyCacheWithAllLottobazarDesktopGamesFirstSource = $this->getAllGameCacheKeyFirst($this->providersIdsLottobazar, $this->lottobazarWhitelabel->id);
        $keyCacheWithAllLottobazarDesktopGamesSecondSource = $this->getAllGameCacheKeySecond($this->providersIdsLottobazar, $this->lottobazarWhitelabel->id);
        $keyCacheWithAllLottobazarMobileGamesFirstSource = $this->getAllGameCacheKeyFirst($this->providersIdsLottobazar, $this->lottobazarWhitelabel->id, true);
        $keyCacheWithAllLottobazarMobileGamesSecondSource = $this->getAllGameCacheKeySecond($this->providersIdsLottobazar, $this->lottobazarWhitelabel->id, true);
        $this->setTestGamesToCache($lottobazarDesktopTestData, $keyCacheWithAllLottobazarDesktopGamesFirstSource);
        $this->setTestGamesToCache($lottobazarDesktopTestData, $keyCacheWithAllLottobazarDesktopGamesSecondSource);
        $this->setTestGamesToCache($lottobazarMobileTestData, $keyCacheWithAllLottobazarMobileGamesFirstSource);
        $this->setTestGamesToCache($lottobazarMobileTestData, $keyCacheWithAllLottobazarMobileGamesSecondSource);
        $this->checkIfCacheHasBeenCorrectlySet($keyCacheWithAllLottobazarDesktopGamesFirstSource, $lottobazarDesktopTestData);
        $this->checkIfCacheHasBeenCorrectlySet($keyCacheWithAllLottobazarDesktopGamesSecondSource, $lottobazarDesktopTestData);
        $this->checkIfCacheHasBeenCorrectlySet($keyCacheWithAllLottobazarMobileGamesFirstSource, $lottobazarMobileTestData);
        $this->checkIfCacheHasBeenCorrectlySet($keyCacheWithAllLottobazarMobileGamesSecondSource, $lottobazarMobileTestData);

        //Providers
        $keyCacheProvidersLottobazar = $this->slotCacheService->getProvidersCacheKey($this->lottobazarWhitelabel->id, 'PL');
        $this->setTestGamesToCache($lottobazarMobileTestData, $keyCacheProvidersLottobazar);
        $this->checkIfCacheHasBeenCorrectlySet($keyCacheProvidersLottobazar, $lottobazarMobileTestData);

        // Casino slider cache
        $keyCacheLottobazarSliderDesktop = $this->getSliderCasinoKeyCache($this->lottobazarWhitelabel->id);
        $keyCacheLottobazarSliderMobile = $this->getSliderCasinoKeyCache($this->lottobazarWhitelabel->id, true);
        $this->setTestGamesToCache($lottobazarDesktopTestData, $keyCacheLottobazarSliderDesktop);
        $this->setTestGamesToCache($lottobazarMobileTestData, $keyCacheLottobazarSliderMobile);
        $this->checkIfCacheHasBeenCorrectlySet($keyCacheLottobazarSliderDesktop, $lottobazarDesktopTestData);
        $this->checkIfCacheHasBeenCorrectlySet($keyCacheLottobazarSliderMobile, $lottobazarMobileTestData);

        $this->slotCacheService->clearWholeSlotCache();

        /** Lottobazar assertion */
        $this->checkIfCacheHasBeenCleared($keyCacheWithLottobazarDesktopGames);
        $this->checkIfCacheHasBeenCleared($keyCacheWithLottobazarMobileGames);
        $this->checkIfCacheHasBeenCleared($keyCacheWithAllLottobazarDesktopGamesFirstSource);
        $this->checkIfCacheHasBeenCleared($keyCacheWithAllLottobazarDesktopGamesSecondSource);
        $this->checkIfCacheHasBeenCleared($keyCacheWithAllLottobazarMobileGamesFirstSource);
        $this->checkIfCacheHasBeenCleared($keyCacheWithAllLottobazarMobileGamesSecondSource);
        $this->checkIfCacheHasBeenCleared($keyCacheLottobazarSliderDesktop);
        $this->checkIfCacheHasBeenCleared($keyCacheLottobazarSliderMobile);
        $this->checkIfCacheHasBeenCleared($keyCacheProvidersLottobazar);

        /** Lottopark assertion */
        $this->checkIfCacheHasBeenCleared($keyCacheWithLottoparkDesktopGames);
        $this->checkIfCacheHasBeenCleared($keyCacheWithLottoparkMobileGames);
        $this->checkIfCacheHasBeenCleared($keyCacheWithAllLottoparkDesktopGamesFirstSource);
        $this->checkIfCacheHasBeenCleared($keyCacheWithAllLottoparkDesktopGamesSecondSource);
        $this->checkIfCacheHasBeenCleared($keyCacheWithAllLottoparkMobileGamesFirstSource);
        $this->checkIfCacheHasBeenCleared($keyCacheWithAllLottoparkMobileGamesSecondSource);
        $this->checkIfCacheHasBeenCleared($keyCacheSliderLottoparkDesktop);
        $this->checkIfCacheHasBeenCleared($keyCacheSliderLottoparkMobile);
        $this->checkIfCacheHasBeenCleared($keyCacheProvidersLottopark);
    }

    private function createAndSetFakeLottobazar(): void
    {
        $this->createFakeWhitelabel(Whitelabel::LOTTOBAZAR_THEME);
        $this->lottobazarWhitelabel = $this->whitelabelRepository->findOneByTheme(Whitelabel::LOTTOBAZAR_THEME);
    }

    private function createAndSetFakeLottopark(): void
    {
        $this->createFakeWhitelabel(Whitelabel::LOTTOPARK_THEME);
        $this->lottoparkWhitelabel = $this->whitelabelRepository->findOneByTheme(Whitelabel::LOTTOPARK_THEME);
    }

    private function createFakeWhitelabel(string $theme): void
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $whitelabel = $whitelabel->to_array();
        $newWhitelabel = new Whitelabel($whitelabel);
        $newWhitelabel->set([
            'prefix' => $this->whitelabelFixture->randomPrefix(),
            'theme'  => $theme,
        ]);
        $newWhitelabel->save();
        /** @var WhitelabelLanguage $whitelabelWithPlLang */
        $whitelabelWithPlLang = $this->whitelabelLanguageRepository->findOneById(2)->to_array();
        $newWhitelabelLanguage = new WhitelabelLanguage($whitelabelWithPlLang);
        $newWhitelabelLanguage->whitelabelId = $newWhitelabel->id;
        $newWhitelabelLanguage->languageId = $whitelabelWithPlLang['id'];
        $newWhitelabelLanguage->save();
        $whitelabelWithGBLang = $this->whitelabelLanguageRepository->findOneById(1)->to_array();
        $newWhitelabelLanguage = new WhitelabelLanguage($whitelabelWithPlLang);
        $newWhitelabelLanguage->whitelabelId = $newWhitelabel->id;
        $newWhitelabelLanguage->languageId = $whitelabelWithGBLang['id'];
        $newWhitelabelLanguage->save();
    }

    private function createFakeLottoparkGames(): void
    {
        foreach ($this->providersIdsLottopark as $providerId) {
            $this->addSlotFakeGames($providerId, Whitelabel::LOTTOPARK_THEME);
        }
    }

    private function createFakeLottobazarGames(): void
    {
        foreach ($this->providersIdsLottobazar as $providerId) {
            $this->addSlotFakeGames($providerId, Whitelabel::LOTTOBAZAR_THEME);
        }
    }

    private function addSlotFakeGames(int $slotProviderId, string $theme): void
    {
        $this->slotGameFixture
            ->with('basic')
            ->createMany([
                'slot_provider_id' => $slotProviderId,
                'provider' => "testCachedGamesProvider-$slotProviderId-$theme"
            ], 25);
    }

    private function createProvider(int $whitelabelId, int $slotProviderId): void
    {
        $newWhitelabel = new WhitelabelSlotProvider();
        $newWhitelabel->set([
            'whitelabel_id' => $whitelabelId,
            'slot_provider_id' => $slotProviderId,
            'is_enabled' => true,
        ]);
        $newWhitelabel->save();
    }

    private function setTestLottoparkCasino(SlotProvider $slotProvider): void
    {
        $this->createAndSetFakeLottopark();
        $this->createProvider($this->lottoparkWhitelabel->id, $slotProvider->id);
        $this->providersIdsLottopark =
            $this->whitelabelSlotProviderRepository->findEnabledSlotProvidersIdsByWhitelabelId($this->lottoparkWhitelabel->id);
        $this->createFakeLottoparkGames();
    }

    private function setTestLottobazarCasino(SlotProvider $slotProvider): void
    {
        $this->createAndSetFakeLottobazar();
        $this->createProvider($this->lottobazarWhitelabel->id, $slotProvider->id);
        $this->providersIdsLottobazar = $this->whitelabelSlotProviderRepository
                ->findEnabledSlotProvidersIdsByWhitelabelId($this->lottobazarWhitelabel->id);
        $this->createFakeLottobazarGames();
    }

    private function createSlotProvider(): SlotProvider
    {
        $slotProvider = new SlotProvider();
        $slotProvider->slug = 'test-provider-cache';
        $slotProvider->apiUrl = 'test';
        $slotProvider->initGamePath = 'test';
        $slotProvider->initDemoGamePath = 'test';
        $slotProvider->apiCredentials = '{}';
        $slotProvider->save();
        return $slotProvider;
    }

    private function getCachePageGameKey(array $providerIds, Whitelabel $whitelabel, bool $isMobile = false): string
    {
        $pagesInCache = $this->slotCacheService->getNumberOfGamePagesInCache();
        return $this->slotCacheService->getPageGameCacheKey(
            $whitelabel,
            mt_rand(1, $pagesInCache),
            $providerIds,
            $isMobile ? 'mobile' : 'desktop',
            'PL'
        );
    }

    private function getAllGameCacheKeyFirst(array $providerIds, int $whitelabelId, bool $isMobile = false): string
    {
        return $this->slotCacheService->getAllGameCacheFirstKey(
            $providerIds,
            $isMobile ? 'mobile' : 'desktop',
            'PL',
            $whitelabelId,
        );
    }

    private function getAllGameCacheKeySecond(array $providerIds, int $whitelabelId, bool $isMobile = false): string
    {
        return $this->slotCacheService->getAllGameCacheFirstKey(
            $providerIds,
            $isMobile ? 'mobile' : 'desktop',
            'PL',
            $whitelabelId,
        );
    }

    private function setTestGamesToCache(string $testData, string $keyCache): void
    {
        $this->cacheService->setGlobalCache(
            $keyCache,
            $testData,
            Helpers_Time::HOUR_IN_SECONDS
        );
    }

    private function checkIfCacheHasBeenCorrectlySet(string $keyCache, string $expectedValueInCache): void
    {
        $this->assertSame($expectedValueInCache, $this->cacheService->getGlobalCache($keyCache));
    }

    private function getSliderCasinoKeyCache(int $whitelabelId, bool $isMobile = false): string
    {
        $gamesInCache = $this->slotGameRepository->getNumberOfGames();
        return $this->slotCacheService->getSliderCasinoCacheKey($isMobile ? 'mobile' : 'desktop', $whitelabelId, 'en', mt_rand(1, $gamesInCache));
    }

    private function checkIfCacheHasBeenCleared(string $keyCache): void
    {
        try {
            $testGames = $this->cacheService->getGlobalCache($keyCache);
        } catch (CacheNotFoundException) {
            $testGames = '';
        }
        $this->assertSame('', $testGames);
    }
}
