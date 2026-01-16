<?php

namespace Services\Shared;

interface DispatchAble
{
    public function is_enqueued(): bool;

    public function dispatch(): void;

    /**
     * This method should restore state of the class before any enquiry has been called.
     */
    public function reset(): void;
}
