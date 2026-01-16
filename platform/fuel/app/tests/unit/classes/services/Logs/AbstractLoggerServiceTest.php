<?php

namespace Tests\Unit\Classes\Services\Logs;

use Container;
use Exception;
use Helpers_Time;
use Test_Unit;
use Services\Logs\AbstractLoggerService;
use Wrappers\Decorators\ConfigContract;
use Services\Logs\LogObject;

final class AbstractLoggerServiceTest extends Test_Unit
{
    private AbstractLoggerService $abstractLoggerService;
    private LogObject $log;

    public function setUp(): void
    {
        parent::setUp();

        $configContract = Container::get(ConfigContract::class);
        $this->log = Container::get(LogObject::class);

        $this->abstractLoggerService = $this->getMockForAbstractClass(
            AbstractLoggerService::class,
            [$configContract, $this->log]
        );
        $this->abstractLoggerService->configure();
    }

    /**
     * This function also tests configureDebugBackTrace() and configure()
     * @test
     */
    public function getBackTraceAsStringShouldHaveCurrentInvocation(): void
    {
        $trace = (new Exception())->getTrace();
        $this->abstractLoggerService->configure($trace);
        $backtrace = $this->abstractLoggerService->getBackTraceAsString();

        $this->assertStringContainsString(__CLASS__, $backtrace);
        $this->assertStringContainsString('getBackTraceAsStringShouldHaveCurrentInvocation', $backtrace);
    }

    /** @test */
    public function getLogLineShouldHaveValidData(): void
    {
        $message = 'test message';
        $type = 'INFO';
        $source = 'testing';
        $timestampWithTimezone = Helpers_Time::getTimestampWithTimezone();

        $log = $this->abstractLoggerService->prepareLogLine($message, $type, $source);

        $this->assertSame($type, $log->type);
        $this->assertSame('TESTING & DEFAULT', $log->source);
        $this->assertSame($message, $log->message);
        $this->assertSame($timestampWithTimezone, $log->date);
        $this->assertSame(1, $log->count);
        $this->assertStringContainsString(__CLASS__, $log->trace);
    }
}
