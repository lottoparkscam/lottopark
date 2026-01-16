<?php

namespace Modules\View;

use Modules\View\Fuel\FuelViewRenderer;
use Modules\View\Twig\TwigViewRenderer;

/**
 * Concrete implementations:
 *
 * @see TwigViewRenderer
 * @see FuelViewRenderer
 * @see ViewRenderer
 */
interface ViewRendererContract
{
    public function render(string $file, array $data = [], array $options = []): string;
}
