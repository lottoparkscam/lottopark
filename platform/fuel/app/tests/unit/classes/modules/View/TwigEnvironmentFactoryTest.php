<?php

namespace Unit\Modules\View;

use Modules\View\Twig\TwigEnvironmentFactory;
use Test_Unit;
use Twig\Loader\FilesystemLoader;
use Wrappers\Decorators\ConfigContract;

class TwigEnvironmentFactoryTest extends Test_Unit
{
    private ConfigContract $config;

    private TwigEnvironmentFactory $factory;

    public function setUp(): void
    {
        parent::setUp();

        $this->config = $this->createMock(ConfigContract::class);
        $this->factory = new TwigEnvironmentFactory($this->config);
    }

    /**
     * @test
     * @dataProvider debugDataProvider
     * @param bool $debug
     */
    public function create__with_options(bool $debug): void
    {
        // Given
        $expected = $debug;
        $options = ['debug' => $debug];

        $loader = $this->createMock(FilesystemLoader::class);

        $this->config->expects($this->once())
            ->method('get')
            ->with('view.options')
            ->willReturn($options);

        // When
        $actual = $this->factory->create($loader)->isDebug();

        // Then
        $this->assertSame($expected, $actual);
    }

    public function debugDataProvider(): array
    {
        return [
            'with debug' => [true],
            'without debug' => [false],
        ];
    }
}
