<?php

namespace Services\Alert;

interface AlertProviderInterface
{
    public function send(string $message, string $type, string $slackChannelName): bool;
}
