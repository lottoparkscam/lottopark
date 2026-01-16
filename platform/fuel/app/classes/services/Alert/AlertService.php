<?php

namespace Services\Alert;

class AlertService
{
    private AlertProviderInterface $alertProvider;

    public function __construct(AlertProviderInterface $alertProvider)
    {
        $this->alertProvider = $alertProvider;
    }

    public function send(string $message, string $type, string $slackChannelName): bool
    {
        return $this->alertProvider->send($message, $type, $slackChannelName);
    }
}
