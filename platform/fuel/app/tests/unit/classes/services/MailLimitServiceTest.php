<?php

namespace Tests\Unit\Classes\Services;

use Fuel\Core\CacheNotFoundException;
use Helpers_Time;
use Repositories\Orm\WhitelabelUserRepository;
use Services\CacheService;
use Services\MailLimitService;
use Test_Unit;

class MailLimitServiceTest extends Test_Unit
{
    private MailLimitService $mailLimitService;
    private WhitelabelUserRepository $whitelabelUserRepository;
    private CacheNotFoundException $cacheNotFoundException;
    private CacheService $cacheService;
    private const HALF_DAY_IN_MINUTES = 720;

    public function setUp(): void
    {
        parent::setUp();
        $this->whitelabelUserRepository = $this->createMock(WhitelabelUserRepository::class);
        $this->cacheService = $this->createMock(CacheService::class);
        $this->cacheNotFoundException = $this->createMock(CacheNotFoundException::class);
        $this->mailLimitService = new MailLimitService($this->whitelabelUserRepository, $this->cacheService);
    }

    /** @test */
    public function updateMailLimit_inCache(): void
    {
        $this->cacheService
        ->expects($this->once())
        ->method('getGlobalCache')
        ->with(MailLimitService::CACHE_KEY)
        ->willReturn(10);

        $results = $this->mailLimitService->getDrawMailsLimitPerMinute();
        $this->assertEquals(10, $results);
    }

    /** @test */
    public function updateMailLimit_emptyCache(): void
    {
        $this->cacheService
            ->expects($this->once())
            ->method('getGlobalCache')
            ->with(MailLimitService::CACHE_KEY)
            ->willThrowException($this->cacheNotFoundException);

        $usersNumber = 123332345;
        $this->whitelabelUserRepository
            ->expects($this->once())
            ->method('getCount')
            ->willReturn($usersNumber);

        $this->cacheService
            ->expects($this->once())
            ->method('setGlobalCache')
            ->with(MailLimitService::CACHE_KEY, ceil($usersNumber / Helpers_Time::ELEVEN_HOURS_IN_MINUTES), Helpers_Time::WEEK_IN_SECONDS);

        $results = $this->mailLimitService->getDrawMailsLimitPerMinute();
        $this->assertTrue($usersNumber / $results <= self::HALF_DAY_IN_MINUTES);
    }

    /** @test */
    public function updateMailLimit_emptyUsers(): void
    {
        $this->cacheService
            ->expects($this->once())
            ->method('getGlobalCache')
            ->with(MailLimitService::CACHE_KEY)
            ->willThrowException($this->cacheNotFoundException);

        $usersNumber = 0;
        $this->whitelabelUserRepository
            ->expects($this->once())
            ->method('getCount')
            ->willReturn($usersNumber);

        $this->cacheService
            ->expects($this->once())
            ->method('setGlobalCache')
            ->with(MailLimitService::CACHE_KEY, 10, Helpers_Time::WEEK_IN_SECONDS);

        $results = $this->mailLimitService->getDrawMailsLimitPerMinute();
        $this->assertEquals(10, $results);
    }
}
