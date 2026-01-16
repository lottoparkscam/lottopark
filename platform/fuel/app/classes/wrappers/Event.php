<?php


namespace Wrappers;

/**
 * @codeCoverageIgnore
 */
class Event
{
    public function register(string $event, $callback): void
    {
        \Fuel\Core\Event::register($event, $callback);
    }

    public function trigger(string $event, $data = null): void
    {
        \Fuel\Core\Event::trigger($event, $data);
    }
}
