<?php

namespace Tests\Unit\Classes\Services\Alert;

use Exception;
use Services\Alert\PagerDutyProvider;
use Services\Logs\FileLoggerService;
use Task\Alert\AbstractAlertListener;
use Test_Unit;
use Wrappers\Decorators\ConfigContract;

class PagerDutyProviderTest extends Test_Unit
{
    private FileLoggerService $logger;
    private ConfigContract $config;
    private PagerDutyProvider $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->logger = $this->createMock(FileLoggerService::class);
        $this->config = $this->createMock(ConfigContract::class);
        $this->provider = new PagerDutyProvider($this->config, $this->logger);
    }

    /** @test */
    public function send_incorrectConfig_throwException(): void
    {
        $type = AbstractAlertListener::TYPE_PAGE_STATUS;
        $this->config->expects($this->once())
            ->method('get')
            ->with("alert.pagerDuty.$type")
            ->willThrowException(new Exception());

        $this->logger->expects($this->once())
            ->method('error');

        $slackChannelName = 'health-check';
        $response = $this->provider->send('Test message', $type, $slackChannelName);
        $this->assertFalse($response);
    }
}
