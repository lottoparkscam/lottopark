<?php


namespace Services\Shared\Logger;

use Closure;
use DateTime;
use Throwable;

/**
 * Class InMemoryLogger
 * Allows to log & subscribe to logs in "memory".
 */
class InMemoryLogger implements LoggerContract, SubscribeAble
{
    private array $subscribers = [];

    public function logInfo(string $message, array $context = []): void
    {
        $this->notify($this->getPreparedMessage('INFO', $message));
        $this->handleContext($context);
    }

    private function handleContext(array $context = []): void
    {
        foreach ($context as $k => $v) {
            if (is_array($v)) {
                continue;
            }
            $this->notify(sprintf("     # $k = $v"));
        }
    }

    private function getPreparedMessage(string $severity, string $message): string
    {
        $time = (new DateTime)->format('Y-m-d H:i');
        return sprintf('[%s][%s] # %s', $severity, $time, $message);
    }

    public function logWarning(string $message, array $context = []): void
    {
        $this->notify($this->getPreparedMessage('WARNING', $message));
        $this->handleContext($context);
    }

    public function logError(string $message, array $context = []): void
    {
        $this->notify($this->getPreparedMessage('ERROR', $message));
        $this->handleContext($context);
    }

    public function logErrorFromException(Throwable $exception, array $context = []): void
    {
        $message = sprintf('%s, %s, %s', $exception->getMessage(), $exception->getFile(), $exception->getLine());
        $context[] = $exception->getTraceAsString();
        $this->notify($this->getPreparedMessage('ERROR', $message));
        $this->handleContext($context);
    }

    public function logSuccess(string $message, array $context = []): void
    {
        $this->notify($this->getPreparedMessage('SUCCESS', $message));
        $this->handleContext($context);
    }

    public function subscribe(Closure $closure): void
    {
        $this->subscribers[] = $closure;
    }

    public function notify(string $message): void
    {
        foreach ($this->subscribers as $s) {
            call_user_func($s, $message);
        }
    }
}
