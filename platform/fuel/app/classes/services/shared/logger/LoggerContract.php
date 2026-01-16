<?php


namespace Services\Shared\Logger;

use Throwable;

interface LoggerContract
{
    public function logInfo(string $message, array $context = []): void;

    public function logWarning(string $message, array $context = []): void;

    public function logError(string $message, array $context = []): void;

    public function logErrorFromException(Throwable $exception, array $context = []): void;

    public function logSuccess(string $message, array $context = []): void;
}
