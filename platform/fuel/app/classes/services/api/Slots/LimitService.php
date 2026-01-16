<?php

namespace Services\Api\Slots;

use Exception;
use Models\Whitelabel;
use Models\WhitelabelSlotProvider;
use Repositories\SlotTransactionRepository;
use Repositories\WhitelabelRepository;
use Repositories\WhitelabelSlotProviderRepository;
use Services\Logs\FileLoggerService;
use Services\MailerService;

class LimitService
{
    private WhitelabelSlotProviderRepository $whitelabelSlotProviderRepository;
    private MailerService $mailerService;
    private SlotTransactionRepository $slotTransactionRepository;
    private WhitelabelRepository $whitelabelRepository;
    private FileLoggerService $fileLoggerService;

    public function __construct(
        WhitelabelSlotProviderRepository $whitelabelSlotProviderRepository,
        MailerService $mailerService,
        SlotTransactionRepository $slotTransactionRepository,
        WhitelabelRepository $whitelabelRepository,
        FileLoggerService $fileLoggerService
    ) {
        $this->whitelabelSlotProviderRepository = $whitelabelSlotProviderRepository;
        $this->mailerService = $mailerService;
        $this->slotTransactionRepository = $slotTransactionRepository;
        $this->whitelabelRepository = $whitelabelRepository;
        $this->fileLoggerService = $fileLoggerService;
    }

    /**
     * @param float $amountInUsd
     * @param WhitelabelSlotProvider $whitelabelSlotProvider
     * @return bool
     * @throws Exception
     */
    public function isMonthlyLimitSufficientPerWhitelabel(
        float $amountInUsd,
        WhitelabelSlotProvider $whitelabelSlotProvider
    ): bool {
        /** @var Whitelabel $whitelabel */
        $whitelabel = $whitelabelSlotProvider->whitelabel;

        if ($whitelabelSlotProvider->isLimitDisabled()) {
            return true;
        }

        $leftLimitInUsd = $this->getLeftLimitInUsd($whitelabel->id);
        $isWhitelabelLimitReached = $leftLimitInUsd <= 0.0;
        if ($isWhitelabelLimitReached) {
            $this->fileLoggerService->warning('Insufficient whitelabel max_monthly_money_around_usd limit to do bet transaction');
            $this->mailerService->send(
                'peter@whitelotto.com',
                'Slots limit reached',
                "Slots limit for whitelabel {$whitelabel->name} reached. Purchasing games is locked."
            );
            return false;
        }

        $leftLimitBeforeThisBet = $leftLimitInUsd + $amountInUsd;

        // It is hardcoded value and could be replaced to dynamic in CRM later
        $notifyBoundaryInUsd = 5000;
        $limitIsBelowNotifyBoundary = $leftLimitBeforeThisBet >= $notifyBoundaryInUsd &&
            $leftLimitInUsd < $notifyBoundaryInUsd;
        if ($limitIsBelowNotifyBoundary) {
            $this->mailerService->send(
                'peter@whitelotto.com',
                'Slots limit almost reached',
                "Slots limit for whitelabel {$whitelabel->name} almost reached. Left limit: $leftLimitInUsd"
            );
        }

        return $leftLimitInUsd >= $amountInUsd;
    }

    public function isWhitelabelLimitReached(int $whitelabelId): bool
    {
        $betsSum = $this->slotTransactionRepository->sumInUsdWithBetActionForCurrentMonth($whitelabelId);
        $whitelabelLimit = $this->whitelabelSlotProviderRepository->getWhitelabelLimitInUsd($whitelabelId);

        return $betsSum >= $whitelabelLimit;
    }

    /** @throws Exception */
    public function getLeftLimitInUsd(int $whitelabelId): float
    {
        $betsSum = $this->slotTransactionRepository->sumInUsdWithBetActionForCurrentMonth($whitelabelId);
        $whitelabelLimit = $this->whitelabelSlotProviderRepository->getWhitelabelLimitInUsd($whitelabelId);
        $leftLimit = $whitelabelLimit - $betsSum;

        return max($leftLimit, 0.0);
    }
}
