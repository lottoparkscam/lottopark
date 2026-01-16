<?php

namespace Tests\Unit\Services;

use Services\CacheService;
use Test_Unit;

final class CacheServiceTest extends Test_Unit
{
    private int $whitelabelId = 1;
    private CacheService $cacheService;

    public function setUp(): void
    {
        parent::setUp();
        $this->cacheService = $this->container->get(CacheService::class);
    }

    /** @test */
    public function prepareCacheKeyForWhitelabelById_ShouldReplaceSpecialChar(): void
    {
        $expectedCacheKey = $this->whitelabelId . '_somerandomKeyATtest.gg';
        $actual = $this->cacheService->prepareCacheKeyForWhitelabelById(
            'somerandomKey@test.gg',
            $this->whitelabelId
        );

        $this->assertSame($expectedCacheKey, $actual);
    }

    /** @test */
    public function prepareCacheKeyForWhitelabelById_HasValidCacheKey_ShouldAddWhitelabelId(): void
    {
        $whitelabelId = 1;

        $expectedCacheKey = $whitelabelId . '_somerandomKeytest.gg';
        $actual = $this->cacheService->prepareCacheKeyForWhitelabelById(
            'somerandomKeytest.gg',
            $whitelabelId
        );

        $this->assertSame($expectedCacheKey, $actual);
    }

    /** @test */
    public function prepareCacheKeyForWhitelabelByDomain_HasValidCacheKey_ShouldAddDomain(): void
    {
        $domain = 'testDomain.loc';

        $expectedCacheKey = $domain . '_somerandomKeytest.gg';
        $actual = $this->cacheService->prepareCacheKeyForWhitelabelByDomain(
            'somerandomKeytest.gg',
            $domain
        );

        $this->assertSame($expectedCacheKey, $actual);
    }

    /** @test */
    public function getAndSaveCacheFunctionWithHandleException_ShouldGetDataFromCache(): void
    {
        $cacheKey = 'cache@helper.test';
        $randomNumber = rand(0, 100);

        $dataFromCache = $this->cacheService->getAndSaveCacheGlobalWithHandleException(
            $cacheKey,
            __CLASS__ . '::returnProvidedNumber',
            [$randomNumber],
            100,
            []
        );

        $dataFromDirectCache = $this->cacheService->getGlobalCache($cacheKey);

        $this->assertSame($dataFromDirectCache, $dataFromCache);
    }

    public static function returnProvidedNumber(int $number)
    {
        return $number;
    }
}
