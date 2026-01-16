<?php

namespace Modules\View;

use Exception;
use Modules\View\Fuel\FuelViewRenderer;
use Modules\View\Twig\TwigViewRenderer;

class ViewRenderer implements ViewRendererContract
{
    public const SUPPORTED_EXTENSIONS = ['twig', 'php'];

    private FuelViewRenderer $fuelViewRenderer;
    private TwigViewRenderer $twigViewRenderer;
    private ViewPathResolver $resolver;

    public function __construct(
        FuelViewRenderer $fuelViewRenderer,
        TwigViewRenderer $twigViewRenderer,
        ViewPathResolver $resolver
    ) {
        $this->fuelViewRenderer = $fuelViewRenderer;
        $this->twigViewRenderer = $twigViewRenderer;
        $this->resolver = $resolver;
    }

    public function render(string $file, array $data = [], array $options = []): string
    {
        $file = $this->resolver->resolveFilePath($file, self::SUPPORTED_EXTENSIONS);

        $ext = pathinfo($file, PATHINFO_EXTENSION);

        switch ($ext) {
            case 'twig':
                return $this->twigViewRenderer->render($file, $data, $options);
            case 'php':
                return $this->fuelViewRenderer->render($file, $data, $options);
            default:
                throw new Exception("Files with extension {$ext} are not supported");
        }
    }
}
