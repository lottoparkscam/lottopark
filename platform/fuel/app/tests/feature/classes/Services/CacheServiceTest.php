<?php

namespace Tests\Feature\Classes\Services;

use Services\CacheService;
use Test_Feature;
use Classes\Orm\Criteria\Model_Orm_Criteria_Select;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Repositories\Orm\AbstractRepository;
use Repositories\WhitelabelRepository;
use Fuel\Core\DB;
use Models\Whitelabel;
use Fuel\Core\Cache;

final class CacheServiceTest extends Test_Feature
{
    private CacheService $cacheService;
    private WhitelabelRepository $whitelabelRepository;
    protected DB $db;
    private Whitelabel $whitelabel;

    public function setUp(): void
    {
        parent::setUp();
        $this->whitelabel = $this->container->get('whitelabel');
        $this->db = $this->container->get(DB::class);
        $this->cacheService = $this->container->get(CacheService::class);
        $this->whitelabelRepository = $this->container->get(WhitelabelRepository::class);
        Cache::delete_all();
    }

    /** @test */
    public function getAndSaveQueryForWhitelabelByDomain_OneQueryFromCacheShouldReturnTheSameAsDb(): void
    {
        $cacheKey = 'testing_the_same_result_from_db';

        $whitelabel = $this->getSelectDomainByThemeQuery()->findOne();

        $dataFromCache = $this->cacheService->getAndSaveQueryForWhitelabelByDomain(
            $cacheKey,
            $this->getSelectDomainByThemeQuery(),
            $this->cacheService::FIND_ONE
        )->to_array();

        $dataFromDatabase = $whitelabel->to_array();

        $this->assertSame($dataFromDatabase, $dataFromCache);
    }

    /**
     * Checks if queries with criteria have impact on others
     * @test
     */
    public function getAndSaveQueryForWhitelabelByDomain_MultiSpecifiedSelectQueryShouldResetQueries(): void
    {
        $domainCacheKey = 'testing_getSelectDomainByThemeQuery';
        $themeCacheKey = 'testing_getSelectThemeByDomainQuery';

        $domain = $this->getSelectDomainByThemeQuery()->findOne()->to_array();
        $theme =  $this->getSelectThemeByDomainQuery()->findOne()->to_array();

        // check if queries didn't use orm cache
        $this->assertNotSame($domain, $theme);

        $domainFromCache = $this->cacheService->getAndSaveQueryForWhitelabelByDomain(
            $domainCacheKey,
            $this->getSelectDomainByThemeQuery(),
            $this->cacheService::GET_ONE,
            30
        )->to_array();

        $this->assertSame($domain, $domainFromCache);

        $themeFromCache = $this->cacheService->getAndSaveQueryForWhitelabelByDomain(
            $themeCacheKey,
            $this->getSelectThemeByDomainQuery(),
            $this->cacheService::GET_ONE,
            30
        )->to_array();

        $this->assertSame($theme, $themeFromCache);
    }

    /** @test */
    public function getAndSaveQueryForWhitelabelByDomain_MultiSelectWithFullAndSpecifiedQueryShouldResetQueries(): void
    {
        // 1st step test select * cache
        $whitelabelCacheKey = 'testing_full_whitelabel';
        $whitelabel = $this->getWhitelabelQuery()->findOne()->to_array();

        $whitelabelFromCache = $this->cacheService->getAndSaveQueryForWhitelabelByDomain(
            $whitelabelCacheKey,
            $this->getWhitelabelQuery(),
            $this->cacheService::GET_ONE,
            30
        )->to_array();

        $this->assertSame($whitelabel, $whitelabelFromCache);

        // 2nd step test specified select after select *
        $domainCacheKey = 'testing_domain_cache';
        $domain = $this->getSelectThemeByDomainQuery()->findOne()->to_array();

        $domainFromCache = $this->cacheService->getAndSaveQueryForWhitelabelByDomain(
            $domainCacheKey,
            $this->getSelectThemeByDomainQuery(),
            $this->cacheService::GET_ONE,
            30
        )->to_array();

        $this->assertSame($domain, $domainFromCache);
    }

    /** @test */
    public function getAndSaveQueryForWhitelabelByDomain_ShouldBeAbleToGetCacheByCacheKey(): void
    {
        $cacheKey = 'testing_cache_key_access';
        $domain = $this->getSelectThemeByDomainQuery()->findOne()->to_array();

        $this->cacheService->getAndSaveQueryForWhitelabelByDomain(
            $cacheKey,
            $this->getSelectThemeByDomainQuery(),
            $this->cacheService::GET_ONE,
            30
        )->to_array();

        $domainFromCacheByCacheKey = $this->cacheService->getCacheForWhitelabelByDomain($cacheKey)->to_array();
        $this->assertSame($domain, $domainFromCacheByCacheKey);
    }

    private function getWhitelabelQuery(): AbstractRepository
    {
        return $this->whitelabelRepository->pushCriterias([
            new Model_Orm_Criteria_Where('theme', $this->whitelabel->theme)
        ]);
    }

    private function getSelectDomainByThemeQuery(): AbstractRepository
    {
        return $this->whitelabelRepository->pushCriterias([
            new Model_Orm_Criteria_Select(['domain']),
            new Model_Orm_Criteria_Where('theme', $this->whitelabel->theme)
        ]);
    }

    private function getSelectThemeByDomainQuery(): AbstractRepository
    {
        return $this->whitelabelRepository->pushCriterias([
            new Model_Orm_Criteria_Select(['theme']),
            new Model_Orm_Criteria_Where('domain', $this->whitelabel->domain)
        ]);
    }
}
