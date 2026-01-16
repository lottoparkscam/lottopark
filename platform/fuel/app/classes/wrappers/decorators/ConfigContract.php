<?php


namespace Wrappers\Decorators;

interface ConfigContract
{
    public function get(string $item, $default = null);

    public function set(string $item, $value): void;
}
