<?php

namespace Tests\Unit\Classes\Services;

use Carbon\Carbon;
use PHPUnit\Framework\MockObject\MockObject;
use Repositories\Orm\WhitelabelUserRepository;
use Repositories\WhitelabelRepository;
use Services\Auth\UserActivationService;
use Services\MailerService;
use Test_Unit;

class UserActivationServiceTest extends Test_Unit
{
    private UserActivationService $userActivationServiceUnderTest;
    private WhitelabelRepository|MockObject $whitelabelRepositoryMock;
    private WhitelabelUserRepository|MockObject $whitelabelUserRepositoryMock;
    private MailerService|MockObject $mailerServiceMock;
    public function setUp(): void
    {
        parent::setUp();
        $this->whitelabelRepositoryMock = $this->createMock(WhitelabelRepository::class);
        $this->whitelabelUserRepositoryMock = $this->createMock(WhitelabelUserRepository::class);
        $this->mailerServiceMock = $this->createMock(MailerService::class);
        $this->userActivationServiceUnderTest = new UserActivationService(
            $this->whitelabelRepositoryMock,
            $this->whitelabelUserRepositoryMock,
            $this->mailerServiceMock,
        );
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function isResendEmailLimitReached(): void
    {
        $currentDate = Carbon::now('UTC');
        $dateTwentyFourHourAgo = Carbon::now()->subHours(24);

        $resultForResendDateContainNull = $this->userActivationServiceUnderTest->isResendEmailLimitReached(null);
        $this->assertFalse($resultForResendDateContainNull);

        $resultWhenDayNotPassed = $this->userActivationServiceUnderTest->isResendEmailLimitReached($currentDate);
        $this->assertTrue($resultWhenDayNotPassed);

        $resultWhenDayPassed = $this->userActivationServiceUnderTest->isResendEmailLimitReached($dateTwentyFourHourAgo);
        $this->assertFalse($resultWhenDayPassed);
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function isResendEmailLimitNotReached(): void
    {
        $currentDate = Carbon::now('UTC');
        $dateTwentyFourHourAgo = Carbon::now()->subHours(24);

        $resultForResendDateContainNull = $this->userActivationServiceUnderTest->isResendEmailLimitNotReached(null);
        $this->assertTrue($resultForResendDateContainNull);

        $resultWhenDayNotPassed = $this->userActivationServiceUnderTest->isResendEmailLimitNotReached($currentDate);
        $this->assertFalse($resultWhenDayNotPassed);

        $resultWhenDayPassed = $this->userActivationServiceUnderTest->isResendEmailLimitNotReached($dateTwentyFourHourAgo);
        $this->assertTrue($resultWhenDayPassed);
    }
}
