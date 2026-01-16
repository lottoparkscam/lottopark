<?php

namespace Tests\Unit\Core;

use Core\App;
use Test_Unit;
use Wrappers\Decorators\ConfigContract;

final class AppTest extends Test_Unit
{
    private App $app;
    private ConfigContract $configContract;

    public function setUp(): void
    {
        parent::setUp();
        $this->configContract = $this->createMock(ConfigContract::class);
        $this->app = new App($this->configContract);
    }

    /** @test */
    public function isDevelopment_shouldReturnTrue(): void
    {
        $this->createFakeConfig('development', 'development');

        $this->assertTrue($this->app->isDevelopment());
    }

    /** @test */
    public function isReviewApp_shouldReturnTrueBasedOnServerTypeInEnv(): void
    {
        $this->createFakeConfig('review-app', 'staging');

        $this->assertTrue($this->app->isReviewApp());
    }

    /** @test */
    public function isNotReviewApp_shouldReturnTrueBasedOnServerTypeInEnv(): void
    {
        $this->createFakeConfig('staging', 'staging');

        $this->assertTrue($this->app->isNotReviewApp());
    }

    /** @test */
    public function isTest_shouldAutoDetect(): void
    {
        /** @var App $app */
        $app = $this->container->get(App::class);

        $this->assertTrue($app->isTest());
    }

    /** @test */
    public function getServerType_returnsValidType(): void
    {
        $this->createFakeConfig('review-app');

        $this->assertSame('review-app', $this->app->getServerType());
    }

    private function createFakeConfig(string $serverType, string $env = ''): void
    {
        $this->configContract
            ->method('get')
            ->withConsecutive(
                ['App.serverType'],
                ['App.env'],
            )
            ->willReturnOnConsecutiveCalls(
                $serverType,
                $env
            );
    }
}
