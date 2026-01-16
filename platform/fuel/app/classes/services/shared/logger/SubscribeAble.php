<?php


namespace Services\Shared\Logger;

use Closure;

interface SubscribeAble
{
    public function subscribe(Closure $closure): void;

    public function notify(string $message): void;
}
