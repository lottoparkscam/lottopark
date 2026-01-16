<?php

namespace Tests\Unit\Classes\Services;

use Core\App;
use Fuel\Core\PhpErrorException;
use Fuel\Core\Session;
use Services\PageCacheService;
use Test_Unit;

class PageCacheServiceTest extends Test_Unit
{
    public PageCacheService $pageCacheService;
    public App $app;

    public function setUp(): void
    {
        parent::setUp();
        $this->app = $this->createMock(App::class);
        $this->container->set(App::class, $this->app);
        $this->pageCacheService = $this->container->get(PageCacheService::class);
    }

    /** @test */
    public function turnOnPageCache_isDevelopmentEnv_shouldNotTurnOnAndLeftCookiesEnabled(): void
    {
        // Given
        $this->app->expects($this->once())
            ->method('isDevelopment')
            ->willReturn(true);

        // When
        $this->pageCacheService->turnOnPageCache();

        //Then
        $actual = Session::instance()->get_config('enable_cookie');
        $this->assertSame(true, $actual);
    }

    /** @test */
    public function turnOnPageCache_isProductionEnv_shouldTryToSetHeader(): void
    {
        // Expect
        $this->expectException(PhpErrorException::class);
        $this->expectExceptionMessage('Cannot modify header information');

        // Given
        $this->app->expects($this->once())
            ->method('isDevelopment')
            ->willReturn(false);

        // When
        $this->pageCacheService->turnOnPageCache();
    }
}
