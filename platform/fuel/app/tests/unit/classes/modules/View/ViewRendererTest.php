<?php

namespace Unit\Modules\View;

use Exception;
use Modules\View\Fuel\FuelViewRenderer;
use Modules\View\Twig\TwigViewRenderer;
use Modules\View\ViewPathResolver;
use Modules\View\ViewRenderer;
use Test_Unit;

class ViewRendererTest extends Test_Unit
{
    private FuelViewRenderer $fuelViewRenderer;
    private TwigViewRenderer $twigViewRenderer;
    private ViewPathResolver $resolver;
    private ViewRenderer $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->fuelViewRenderer = $this->createMock(FuelViewRenderer::class);
        $this->twigViewRenderer = $this->createMock(TwigViewRenderer::class);
        $this->resolver = $this->createMock(ViewPathResolver::class);

        $this->service = new ViewRenderer($this->fuelViewRenderer, $this->twigViewRenderer, $this->resolver);
    }

    /** @test */
    public function render__twig_extension__renders_using_twig(): void
    {
        // Given
        $file = 'some/path/file.twig';
        $data = [];
        $options = [];
        $expected = 'output';

        $this->resolver
            ->expects($this->once())
            ->method('resolveFilePath')
            ->with($file)
            ->willReturn($file);

        $this->twigViewRenderer
            ->expects($this->once())
            ->method('render')
            ->with($file, $data, $options)
            ->willReturn($expected);

        // When
        $actual = $this->service->render($file, $data, $options);

        // Then
        $this->assertSame($actual, $expected);
    }

    /** @test */
    public function render__fuel_extension__renders_using_fuel(): void
    {
        // Given
        $file = 'some/path/file.php';
        $data = [];
        $options = [];
        $expected = 'output';

        $this->resolver
            ->expects($this->once())
            ->method('resolveFilePath')
            ->with($file)
            ->willReturn($file);

        $this->fuelViewRenderer
            ->expects($this->once())
            ->method('render')
            ->with($file, $data, $options)
            ->willReturn($expected);

        // When
        $actual = $this->service->render($file, $data, $options);

        // Then
        $this->assertSame($actual, $expected);
    }

    /** @test */
    public function render__no_view_with_extension_twig_or_php__throws_exception(): void
    {
        // Except
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('~Files with extension~');

        // Given
        $file = 'some/path/file.txt';

        // When
        $this->service->render($file);
    }
}
