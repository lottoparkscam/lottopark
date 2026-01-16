<?php

namespace Unit\Modules\View;

use Modules\View\Twig\TwigFilesystemLoaderFactory;
use Test_Unit;
use Wrappers\Decorators\ConfigContract;

class TwigFilesystemLoaderFactoryTest extends Test_Unit
{
    private ConfigContract $config;

    private TwigFilesystemLoaderFactory $factory;

    public function setUp(): void
    {
        parent::setUp();

        $this->config = $this->createMock(ConfigContract::class);
        $this->factory = new TwigFilesystemLoaderFactory($this->config);
    }

    /** @test */
    public function create__with_config_template_directories(): void
    {
        // Given
        $expected = [__DIR__];

        $this->config
            ->expects($this->once())
            ->method('get')
            ->with('view.template_directories')
            ->willReturn($expected);

        // When
        $actual = $this->factory->create()->getPaths();

        // Then
        $this->assertSame($expected, $actual);
    }
}
