<?php

namespace Bus;

interface BusInterface
{
    public function apply(...$params);
}
