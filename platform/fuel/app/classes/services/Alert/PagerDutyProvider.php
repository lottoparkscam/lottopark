<?php

namespace Services\Alert;

use PagerDuty\TriggerEvent;
use PagerDuty\Exceptions\PagerDutyException;
use Services\Logs\FileLoggerService;
use Throwable;
use Wrappers\Decorators\ConfigContract;

class PagerDutyProvider implements AlertProviderInterface
{
    private ConfigContract $config;
    private FileLoggerService $logger;

    public function __construct(ConfigContract $config, FileLoggerService $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    public function send(string $message, string $type, string $slackChannelName): bool
    {
        try {
            $routingKey = $this->getRoutingKeyByType($type);

            $event = new TriggerEvent(
                $routingKey,
                $message,
                'Whitelotto',
                TriggerEvent::CRITICAL,
                true
            );

            $responseCode = $event->send();

            switch ($responseCode) {
                case 202:
                    return true;
                case 429:
                    $this->logger->error('Too many requests to PagerDuty! Slow down!');
                    return false;
                default:
                    $this->logger->error("PagerDuty response with $responseCode code.");
                    return false;
            }
        } catch (PagerDutyException $exception) {
            $this->logger->error("PagerDuty error. {$exception->getMessage()}");
            return false;
        } catch (Throwable $exception) {
            $this->logger->error("Error in PagerDutyProvider. {$exception->getMessage()}");
            return false;
        }
    }

    private function getRoutingKeyByType(string $type): string
    {
        return $this->config->get("alert.pagerDuty.$type");
    }
}
