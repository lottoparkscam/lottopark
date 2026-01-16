<?php

namespace Bus;

use Container;

abstract class Processor
{
    abstract protected function run(...$params);

    /**
     * Name should be in PascalCase
     * @param string $name
     * @param array $params
     * @return mixed
     */
    protected function applyBusByName(string $name, array $params = [])
    {
        $className = "{$name}Bus";

        /** @var BusInterface $busClass */
        $busClass = Container::get($className);
        return $busClass->apply(...$params);
    }
}
