<?php

namespace Modules\View;

use Container;

/**
 * @codeCoverageIgnore
 */
class ViewHelper
{
    public static function render(string $file, array $data = [], array $options = []): string
    {
        return Container::get(ViewRendererContract::class)->render($file, $data, $options);
    }
}
