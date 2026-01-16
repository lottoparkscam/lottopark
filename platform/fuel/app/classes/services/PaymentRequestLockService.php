<?php

namespace Services;

use Carbon\Carbon;
use Exception;
use Helpers_Time;
use Models\Whitelabel;
use Model_Whitelabel;
use Models\WhitelabelUser;
use Models\PaymentMethod;
use Models\PaymentRequestLock;
use Repositories\PaymentRequestLockRepository;
use Services\Logs\FileLoggerService;

class PaymentRequestLockService
{
    const SHOW_CAPTCHA_AFTER_NTH_TRY = 5;
    const MAX_AMOUNT_OF_TRIES_WITHOUT_LOCK = 100;
    const LOCK_TIME_IN_SECONDS = Helpers_Time::DAY_IN_SECONDS;

    private PaymentRequestLockRepository $paymentRequestLockRepository;
    protected FileLoggerService $fileLoggerService;
    private WhitelabelUser $whitelabelUser;
    private PaymentMethod $paymentMethod;

    public function __construct(PaymentRequestLockRepository $paymentRequestLockRepository, FileLoggerService $fileLoggerService)
    {
        $this->paymentRequestLockRepository = $paymentRequestLockRepository;
        $this->fileLoggerService = $fileLoggerService;
    }

    public function setUserAndPaymentMethod(WhitelabelUser $whitelabelUser, PaymentMethod $paymentMethod): void
    {
        $this->whitelabelUser = $whitelabelUser;
        $this->paymentMethod = $paymentMethod;
    }

    /** @throws Exception */
    public function logSendingRequest(): void
    {
        $paymentRequestLock = $this->getOrCreatePaymentRequestModel();
        $now = Carbon::now($paymentRequestLock->getTimezoneForField('first_request_date'));
        /** @var object $paymentRequestLock*/
        $lockTimeIsUp = $paymentRequestLock->firstRequestDate->diffInSeconds($now) >= self::LOCK_TIME_IN_SECONDS;

        if ($lockTimeIsUp) {
            $this->resetRequestsCount($paymentRequestLock);
        }

        $this->increaseRequestsAmount($paymentRequestLock);
    }

    /**
     * @param PaymentRequestLock|null $paymentRequestLock
     * @throws Exception
     */
    public function resetRequestsCount(PaymentRequestLock $paymentRequestLock = null): void
    {
        $paymentRequestLock = $paymentRequestLock ?? $this->getOrCreatePaymentRequestModel();
        $paymentRequestLock->set('requests_count', 0);
        $this->paymentRequestLockRepository->save($paymentRequestLock);
    }

    public function shouldDisplayCaptcha(WhitelabelUser $whitelabelUser): bool
    {
        return $this->paymentRequestLockRepository->shouldDisplayCaptcha(
            $whitelabelUser,
            self::SHOW_CAPTCHA_AFTER_NTH_TRY
        );
    }

    public function isSendingRequestLocked(): bool
    {
        $paymentRequestLock = $this->paymentRequestLockRepository->findOneByUserAndPaymentMethod(
            $this->whitelabelUser,
            $this->paymentMethod
        );

        if (empty($paymentRequestLock)) {
            return false;
        }

        /** @var object $paymentRequestLock */
        $shouldNotBeLockedByAmount = $paymentRequestLock->requestsCount < self::MAX_AMOUNT_OF_TRIES_WITHOUT_LOCK;
        if ($shouldNotBeLockedByAmount) {
            return false;
        }

        $now = Carbon::now($paymentRequestLock->getTimezoneForField('first_request_date'));
        return $lockTimeIsNotUp = $paymentRequestLock->firstRequestDate->diffInSeconds($now) < self::LOCK_TIME_IN_SECONDS;
    }

    /**
     * @param PaymentRequestLock $paymentRequestLock
     * @throws Exception
     */
    private function increaseRequestsAmount(PaymentRequestLock $paymentRequestLock): void
    {
        $additionalChanges = [];

        if ($paymentRequestLock->requests_count === 0) {
            $now = Carbon::now($paymentRequestLock->getTimezoneForField('first_request_date'));
            $additionalChanges['first_request_date'] = $now;
        }

        $this->paymentRequestLockRepository->updateFloatField(
            $paymentRequestLock->id,
            'requests_count',
            1,
            $additionalChanges
        );
    }

    /**
     * @return PaymentRequestLock
     * @throws Exception
     */
    private function getOrCreatePaymentRequestModel(): PaymentRequestLock
    {
        $paymentRequestLock = $this->paymentRequestLockRepository->findOneByUserAndPaymentMethod(
            $this->whitelabelUser,
            $this->paymentMethod
        );

        if (!empty($paymentRequestLock)) {
            return $paymentRequestLock;
        }

        $paymentRequestLock = new PaymentRequestLock();
        $paymentRequestLock->set([
            'whitelabel' => $this->whitelabelUser->whitelabel,
            'whitelabel_user' => $this->whitelabelUser,
            'payment_method' => $this->paymentMethod,
            'first_request_date' => Carbon::now()
        ]);
        $this->paymentRequestLockRepository->save($paymentRequestLock);

        return $paymentRequestLock;
    }
}
