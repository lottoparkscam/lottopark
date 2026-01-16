<?php

namespace Services;

use Container;
use Throwable;
use Fuel\Core\Cache;
use Fuel\Core\CacheNotFoundException;
use Repositories\Orm\AbstractRepository;
use Helpers_Time;
use Services\Logs\FileLoggerService;

final class CacheService
{
    /** 
     * We use time constants here to avoid importing Helpers_Time 
     * during every invocation of this class 
     */
    public const MINUTE_IN_SECONDS = Helpers_Time::MINUTE_IN_SECONDS;
    public const TEN_MINUTES_IN_SECONDS = Helpers_Time::TEN_MINUTES_IN_SECONDS;
    public const HOUR_IN_SECONDS = Helpers_Time::HOUR_IN_SECONDS;
    public const DAY_IN_SECONDS = Helpers_Time::DAY_IN_SECONDS;

    public const FIND_ONE = 'findOne';
    public const GET_ONE = 'getOne';
    public const GET_RESULTS = 'getResults';
    public const GET_RESULTS_FOR_SINGLE_FIELD = 'getResultsForSingleField';

    /** CacheKey cannot contain special chars so it has to be changed */
    private function changeSpecialCharsInCacheKeyToLetters(string $cacheKey): string
    {
        $valuesToChange = [
            '@' => 'AT',
        ];

        return strtr($cacheKey, $valuesToChange);
    }

    public function prepareCacheKeyForWhitelabelById(string $cacheKey, int $whitelabelId): string
    {
        $cacheKey = $this->changeSpecialCharsInCacheKeyToLetters($cacheKey);
        return "{$whitelabelId}_{$cacheKey}";
    }

    /**
     * @return mixed - cache can store all values 
     * @throws CacheNotFoundException
     */
    private function getCache(string $cacheKey)
    {
        $cacheKey = $this->changeSpecialCharsInCacheKeyToLetters($cacheKey);
        return Cache::get($cacheKey);
    }

    /** @param mixed $data cache can store all values */
    private function setCache(string $cacheKey, $data, ?int $cacheTimeInSeconds = null)
    {
        $cacheKey = $this->changeSpecialCharsInCacheKeyToLetters($cacheKey);
        Cache::set($cacheKey, $data, $cacheTimeInSeconds);
        return $data;
    }

    public function deleteGlobalCache(string $cacheKey): void
    {
        $cacheKey = $this->changeSpecialCharsInCacheKeyToLetters($cacheKey);
        Cache::delete($cacheKey);
    }

    /** If domain is null it gets auto for current wl */
    public function prepareCacheKeyForWhitelabelByDomain(string $cacheKey, ?string $domain): string
    {
        $cacheKey = $this->changeSpecialCharsInCacheKeyToLetters($cacheKey);
        if (empty($domain)) {
            $domain = Container::get('domain');
        }

        return "{$domain}_{$cacheKey}";
    }

    private function addLog(string $errorMessage): void
    {
        $fileLoggerService = Container::get(FileLoggerService::class);
        $fileLoggerService->error($errorMessage);
    }

    /**
     * @param callable $function function which will be used for cache
     * @param mixed $returnOnFail data which will be received after exception from function
     * example function call:
     * CacheService->getAndSaveCacheFunctionWithHandleException(
     *  $cacheKey,
     *  'Model_Whitelabel_Lottery::find_for_whitelabel_and_lottery',
     *  [
     *      $whitelabel["id"],
     *      $lottery["id"]
     *  ],
     *  CacheService::TEN_MINUTES_IN_SECONDS,
     *  []
     *  );
     * 
     * @return mixed - cache can store all values
     */
    private function getAndSaveCacheFunctionWithHandleException(
        string $cacheKey,
        callable $function,
        array $functionParams = [],
        int $cacheTimeInSeconds = 0,
        $returnOnFail = null
    ) {
        try {
            $data = $this->getCache($cacheKey);
        } catch (CacheNotFoundException $e) {
            $cacheKey = $this->changeSpecialCharsInCacheKeyToLetters($cacheKey);
            $data = Cache::call($cacheKey, $function, $functionParams, $cacheTimeInSeconds);
        } catch (Throwable $e) {
            $data = $returnOnFail;
            self::addLog(
                'Error with getAndSaveCacheFunctionWithHandleException: ' . $e
            );
        }

        return $data;
    }

    public function setCacheForWhitelabelById(string $cacheKey, $data, int $whitelabelId, int $cacheTimeInSeconds = 0): void
    {
        $cacheKey = $this->prepareCacheKeyForWhitelabelById($cacheKey, $whitelabelId);
        $this->setCache($cacheKey, $data, $cacheTimeInSeconds);
    }

    /** 
     * @return mixed - cache can store all values 
     * @throws CacheNotFoundException
     */
    public function getCacheForWhitelabelById(string $cacheKey, int $whitelabelId)
    {
        $cacheKey = $this->prepareCacheKeyForWhitelabelById($cacheKey, $whitelabelId);

        return $this->getCache($cacheKey);
    }

    /** 
     * @return mixed - cache can store all values
     * @throws CacheNotFoundException
     */
    public function getCacheForWhitelabelByDomain(string $cacheKey, ?string $domain = null): mixed
    {
        $cacheKey = $this->prepareCacheKeyForWhitelabelByDomain($cacheKey, $domain);

        return $this->getCache($cacheKey);
    }

    public function setCacheForWhitelabelByDomain(string $cacheKey, $data, int $cacheTimeInSeconds = 0, ?string $domain = null): mixed
    {
        $cacheKey = $this->prepareCacheKeyForWhitelabelByDomain($cacheKey, $domain);

        return $this->setCache($cacheKey, $data, $cacheTimeInSeconds);
    }

    public function setGlobalCache(string $cacheKey, $data, int $cacheTimeInSeconds = 0): void
    {
        $this->setCache($cacheKey, $data, $cacheTimeInSeconds);
    }

    /**
     * @throws CacheNotFoundException
     */
    public function getGlobalCache(string $cacheKey)
    {
        return $this->getCache($cacheKey);
    }

    /** 
     * @param mixed $returnOnFail
     * @return mixed - cache can store all values
     */
    public function getAndSaveCacheGlobalWithHandleException(
        string $cacheKey,
        callable $function,
        array $functionParams = [],
        int $cacheTimeInSeconds = 0,
        $returnOnFail = null
    ) {
        return $this->getAndSaveCacheFunctionWithHandleException(
            $cacheKey,
            $function,
            $functionParams,
            $cacheTimeInSeconds,
            $returnOnFail
        );
    }

    /** 
     * @see CacheService->getAndSaveCacheFunctionWithHandleException()
     * @param mixed $returnOnFail
     * @return mixed - cache can store all values
     */
    public function getAndSaveCacheForWhitelabelByIdWithHandleException(
        int $whitelabelId,
        string $cacheKey,
        callable $function,
        array $functionParams = [],
        int $cacheTimeInSeconds = 0,
        $returnOnFail = null
    ) {
        $cacheKey = $this->prepareCacheKeyForWhitelabelById($cacheKey, $whitelabelId);

        return $this->getAndSaveCacheFunctionWithHandleException(
            $cacheKey,
            $function,
            $functionParams,
            $cacheTimeInSeconds,
            $returnOnFail
        );
    }

    /** 
     * @see CacheService->getAndSaveCacheFunctionWithHandleException()
     * @return mixed - cache can store all values
     * @param mixed $returnOnFail
     */
    public function getAndSaveCacheForWhitelabelByDomainWithHandleException(
        ?string $domain,
        string $cacheKey,
        callable $function,
        array $functionParams = [],
        int $cacheTimeInSeconds = 0,
        $returnOnFail = null
    ) {
        $cacheKey = $this->prepareCacheKeyForWhitelabelByDomain($cacheKey, $domain);

        return $this->getAndSaveCacheFunctionWithHandleException(
            $cacheKey,
            $function,
            $functionParams,
            $cacheTimeInSeconds,
            $returnOnFail
        );
    }

    /**
     * We shouldn't pass $method inside $query because it calls query during an invocation
     * @param mixed $returnOnFail 
     */
    private function getAndSaveQuery(
        string $cacheKey,
        AbstractRepository $query,
        string $method,
        int $cacheTimeInSeconds = 0,
        $returnOnFail = null
    ) {
        try {
            // handle error during maintenance mode, don't get from cache if empty
            $dataFromCache = $this->getCache($cacheKey);
            if (!empty($dataFromCache)) {
                // reset criteria from repository when cache exists
                $query->resetQuery();
                return $dataFromCache;
            }
            throw new CacheNotFoundException();
        } catch (CacheNotFoundException $e) {
            $data = $query->$method();
            return $this->setCache($cacheKey, $data, $cacheTimeInSeconds);
        } catch (Throwable $e) {
            $this->addLog($e->getMessage());
            return $returnOnFail;
        }
    }

    /** @param mixed $returnOnFail */
    public function getAndSaveQueryForWhitelabelByDomain(
        string $cacheKey,
        AbstractRepository $query,
        string $method,
        int $cacheTimeInSeconds = 0,
        $returnOnFail = null,
        ?string $domain = null
    ) {
        $cacheKey = $this->prepareCacheKeyForWhitelabelByDomain($cacheKey, $domain);
        return $this->getAndSaveQuery($cacheKey, $query, $method, $cacheTimeInSeconds, $returnOnFail);
    }

    public function deleteForWhitelabelByDomain(string $cacheKey, ?string $domain = null): void
    {
        $cacheKey = $this->prepareCacheKeyForWhitelabelByDomain($cacheKey, $domain);
        Cache::delete($cacheKey);
    }

    /** @param mixed $returnOnFail */
    public function getAndSaveQueryGlobal(
        string $cacheKey,
        AbstractRepository $query,
        string $method,
        int $cacheTimeInSeconds = 0,
        $returnOnFail = null
    ) {
        return $this->getAndSaveQuery($cacheKey, $query, $method, $cacheTimeInSeconds, $returnOnFail);
    }
}
