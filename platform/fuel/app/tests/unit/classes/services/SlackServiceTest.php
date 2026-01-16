<?php

namespace Tests\Unit\Classes\Services\Logs;

use Test_Unit;
use Services\SlackService;

final class SlackServiceTest extends Test_Unit
{
    private SlackService $slackService;

    public function setUp(): void
    {
        parent::setUp();
        $this->slackService = $this->container->get(SlackService::class);
    }

    /**
     * Check just one type, all of them works the same
     * @test
     */
    public function getIconAndColor_shouldReturnValidBasedOnType(): void
    {
        $expectedIcon = ':information_source:';
        $expectedColor = '#17a2b8';
        $type = 'INFO';

        $received = $this->slackService->getIconAndColor($type);
        extract($received);

        $this->assertSame($expectedIcon, $icon);
        $this->assertSame($expectedColor, $color);
    }

    /** @test */
    public function prepareChannelName_shouldReturnApiWithoutType(): void
    {
        // set api source
        $this->slackService->setSource('api');

        $type = 'INFO';
        $expected = 'logs-whitelotto-test-api';
        $actual = $this->slackService->prepareChannelName($type);

        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function prepareChannelName_shouldReturnDefaultWithLogType(): void
    {
        $type = 'INFO';
        $expected = 'logs-whitelotto-test-info';
        $actual = $this->slackService->prepareChannelName($type);

        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function prepareChannelName_shouldOverrideChannel(): void
    {
        $type = 'INFO';
        $defaultChannel = 'logs-whitelotto-test-info';
        $overrodeChannel = 'testing-overrode-channel';
        $this->slackService->overrideChannels[$defaultChannel] = $overrodeChannel;

        $expected = $overrodeChannel;
        $actual = $this->slackService->prepareChannelName($type);

        $this->assertSame($expected, $actual);
    }
}
