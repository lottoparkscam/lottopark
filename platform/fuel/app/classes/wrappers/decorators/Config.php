<?php


namespace Wrappers\Decorators;

use Wrappers\Config as WrappersConfig;

class Config implements ConfigContract
{
    private WrappersConfig $config;

    public function __construct(WrappersConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $item
     * @param null $default
     * @return mixed
     */
    public function get(string $item, $default = null)
    {
        $value = $this->config->get($item, $default);
        if (is_string($value) && in_array($value, ['true', 'false'])) {
            return $value === 'true';
        }

        return $value;
    }

    /**
     * @param string $item
     * @param mixed $value
     */
    public function set(string $item, $value): void
    {
        $this->config->set($item, $value);
    }
}
