<?php

namespace Modules\View\Fuel;

use Fuel\Core\View;
use Modules\View\ViewRendererContract;

/**
 * @codeCoverageIgnore
 * It's simple and concrete wrapper, so we don't have to test it.
 * Feature test covers this case as well.
 */
class FuelViewRenderer implements ViewRendererContract
{
    private View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function render(string $file, array $data = [], array $options = []): string
    {
        return $this->view::forge($file, $data, $options['auto_filter'] ?? null);
    }
}
