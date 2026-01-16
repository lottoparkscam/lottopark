<?php


namespace Wrappers;

/**
 * @codeCoverageIgnore
 */
class Cli
{
    public function write($text = '', $foreground = null, $background = null): void
    {
        \Fuel\Core\Cli::write($text, $foreground, $background);
    }
}
