<?php

namespace Feature\Classes\Services;

use Carbon\Carbon;
use Container;
use Factory_Orm_Whitelabel_User;
use Helpers_Time;
use Models\Whitelabel;
use Models\WhitelabelUser;
use Models\PaymentMethod;
use Models\PaymentRequestLock;
use Repositories\PaymentRequestLockRepository;
use Services\PaymentRequestLockService;
use Test_Feature;
use Wrappers\Orm;
use Services\Logs\FileLoggerService;

class PaymentRequestLockTest extends Test_Feature
{
    private PaymentRequestLockService $paymentRequestLockService;
    private PaymentRequestLockRepository $paymentRequestLockRepository;
    private FileLoggerService $fileLoggerService;
    private Whitelabel $whitelabel;
    private WhitelabelUser $whitelabelUser;
    private PaymentMethod $paymentMethod;

    public function setUp(): void
    {
        parent::setUp();
        Orm::disableCaching();
        $this->paymentRequestLockRepository = Container::get(PaymentRequestLockRepository::class);
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        $this->paymentRequestLockService = new PaymentRequestLockService(
            $this->paymentRequestLockRepository,
            $this->fileLoggerService
        );

        $this->whitelabel = Whitelabel::find(1);
        $whitelabelUser = WhitelabelUser::find(1);

        if (empty($this->whitelabelUser)) {
            $whitelabelUserFactory = new Factory_Orm_Whitelabel_User(['login' => 'paymentRequestLockTest']);
            $this->whitelabelUser = $whitelabelUserFactory->build();
            $this->models_to_rewind[] = $this->whitelabelUser;
        } else {
            $this->whitelabelUser = $whitelabelUser;
        }

        $this->paymentMethod = PaymentMethod::find(1);
        $this->paymentRequestLockService->setUserAndPaymentMethod($this->whitelabelUser, $this->paymentMethod);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->deleteAllLogs();
    }

    /** @test  */
    public function logSendingRequest_shouldCreate_IfNotExists()
    {
        // When
        $this->paymentRequestLockService->logSendingRequest();

        // Then
        $newPaymentRequestLock = PaymentRequestLock::find('first');
        $this->assertInstanceOf(PaymentRequestLock::class, $newPaymentRequestLock);
    }

    /** @test  */
    public function logSendingRequest_shouldUpdate_IfExists()
    {
        // When
        $this->paymentRequestLockService->logSendingRequest();
        $this->paymentRequestLockService->logSendingRequest();

        // Then
        $paymentRequestLocks = PaymentRequestLock::find('all');
        $this->assertCount(1, $paymentRequestLocks);
    }

    /** @test */
    public function logSendingRequest_shouldReset_IfLockTimeIsUp()
    {
        // When
        $this->paymentRequestLockService->logSendingRequest();
        $this->setLockTimeToBeUp();
        $this->paymentRequestLockService->logSendingRequest();

        // Then
        /** @var PaymentRequestLock $newPaymentRequestLock */
        $newPaymentRequestLock = PaymentRequestLock::find('first');
        $this->assertSame(1, $newPaymentRequestLock->requestsCount);
    }

    /** @test */
    public function logSendingRequest_shouldIncrease_IfLockTimeIsNotUp()
    {
        // When
        $this->paymentRequestLockService->logSendingRequest();
        $this->paymentRequestLockService->logSendingRequest();

        // Then
        /** @var PaymentRequestLock $newPaymentRequestLock */
        $newPaymentRequestLock = PaymentRequestLock::find('first');
        $this->assertSame(2, $newPaymentRequestLock->requestsCount);
    }

    /** @test */
    public function resetRequestsCount()
    {
        // When
        $this->paymentRequestLockService->logSendingRequest();
        $this->paymentRequestLockService->logSendingRequest();
        $this->paymentRequestLockService->logSendingRequest();
        /** @var PaymentRequestLock $paymentRequestLock */
        $paymentRequestLock = PaymentRequestLock::find('first');
        $this->assertSame(3, $paymentRequestLock->requestsCount);
        $this->paymentRequestLockService->resetRequestsCount();

        // Then
        /** @var PaymentRequestLock $paymentRequestLock */
        $paymentRequestLock = PaymentRequestLock::find('first');
        $this->assertSame(0, $paymentRequestLock->requestsCount);
    }

    /** @test */
    public function shouldDisplayCaptcha_divideByNthTries_shouldDisplay()
    {
        // When
        for ($i = 0; $i < PaymentRequestLockService::SHOW_CAPTCHA_AFTER_NTH_TRY; $i++) {
            $this->paymentRequestLockService->logSendingRequest();
        }

        // Then
        $shouldDisplayCaptcha = $this->paymentRequestLockService->shouldDisplayCaptcha($this->whitelabelUser);
        $this->assertTrue($shouldDisplayCaptcha);
    }

    /** @test */
    public function shouldDisplayCaptcha_nowDivideByNthTries_shouldNotDisplay()
    {
        // When
        for ($i = 0; $i <= PaymentRequestLockService::SHOW_CAPTCHA_AFTER_NTH_TRY; $i++) {
            $this->paymentRequestLockService->logSendingRequest();
        }

        // Then
        $shouldDisplayCaptcha = $this->paymentRequestLockService->shouldDisplayCaptcha($this->whitelabelUser);
        $this->assertFalse($shouldDisplayCaptcha);
    }

    /** @test */
    public function isSendingRequestLocked_aboveNTriesAndLockTimeIsNotUp_true()
    {
        // When
        for ($i = 0; $i <= PaymentRequestLockService::MAX_AMOUNT_OF_TRIES_WITHOUT_LOCK; $i++) {
            $this->paymentRequestLockService->logSendingRequest();
        }

        // Then
        $isSendingRequestLocked = $this->paymentRequestLockService->isSendingRequestLocked();
        $this->assertTrue($isSendingRequestLocked);
    }

    /** @test */
    public function isSendingRequestLocked_aboveNTriesAndLockTimeIsNotUpButClose_true()
    {
        // When
        for ($i = 0; $i <= PaymentRequestLockService::MAX_AMOUNT_OF_TRIES_WITHOUT_LOCK; $i++) {
            $this->paymentRequestLockService->logSendingRequest();
        }

        $newTimeCloseToBoundary = Carbon::now()->subSeconds(PaymentRequestLockService::LOCK_TIME_IN_SECONDS)
        ->addSeconds(10)
        ->format(Helpers_Time::DATETIME_FORMAT);

        $newPaymentRequestLock = PaymentRequestLock::find('first');
        $newPaymentRequestLock->set([
            'first_request_date' => $newTimeCloseToBoundary
        ]);
        $newPaymentRequestLock->save();

        // Then
        $isSendingRequestLocked = $this->paymentRequestLockService->isSendingRequestLocked();
        $this->assertTrue($isSendingRequestLocked);
    }

    /** @test */
    public function isSendingRequestLocked_aboveNTriesAndLockTimeIsUp_false()
    {
        // When
        for ($i = 0; $i <= PaymentRequestLockService::MAX_AMOUNT_OF_TRIES_WITHOUT_LOCK + 1; $i++) {
            $this->paymentRequestLockService->logSendingRequest();
        }
        $this->setLockTimeToBeUp();

        // Then
        $isSendingRequestLocked = $this->paymentRequestLockService->isSendingRequestLocked();
        $this->assertFalse($isSendingRequestLocked);
    }

    /** @test */
    public function isSendingRequestLocked_belowNTries_false()
    {
        // When
        for ($i = 0; $i < PaymentRequestLockService::MAX_AMOUNT_OF_TRIES_WITHOUT_LOCK - 1; $i++) {
            $this->paymentRequestLockService->logSendingRequest();
        }

        // Then
        $isSendingRequestLocked = $this->paymentRequestLockService->isSendingRequestLocked();
        $this->assertFalse($isSendingRequestLocked);
    }

    private function deleteAllLogs(): void
    {
        $allLogs = PaymentRequestLock::find('all');
        foreach ($allLogs as $log) {
            $log->delete();
        }
    }

    private function setLockTimeToBeUp(): void
    {
        $newPaymentRequestLock = PaymentRequestLock::find('first');
        $newPaymentRequestLock->set([
            'first_request_date' => Carbon::now()->subDays(1)->format(Helpers_Time::DATETIME_FORMAT)
        ]);
        $newPaymentRequestLock->save();
    }
}
