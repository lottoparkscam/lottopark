<?php

namespace Helpers;

use ReflectionClass;
use ReflectionException;

class ClassHelper
{
    /**
     * @throws ReflectionException
     */
    public static function getClassNameWithoutNamespace(string|object $class): string
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        $reflection = new ReflectionClass($class);
        return $reflection->getShortName();
    }
}
