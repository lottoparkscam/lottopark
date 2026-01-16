<?php

namespace Services;

use Fuel\Core\CacheNotFoundException;
use Helpers_Time;
use Repositories\Orm\WhitelabelUserRepository;

class MailLimitService
{
    private WhitelabelUserRepository $whitelabelUserRepository;
    private CacheService $cacheService;
    public const CACHE_KEY = 'usersDrawMailsLimitPerMinute';

    public function __construct(WhitelabelUserRepository $whitelabelUserRepository, CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
        $this->whitelabelUserRepository = $whitelabelUserRepository;
    }

    public function getDrawMailsLimitPerMinute(): int
    {
        try {
            $mailLimit = $this->cacheService->getGlobalCache(self::CACHE_KEY);
        } catch (CacheNotFoundException $exception) {
            $usersNumber = $this->whitelabelUserRepository->getCount();
            $mailLimit = $this->countMailsLimit($usersNumber);
            $this->cacheService->setGlobalCache(self::CACHE_KEY, $mailLimit, Helpers_Time::WEEK_IN_SECONDS);
        }
        return $mailLimit;
    }

    private function countMailsLimit(int $usersNumber): int
    {
        if ($usersNumber === 0) {
            return 10;
        }

        /** We should send all mails for up to twelve hours */
        return ceil($usersNumber / Helpers_Time::ELEVEN_HOURS_IN_MINUTES);
    }
}
