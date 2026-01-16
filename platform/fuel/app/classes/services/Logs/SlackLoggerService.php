<?php

namespace Services\Logs;

use Services\Logs\LogObject;
use Services\SlackService;
use Wrappers\Decorators\ConfigContract;

/** @coverage in platform/fuel/app/tests/feature/Tasks/SyncLogsTest.php */
class SlackLoggerService extends AbstractLoggerService
{
    public SlackService $slackService;
    public LogObject $log;

    public function __construct(
        ConfigContract $configContract,
        SlackService $slackService,
        LogObject $log
    ) {
        parent::__construct($configContract, $log);
        $this->slackService = $slackService;
        $this->log = $log;
    }

    public function setSource(string $source): void
    {
        parent::setSource($source);
        $this->slackService->setSource($source);
    }

    public function getPayload()
    {
        $logType = $this->log->type;

        $styles = $this->slackService->getIconAndColor($logType);

        $attachments = $this->getLogAttachments();
        $attachments['color'] = $styles['color'];

        $payload = [
            'username' => $this->slackService->name . ' ' . $logType,
            'icon_emoji' => $styles['icon'],
            'attachments' => [$attachments]
        ];

        return $payload;
    }

    /** 
     * @param bool $withDelay slack limits 1 request per 1s
     * We cannot send multi messages in one request so we have to add delay
     */
    public function sendLog(LogObject $log, bool $withDelay = false): bool
    {
        $this->log = $log;
        $channel = $this->slackService->prepareChannelName($this->log->type);
        $payload = $this->getPayload();

        $isSuccess = $this->slackService->sendFromPayload($payload, $channel);

        if ($withDelay) {
            sleep(1);
        }

        return $isSuccess;
    }

    /** @param LogObject[] $logs */
    public function sendLogs(array $logs): array
    {
        foreach ($logs as $lineNumber => $log) {
            $isNotSent = !$this->sendLog($log, true);

            if ($isNotSent) {
                return [
                    'success' => false,
                    'line' => $lineNumber
                ];
            }
        }

        return [
            'success' => true,
            'line' => null
        ];
    }

    public function getLogAttachments(): array
    {
        $logCount = $this->log->count ?? 1;
        // slack block limit
        $this->log->trace = mb_strimwidth($this->log->trace, 0, 2500, '...');
        $logDetails = $this->log->getLogDetails($logCount);
        $logDetails = mb_strimwidth($logDetails, 0, 2500, '...');

        return [
            'blocks' => [
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => $logDetails
                    ],
                ],
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => "*Trace:* ```{$this->log->trace}```"
                    ],
                ],
                [
                    'type' => 'divider'
                ]
            ]
        ];
    }
}
