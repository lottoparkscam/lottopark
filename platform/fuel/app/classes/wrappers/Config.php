<?php


namespace Wrappers;

use Fuel\Core\Config as CoreConfig;
use Wrappers\Decorators\ConfigContract;

/**
 * @codeCoverageIgnore
 */
class Config implements ConfigContract
{
    public function get(string $item, $default = null)
    {
        $is_nested_path = false !== strpos($item, '.');

        if ($is_nested_path) {
            [$file] = explode('.', $item);
            CoreConfig::load($file, true);
        } else {
            CoreConfig::load($item, true);
        }

        return CoreConfig::get($item, $default);
    }

    public function set(string $item, $value): void
    {
        CoreConfig::set($item, $value);
    }
}
