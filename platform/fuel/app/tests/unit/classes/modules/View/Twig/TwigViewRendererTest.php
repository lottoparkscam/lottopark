<?php

namespace Unit\Modules\View;

use Core\App;
use Modules\View\Twig\TwigEnvironmentFactory;
use Modules\View\Twig\TwigViewRenderer;
use Test_Unit;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

use const DIRECTORY_SEPARATOR;

class TwigViewRendererTest extends Test_Unit
{
    private FilesystemLoader $loader;
    private TwigEnvironmentFactory $environmentFactory;

    private TwigViewRenderer $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->loader = $this->createMock(FilesystemLoader::class);
        $this->environmentFactory = $this->createMock(TwigEnvironmentFactory::class);
        $app = $this->container->get(App::class);

        $this->service = new TwigViewRenderer(
            $this->loader,
            $this->environmentFactory,
            $app,
        );
    }

    /** @test */
    public function render__propagates_args(): void
    {
        // Given
        $dirname = __DIR__;
        $basename = 'sometwigfile.twig';
        $file = $dirname . DIRECTORY_SEPARATOR . $basename;

        $data = ['somefield' => 'value'];

        $output = 'someoutput';
        $expected = $output;

        $this->loader
            ->expects($this->once())
            ->method('addPath')
            ->with($dirname);

        $twig = $this->createMock(Environment::class);
        $twig
            ->expects($this->once())
            ->method('render')
            ->with($basename, $data)
            ->willReturn($output);

        $this->environmentFactory
            ->expects($this->once())
            ->method('create')
            ->with($this->loader)
            ->willReturn($twig);

        // When
        $actual = $this->service->render($file, $data);

        // Then
        $this->assertSame($expected, $actual);
    }
}
