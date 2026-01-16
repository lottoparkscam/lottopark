<?php

namespace Tests\Unit\Classes\Services\Logs;

use Carbon\Carbon;
use Core\App;
use Fuel\Core\CacheNotFoundException;
use Helpers_Time;
use PHPUnit\Framework\MockObject\MockObject;
use Services\CacheService;
use Services\Files\FileService;
use Services\Logs\FileLoggerService;
use Services\Logs\LogObject;
use Test_Unit;
use Wrappers\Decorators\ConfigContract;

final class FileLoggerServiceTest extends Test_Unit
{
    private FileLoggerService $fileLoggerService;
    private FileLoggerService|MockObject $fileLoggerServiceMock;
    private ConfigContract $configContract;
    private App $app;
    private FileService $fileService;
    private LogObject $logObject;
    private CacheService $cacheService;

    public function setUp(): void
    {
        parent::setUp();
        $this->cacheService = $this->getMockBuilder(CacheService::class)
            ->onlyMethods(['getGlobalCache', 'setGlobalCache'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->logObject = $this->container->get(LogObject::class);
        $this->fileService = $this->createMock(FileService::class);
        $this->app = $this->createMock(App::class);
        $this->configContract = $this->createMock(ConfigContract::class);
        $this->fileLoggerServiceMock = $this->getMockBuilder(FileLoggerService::class)
            ->onlyMethods(['error', 'info', 'warning'])
            ->setConstructorArgs([
                $this->configContract,
                $this->app,
                $this->fileService,
                $this->logObject,
                $this->cacheService])
            ->getMock();
        $this->fileLoggerService = $this->container->get(FileLoggerService::class);
    }

    /** @test */
    public function createFileIfNotExists_ShouldCreateFileBasedOnCarbonSetTestNow(): void
    {
        $this->setFakeCarbon('2020-10-23 23:55:59');
        $this->fileLoggerService->configure();

        $expected = '/var/log/php/whitelotto/tests/2020/10/23.log';

        $filePath = $this->fileLoggerService->getPathToLogFile();
        $this->fileLoggerService->info('test' . __FUNCTION__);
        $this->assertFileExists($expected);
        $this->assertSame($expected, $filePath);
    }

    /** @test */
    public function createFileIfNotExists_ShouldCreateFileBasedOnCarbonSetTestNowWithTimezone(): void
    {
        $this->setFakeCarbon('2020-10-23 23:59:59', 'America/New_York');
        $this->fileLoggerService->configure();

        $expected = '/var/log/php/whitelotto/tests/2020/10/23.log';

        $filePath = $this->fileLoggerService->getPathToLogFile();

        $isSuccess = $this->fileLoggerService->info('test' . __FUNCTION__);

        $this->assertTrue($isSuccess);
        $this->assertFileExists($expected);
        $this->assertSame($expected, $filePath);
    }

    /** @test */
    public function shouldSendLogWhenProblemExistsAfterGivenTime_timeHasNotPassed_cacheIsEmpty(): void
    {
        $expectedMessage = 'testMessage';
        $timeInHours = 60;
        $cacheKey = 'testError';
        $expectedCacheTime = ($timeInHours * Helpers_Time::HOUR_IN_SECONDS) + Helpers_Time::HOUR_IN_SECONDS;
        $this->cacheService->expects($this->once())
            ->method('getGlobalCache')
            ->willThrowException(new CacheNotFoundException());

        $this->cacheService->expects($this->once())
            ->method('setGlobalCache')
            ->with($cacheKey, Carbon::now()->format(Helpers_Time::DATETIME_FORMAT), $expectedCacheTime);

        $this->fileLoggerServiceMock->expects($this->never())
            ->method('error');

        $this->fileLoggerServiceMock->expects($this->never())
            ->method('info');

        $this->fileLoggerServiceMock->expects($this->never())
            ->method('warning');

        $this->fileLoggerServiceMock->shouldSendLogWhenProblemExistsAfterGivenTime(
            $timeInHours,
            $expectedMessage,
            $cacheKey,
            FileLoggerService::LOG_TYPE_ERROR
        );
    }

    /** @test */
    public function shouldSendLogWhenProblemExistsAfterGivenTime_timeHasNotPassed_cacheReturnNowTime(): void
    {
        $cacheKey = 'testError';
        $expectedMessage = 'testMessage';

        $this->cacheService->expects($this->once())
            ->method('getGlobalCache')
            ->with($cacheKey)
            ->willReturn(Carbon::now()->format(Helpers_Time::DATETIME_FORMAT));

        $this->cacheService->expects($this->never())
            ->method('setGlobalCache');

        $this->fileLoggerServiceMock->expects($this->never())
            ->method('error');

        $this->fileLoggerServiceMock->expects($this->never())
            ->method('info');

        $this->fileLoggerServiceMock->expects($this->never())
            ->method('warning');

        $this->fileLoggerServiceMock->shouldSendLogWhenProblemExistsAfterGivenTime(
            60,
            $expectedMessage,
            $cacheKey,
            FileLoggerService::LOG_TYPE_ERROR
        );
    }

    /** @test */
    public function shouldSendLogWhenProblemExistsAfterGivenTime_timeHasNotPassed(): void
    {
        $expectedMessage = 'testMessage';
        $cacheKey = 'testError';

        $this->cacheService->expects($this->once())
            ->method('getGlobalCache')
            ->with($cacheKey)
            ->willReturn(Carbon::now()->subHours(59)->format(Helpers_Time::DATETIME_FORMAT));

        $this->cacheService->expects($this->never())
            ->method('setGlobalCache');

        $this->fileLoggerServiceMock->expects($this->never())
            ->method('error');

        $this->fileLoggerServiceMock->expects($this->never())
            ->method('info');

        $this->fileLoggerServiceMock->expects($this->never())
            ->method('warning');

        $this->fileLoggerServiceMock->shouldSendLogWhenProblemExistsAfterGivenTime(
            60,
            $expectedMessage,
            $cacheKey,
            FileLoggerService::LOG_TYPE_ERROR
        );
    }

    /** @test */
    public function shouldSendLogWhenProblemExistsAfterGivenTime_timePassed(): void
    {
        $expectedMessage = 'testMessage';
        $timeInHours = 60;
        $cacheKey = 'testError';

        $this->cacheService->expects($this->once())
            ->method('getGlobalCache')
            ->with($cacheKey)
            ->willReturn(Carbon::now()->subHours($timeInHours)->format(Helpers_Time::DATETIME_FORMAT));

        $this->cacheService->expects($this->never())
            ->method('setGlobalCache');

        $this->fileLoggerServiceMock->expects($this->once())
            ->method('error')
            ->with($expectedMessage);

        $this->fileLoggerServiceMock->expects($this->never())
            ->method('info');

        $this->fileLoggerServiceMock->expects($this->never())
            ->method('warning');

        $this->fileLoggerServiceMock->shouldSendLogWhenProblemExistsAfterGivenTime(
            $timeInHours,
            $expectedMessage,
            $cacheKey,
            FileLoggerService::LOG_TYPE_ERROR
        );
    }

    /** @test */
    public function shouldSendLogWhenProblemExistsAfterGivenTime_timePassed_logTypeInfo(): void
    {
        $expectedMessage = 'testMessage';
        $timeInHours = 60;
        $cacheKey = 'testError';

        $this->cacheService->expects($this->once())
            ->method('getGlobalCache')
            ->with($cacheKey)
            ->willReturn(Carbon::now()->subHours($timeInHours)->format(Helpers_Time::DATETIME_FORMAT));

        $this->cacheService->expects($this->never())
            ->method('setGlobalCache');

        $this->fileLoggerServiceMock->expects($this->never())
            ->method('error');

        $this->fileLoggerServiceMock->expects($this->once())
            ->method('info')
            ->with($expectedMessage);

        $this->fileLoggerServiceMock->expects($this->never())
            ->method('warning');

        $this->fileLoggerServiceMock->shouldSendLogWhenProblemExistsAfterGivenTime(
            $timeInHours,
            $expectedMessage,
            $cacheKey,
            FileLoggerService::LOG_TYPE_INFO
        );
    }

    /** @test */
    public function shouldSendLogWhenProblemExistsAfterGivenTime_timePassed_logTypeWarning(): void
    {
        $expectedMessage = 'testMessage';
        $timeInHours = 60;
        $cacheKey = 'testError';

        $this->cacheService->expects($this->once())
            ->method('getGlobalCache')
            ->with($cacheKey)
            ->willReturn(Carbon::now()->subHours($timeInHours)->format(Helpers_Time::DATETIME_FORMAT));

        $this->cacheService->expects($this->never())
            ->method('setGlobalCache');

        $this->fileLoggerServiceMock->expects($this->never())
            ->method('error');

        $this->fileLoggerServiceMock->expects($this->never())
            ->method('info');

        $this->fileLoggerServiceMock->expects($this->once())
            ->method('warning')
            ->with($expectedMessage);

        $this->fileLoggerServiceMock->shouldSendLogWhenProblemExistsAfterGivenTime(
            $timeInHours,
            $expectedMessage,
            $cacheKey,
            FileLoggerService::LOG_TYPE_WARNING
        );
    }
}
