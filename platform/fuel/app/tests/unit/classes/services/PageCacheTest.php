<?php

namespace Tests\Unit\Classes\Services;

use Container;
use Core\App;
use Models\Whitelabel;
use Repositories\WhitelabelRepository;
use Services\CloudflareService;
use Services\PageCacheService;
use Services\ShellExecutorService;
use Test_Unit;
use Wrappers\Decorators\ConfigContract;

class PageCacheTest extends Test_Unit
{
    private PageCacheService $pageCacheService;
    private CloudflareService $cloudflareServiceMock;
    private WhitelabelRepository $whitelabelRepositoryMock;
    private App $app;
    private Whitelabel $whitelabel;

    public function setUp(): void
    {
        parent::setUp();
        $_SERVER['HTTP_HOST'] = 'lottopark.loc';
        $this->shellExecutorService = $this->createMock(ShellExecutorService::class);
        $this->cloudflareServiceMock = $this->createMock(CloudflareService::class);
        $this->whitelabelRepositoryMock = $this->getMockBuilder(WhitelabelRepository::class)
            ->disableOriginalConstructor()
            ->addMethods(['findByIsActive'])
            ->getMock();
        $config = $this->createMock(ConfigContract::class);
        $this->app = $this->createMock(App::class);
        $config->expects($this->once())
            ->method('get')
            ->with('page_cache.deleteScriptPath')
            ->willReturn('/var/www/purge-page-cache.sh');

        $this->pageCacheService = new PageCacheService(
            $this->shellExecutorService,
            $this->cloudflareServiceMock,
            $this->whitelabelRepositoryMock,
            $config,
            $this->app
        );

        $this->whitelabel = Container::get('whitelabel');
    }

    /** @test */
    public function clearAllActiveWhitelabels_shouldClearNginx(): void
    {
        $this->whitelabelRepositoryMock->expects($this->once())
            ->method('findByIsActive')
            ->with(true)
            ->willReturn([]);

        $this->shellExecutorService->expects($this->once())
            ->method('execute')
            ->with("/var/www/purge-page-cache.sh 'all'");
        $this->pageCacheService->clearAllActiveWhitelabels();
    }

    /** @test */
    public function clearAllActiveWhitelabels_withNoActiveWhitelabel_shouldClearProxy(): void
    {
        // Then
        $this->whitelabelRepositoryMock->expects($this->once())
            ->method('findByIsActive')
            ->with(true)
            ->willReturn([]);

        $this->cloudflareServiceMock->expects($this->never())
            ->method('clearCacheByWhitelabel');

        // When
        $this->pageCacheService->clearAllActiveWhitelabels();
    }

    /** @test */
    public function clearAllActiveWhitelabels_withOneActiveWhitelabel_shouldClearProxy(): void
    {
        // Given
        $domain = $this->whitelabel->domain;

        // Then
        $this->whitelabelRepositoryMock->expects($this->once())
            ->method('findByIsActive')
            ->with(true)
            ->willReturn([$this->whitelabel]);

        $this->cloudflareServiceMock->expects($this->once())
            ->method('clearCacheByWhitelabel')
            ->with($domain);

        // When
        $this->pageCacheService->clearAllActiveWhitelabels();
    }

    /** @test */
    public function clearAllActiveWhitelabels_withManyActiveWhitelabels_shouldClearProxy(): void
    {
        // Given
        $domain = $this->whitelabel->domain;

        // Then
        $this->whitelabelRepositoryMock->expects($this->once())
            ->method('findByIsActive')
            ->with(true)
            ->willReturn([$this->whitelabel, $this->whitelabel]);

        $this->cloudflareServiceMock->expects($this->exactly(1))
            ->method('clearCacheByWhitelabel')
            ->with($domain);

        // When
        $this->pageCacheService->clearAllActiveWhitelabels();
    }


    /** @test */
    public function clearWhitelabel(): void
    {
        $this->shellExecutorService->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                ["/var/www/purge-page-cache.sh 'wl' 'lottopark.loc'"],
                ["/var/www/purge-page-cache.sh 'wl' 'www.lottopark.loc'"],
            );
        $this->pageCacheService->clearWhitelabel();
    }

    /** @test */
    public function clearWhitelabelByLanguage(): void
    {
        $this->shellExecutorService->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                ["/var/www/purge-page-cache.sh 'regex' 'lottopark.loc' '/pl/*'"],
                ["/var/www/purge-page-cache.sh 'regex' 'www.lottopark.loc' '/pl/*'"],
            );
        $this->pageCacheService->clearWhitelabelByLanguage('pl');
    }

    /** @test */
    public function clearWhitelabelByLanguage_forDefaultLanguage(): void
    {
        $this->shellExecutorService->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                ["/var/www/purge-page-cache.sh 'regex' 'lottopark.loc' '/*'"],
                ["/var/www/purge-page-cache.sh 'regex' 'www.lottopark.loc' '/*'"],
            );
        $this->pageCacheService->clearWhitelabelByLanguage();
    }
}
