<?php


namespace Wrappers\Decorators;

use Container;

/**
 * Class EventDecorator
 * Allows to overwrite static method call (from Fuel) and initialize
 * Container autowired class.
 */
abstract class EventDecorator
{
    public static function handle(array $data = [])
    {
        $instance = Container::get(get_called_class());
        $instance($data);
    }
}
