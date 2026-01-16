<?php

namespace Services;

use Wrappers\Decorators\ConfigContract;
use Throwable;
use GuzzleHttp\RequestOptions;
use Services\Logs\FileLoggerService;
use Core\App;
use GuzzleHttp\Client;
use Interfaces\Logs\LogsInterface;

/** @coverage in platform/fuel/app/tests/feature/Tasks/SyncLogsTest.php */
class SlackService implements LogsInterface
{
    public string $source = 'default';
    public array $overrideChannels = [];
    public string $name;
    protected int $timeout;
    private ConfigContract $configContract;
    private FileLoggerService $fileLoggerService;
    private App $app;
    private Client $client;
    private bool $isFileError = false;

    private const CHANNEL_WEBHOOK_URLS = [
        "logs-whitelotto-pre-master-emergency" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-pre-master-error" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-pre-master-info" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-pre-master-warning" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-pre-master-wordpress" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-production-api" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-production-assistant" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-production-emergency" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-production-error" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-production-health-check" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-production-info" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-production-warning" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-production-wordpress" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-review-app-api" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-review-app-error" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-review-app-info" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-review-app-warning" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-review-app-wordpress" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-staging-api" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-staging-emergency" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-staging-error" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-staging-health-check" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-staging-info" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-staging-warning" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-staging-wordpress" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-staging-health-check-wordpress" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-texts-api" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-texts-error" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-texts-info" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-texts-warning" => "https://hooks.slack.com/services/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",
        "logs-whitelotto-texts-wordpress" => "https://hooks.slack.com/services/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",
    ];
    private const DEFAULT_WEBHOOK = self::CHANNEL_WEBHOOK_URLS['logs-whitelotto-production-info'];
    

    public function __construct(
        ConfigContract $configContract,
        FileLoggerService $fileLoggerService,
        App $app,
        HttpService $httpService
    ) {
        $this->fileLoggerService = $fileLoggerService;
        $this->configContract = $configContract;
        $this->app = $app;
        $this->client = $httpService->getClient();

        $this->configureSlack();
    }

    public function setIsFileError(): void
    {
        $this->isFileError = true;
    }

    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    private function configureSlack(): void
    {
        $this->name = $this->configContract->get('slack.details.name');
        $this->timeout = $this->configContract->get('slack.details.timeout') ?? 2;
    }

    public function isSlackConfiguredProperly(): bool
    {
        return !empty(self::CHANNEL_WEBHOOK_URLS) && !empty($this->name);
    }

    public function getIconAndColor(string $logType): array
    {
        return self::LOG_STYLES[$logType] ?? self::LOG_STYLES[self::TYPE_UNKNOWN];
    }

    public function sendFromPayload(array $payload, string $channel): bool
    {
        // slack provides default channel, we don't have to pass it in .env for dev env
        // if we don't pass it in dev env we should receive logs as private message after setting up incoming hooks
        $isNotDevEnv = !$this->app->isDevelopment();
        if ($isNotDevEnv) {
            $payload['channel'] = $channel;
        }

        return $this->send($payload);
    }

    private function send(array $payload): bool
    {
        $url = isset($payload['channel']) && $payload['channel'] 
            ? $this->getWebhookUrlByName($payload['channel']) 
            : self::DEFAULT_WEBHOOK;
        try {
            $response = $this->client->post(
                $url,
                [
                    'timeout' => $this->timeout,
                    RequestOptions::JSON => $payload
                ]
            );
            $isSuccess = $response->getStatusCode() === 200;
        } catch (Throwable $e) {
            $isSuccess = false;
            $apiNotResponding = $e->getCode() === 500;
            $channelNotFound = $e->getCode() === 404;
            $channelAccessDenied = $e->getCode() === 403;

            if (($channelNotFound || $channelAccessDenied || $apiNotResponding) && $this->app->isTest()) {
                return $isSuccess;
            }

            $isSlackLimitReached = $e->getCode() === 429;
            if ($isSlackLimitReached) {
                sleep(1);
                $this->send($payload);
                return false;
            }

            if (!$this->isFileError) {
                if (!$this->app->isTest()) {
                    if ($channelNotFound) {
                        $error = 'Slack channel not found: ' . $e->getMessage();
                        $this->fileLoggerService->error($error, true);
                        return $isSuccess;
                    }
    
                    if ($channelAccessDenied) {
                        $error = 'Denied access to slack channel: ' . $e->getMessage();
                        $this->fileLoggerService->error($error, true);
                        return $isSuccess;
                    }
    
                    if ($apiNotResponding) {
                        $error = 'Slack server answered with 500: ' . $e->getMessage();
                        $this->fileLoggerService->error($error, true);
                        return $isSuccess;
                    }
                }

                $error = 'Guzzle exception: ' . $e->getMessage() . $e->getCode();
                $this->fileLoggerService->error($error, true);
                return $isSuccess;
            }
        }

        return $isSuccess;
    }

    public function sendMessage(string $message, string $logType): bool
    {
        $styles = $this->getIconAndColor($logType);
        $color = $styles['color'];
        $icon = $styles['icon'];

        $attachments = [
            [
                'color' => $color,
                'text' => $message
            ]
        ];

        $channel = $this->prepareChannelName($logType);

        $payload = [
            'username'    => $this->name . " $logType",
            'icon_emoji'  => $icon,
            'attachments' => $attachments
        ];

        // slack provides default channel, we don't have to pass it in .env for dev env
        // if we don't pass it in dev env we should receive logs as private message after setting up incoming hooks
        $isNotDevEnv = !$this->app->isDevelopment();
        if ($isNotDevEnv) {
            $payload['channel'] = $channel;
        }

        return $this->send($payload);
    }


    /**
     * default channel name example: logs-whitelotto-development-api-error
     * Can be overwritten in protected static $overrideChannels
     */
    public function prepareChannelName(string $logType): string
    {
        $isDevEnv = $this->app->isDevelopment();
        // if we want to have slack messages on loc env we shouldn't pass channel
        if ($isDevEnv) {
            return '';
        }

        $logType = strtolower($logType);

        // send assistant logs to **-info channel
        $isAssistant = $logType === strtolower(self::TYPE_ASSISTANT);
        $shouldAddAssistantLogsToInfoChannel = $isAssistant && !$this->app->isProduction();
        if ($shouldAddAssistantLogsToInfoChannel) {
            $logType = self::TYPE_INFO;
        }

        $shouldAddAssistantLogsToAssistantChannel = $isAssistant && $this->app->isProduction();
        if ($shouldAddAssistantLogsToAssistantChannel) {
            $logType = self::ASSISTANT_CHANNEL;
        }

        $channel = self::SLACK_CHANNEL_PREFIX . $this->app->getServerType();

        $shouldAddSourceToChannel = $this->source !== self::SOURCE_DEFAULT;
        if ($shouldAddSourceToChannel) {
            $channel .= '-'  . $this->source;
        }

        // separate logs only for default. Other logs have only one channel
        $shouldAddTypeToChannel = $this->source === self::SOURCE_DEFAULT;
        if ($shouldAddTypeToChannel) {
            $channel .=  '-' . $logType;
        }

        $overrideChannel = !empty($this->overrideChannels[$channel]);

        return $overrideChannel ? $this->overrideChannels[$channel] : $channel;
    }

    private function getWebhookUrlByName(string $name) 
    {
        return self::CHANNEL_WEBHOOK_URLS[$name] ?? self::DEFAULT_WEBHOOK;
    }
    
    public function error(string $message): bool
    {
        return $this->sendMessage($message, self::TYPE_ERROR);
    }

    public function info(string $message): bool
    {
        return $this->sendMessage($message, self::TYPE_INFO);
    }

    public function assistant(string $message): bool
    {
        return $this->sendMessage($message, self::TYPE_ASSISTANT);
    }

    public function warning(string $message): bool
    {
        return $this->sendMessage($message, self::TYPE_WARNING);
    }
}
