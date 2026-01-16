<?php

namespace Services\Alert;

use Services\Logs\FileLoggerService;

class SlackProvider implements AlertProviderInterface
{
    private FileLoggerService $logger;

    public function __construct(FileLoggerService $logger)
    {
        $this->logger = $logger;
    }

    public function send(string $message, string $type, string $slackChannelName): bool
    {
        $this->logger->setSource($slackChannelName);
        $this->logger->error("Health Check Error: $type! \r\n $message");
        return true;
    }
}
