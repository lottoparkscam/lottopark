<?php


namespace Services\Shared\Logger;

interface LoggerAwareContract
{
    public function getLogger(): LoggerContract;

    public function setLogger(LoggerContract $logger): void;
}
