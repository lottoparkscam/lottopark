<?php

namespace unit\classes\services;

use Models\Whitelabel;
use PHPUnit\Framework\MockObject\MockObject;
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
use Services\Api\Slots\SlotCacheService;
use Services\CacheService;
use Test_Unit;

class SlotCacheServiceTest extends Test_Unit
{
    private SlotCacheService $slotCacheServiceUnderTest;
    private WhitelabelRepository|MockObject $whitelabelRepositoryMock;
    private SlotGameRepository|MockObject $slotGameRepositoryMock;
    private WhitelabelSlotProviderRepository|MockObject $whitelabelSlotProviderRepositoryMock;
    private WhitelabelSlotProviderSubproviderRepository|MockObject $whitelabelSlotProviderSubproviderRepositoryMock;
    private SlotProviderRepository|MockObject $slotProviderRepositoryMock;
    private SlotSubproviderRepository|MockObject $slotSubproviderRepositoryMock;
    private CacheService|MockObject $cacheServiceMock;
    private SlotLogRepository|MockObject $slotLogRepositoryMock;
    private SlotOpenGameRepository|MockObject $slotOpenGameRepositoryMock;
    private SlotTransactionRepository|MockObject $slotTransactionRepositoryMock;
    private SlotWhitelistIpRepository|MockObject $slotWhitelistIpRepositoryMock;
    private WhitelabelSlotGameOrderRepository|MockObject $whitelabelSlotGameOrderRepositoryMock;
    private WhitelabelAffSlotCommissionRepository|MockObject $whitelabelAffSlotCommissionRepositoryMock;
    private WhitelabelAffCasinoGroupRepository|MockObject $whitelabelAffCasinoGroupRepositoryMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->whitelabelRepositoryMock = $this->createMock(WhitelabelRepository::class);
        $this->slotGameRepositoryMock = $this->createMock(SlotGameRepository::class);
        $this->whitelabelSlotProviderRepositoryMock = $this->createMock(WhitelabelSlotProviderRepository::class);
        $this->whitelabelSlotProviderSubproviderRepositoryMock = $this->createMock(WhitelabelSlotProviderSubproviderRepository::class);
        $this->slotProviderRepositoryMock = $this->createMock(SlotProviderRepository::class);
        $this->slotSubproviderRepositoryMock = $this->createMock(SlotSubproviderRepository::class);
        $this->cacheServiceMock = $this->createMock(CacheService::class);
        $this->slotLogRepositoryMock = $this->createMock(SlotLogRepository::class);
        $this->slotOpenGameRepositoryMock = $this->createMock(SlotOpenGameRepository::class);
        $this->slotTransactionRepositoryMock = $this->createMock(SlotTransactionRepository::class);
        $this->slotWhitelistIpRepositoryMock = $this->createMock(SlotWhitelistIpRepository::class);
        $this->whitelabelSlotGameOrderRepositoryMock = $this->createMock(WhitelabelSlotGameOrderRepository::class);
        $this->whitelabelAffSlotCommissionRepositoryMock = $this->createMock(WhitelabelAffSlotCommissionRepository::class);
        $this->whitelabelAffCasinoGroupRepositoryMock = $this->createMock(WhitelabelAffCasinoGroupRepository::class);
        $this->slotCacheServiceUnderTest = new SlotCacheService(
            $this->slotGameRepositoryMock,
            $this->whitelabelSlotProviderRepositoryMock,
            $this->whitelabelSlotProviderSubproviderRepositoryMock,
            $this->slotProviderRepositoryMock,
            $this->slotSubproviderRepositoryMock,
            $this->cacheServiceMock,
            $this->whitelabelRepositoryMock,
            $this->slotLogRepositoryMock,
            $this->slotOpenGameRepositoryMock,
            $this->slotTransactionRepositoryMock,
            $this->slotWhitelistIpRepositoryMock,
            $this->whitelabelSlotGameOrderRepositoryMock,
            $this->whitelabelAffSlotCommissionRepositoryMock,
            $this->whitelabelAffCasinoGroupRepositoryMock,
        );
    }

    /**
     * @test
     * @dataProvider getNumberOfGamePagesInCacheProvider
     */
    public function getNumberOfGamePagesInCache(int $gamesNumber, int $expectedPages): void
    {
        $this->slotGameRepositoryMock->expects($this->once())
            ->method('getNumberOfGames')
            ->willReturn($gamesNumber);
        $result = $this->slotCacheServiceUnderTest->getNumberOfGamePagesInCache();
        $this->assertSame($expectedPages, $result);
    }
    public function getNumberOfGamePagesInCacheProvider(): array
    {
        return [
            [230, 8],
            [520, 17],
            [7366, 231],
        ];
    }

    /**
     * @test
     * @dataProvider getCachedKeysProvider
     * CacheKey cannot contain numbers so it has to be changed
     */
    public function getSlotPageGameCacheKey(
        string $whitelabelTheme,
        int $whitelabelId,
        string $whitelabelIdAsLetter,
        array $whitelabelProviderIds,
        string $whitelabelProviderIdsAsLetter,
        string $platform,
        string $userCountry,
        int $pageNumber,
        string $pageNumberAsLetter,
        string $expectedUserCountry
    ): void {
        $whitelabel = new Whitelabel();
        $whitelabel->theme = $whitelabelTheme;
        $whitelabel->id = $whitelabelId;
        $result = $this->slotCacheServiceUnderTest->getPageGameCacheKey(
            $whitelabel,
            $pageNumber,
            $whitelabelProviderIds,
            $platform,
            $userCountry,
        );
        $this->assertSame(
            $whitelabelTheme . '.slots.games.' . $expectedUserCountry . '_' . $platform . '_slot_games_page_' .
            $whitelabelIdAsLetter . '_' . $whitelabelProviderIdsAsLetter . '_' . $pageNumberAsLetter,
            $result
        );
    }

    /** @test */
    public function getAllGameCacheFirstKey(): void
    {
        $resultFirstSource = $this->slotCacheServiceUnderTest->getAllGameCacheFirstKey([1], 'desktop', 'UK', 1);
        $this->assertSame('UK_desktop_slot_games_all_b_b_b', $resultFirstSource);
        $resultFirstSource = $this->slotCacheServiceUnderTest->getAllGameCacheFirstKey([1], 'mobile', 'UK', 1);
        $this->assertSame('UK_mobile_slot_games_all_b_b_b', $resultFirstSource);
    }

    /** @test */
    public function getAllGameCacheSecondKey(): void
    {
        $resultSecondSource = $this->slotCacheServiceUnderTest->getAllGameCacheSecondKey([1], 'desktop', 'UK', 1);
        $this->assertSame('UK_desktop_slot_games_all_c_b_b', $resultSecondSource);
        $resultSecondSource = $this->slotCacheServiceUnderTest->getAllGameCacheSecondKey([1], 'mobile', 'UK', 1);
        $this->assertSame('UK_mobile_slot_games_all_c_b_b', $resultSecondSource);
        $resultSecondSource = $this->slotCacheServiceUnderTest->getAllGameCacheSecondKey([1], 'desktop', 'uk', 1);
        $this->assertSame('UK_desktop_slot_games_all_c_b_b', $resultSecondSource);
        $resultSecondSource = $this->slotCacheServiceUnderTest->getAllGameCacheSecondKey([1], 'mobile', 'pl', 1);
        $this->assertSame('PL_mobile_slot_games_all_c_b_b', $resultSecondSource);
    }

    /** @test */
    public function getSliderCasinoCacheKey(): void
    {
        $result = $this->slotCacheServiceUnderTest->getSliderCasinoCacheKey('desktop', 1, 'en', 256);
        $this->assertSame('desktop_bcasinoSliderWidget_en_cfg', $result);
        $result = $this->slotCacheServiceUnderTest->getSliderCasinoCacheKey('mobile', 1, 'en', 256);
        $this->assertSame('mobile_bcasinoSliderWidget_en_cfg', $result);
        $result = $this->slotCacheServiceUnderTest->getSliderCasinoCacheKey('desktop', 1, 'EN', 256);
        $this->assertSame('desktop_bcasinoSliderWidget_en_cfg', $result);
    }

    /** @test */
    public function getProvidersCacheKey(): void
    {
        $result = $this->slotCacheServiceUnderTest->getProvidersCacheKey(1, 'UK');
        $this->assertSame('UK_slot_game_providers_1', $result);
        $result = $this->slotCacheServiceUnderTest->getProvidersCacheKey(2, 'PL');
        $this->assertSame('PL_slot_game_providers_2', $result);
        $result = $this->slotCacheServiceUnderTest->getProvidersCacheKey(1, 'uk');
        $this->assertSame('UK_slot_game_providers_1', $result);
        $result = $this->slotCacheServiceUnderTest->getProvidersCacheKey(2, 'pl');
        $this->assertSame('PL_slot_game_providers_2', $result);
    }

    public function getCachedKeysProvider(): array
    {
        return [
            [Whitelabel::LOTTOPARK_THEME, 1, 'b', [1], 'b', 'desktop', 'UK', 1, 'b', 'UK'],
            [Whitelabel::LOTTOPARK_THEME, 1, 'b', [1], 'b', 'desktop', 'uk', 1, 'b', 'UK'],
            [Whitelabel::LOTTOBAZAR_THEME, 98, 'ji', [1, 100, 25, 13], 'b_baa_cf_bd', 'mobile', 'PL', 128, 'bci', 'PL'],
            [Whitelabel::LOTTOBAZAR_THEME, 98, 'ji', [1, 100, 25, 13], 'b_baa_cf_bd', 'mobile', 'pl', 128, 'bci', 'PL'],
        ];
    }
}
