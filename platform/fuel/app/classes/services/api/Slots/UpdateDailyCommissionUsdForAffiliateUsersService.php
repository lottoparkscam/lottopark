<?php

namespace Services\Api\Slots;

use Carbon\Carbon;
use Exception;
use Helpers_General;
use Helpers_Time;
use Services\Logs\FileLoggerService;
use Models\WhitelabelAffSlotCommission;
use Repositories\WhitelabelAffSlotCommissionRepository;
use Stwarog\UowFuel\FuelEntityManager;

class UpdateDailyCommissionUsdForAffiliateUsersService
{
    private const SUPPORTED_TIERS = [
        Helpers_General::TYPE_TIER_FIRST,
        Helpers_General::TYPE_TIER_SECOND,
    ];

    private FileLoggerService $fileLoggerService;
    private WhitelabelAffSlotCommissionRepository $whitelabelAffSlotCommissionRepository;
    private FuelEntityManager $entityManager;

    public function __construct(
        FileLoggerService $fileLoggerService,
        WhitelabelAffSlotCommissionRepository $whitelabelAffSlotCommissionRepository,
        FuelEntityManager $entityManager
    ) {
        $this->fileLoggerService = $fileLoggerService;
        $this->whitelabelAffSlotCommissionRepository = $whitelabelAffSlotCommissionRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param integer $tier
     * @param boolean $skipDate - currently for tests only
     * @return void
     */
    public function updateCommissionsByTier(int $tier, bool $skipDate): void
    {
        if (!in_array($tier, self::SUPPORTED_TIERS)) {
            $this->fileLoggerService->error('Exit.. Trying to update disallowed commission with tier: ' . $tier);
            return;
        }

        $yesterdayDateWithoutTime = Carbon::now('UTC')
            ->subDay()
            ->format(Helpers_Time::DATE_FORMAT);

        $dateWithoutTime = $skipDate ? Carbon::now() : $yesterdayDateWithoutTime;

        $hasUpdatedCommissions = $this->whitelabelAffSlotCommissionRepository->hasUpdatedCasinoCommissionsOnDay($tier, $dateWithoutTime);
        if (!$skipDate && $hasUpdatedCommissions) {
            $this->fileLoggerService->info(
                "Casino affiliate commissions has been updated before. Tier: $tier, Date: $dateWithoutTime"
            );
            return;
        }

        $casinoCommissions = $this->whitelabelAffSlotCommissionRepository
            ->getCasinoCommissions($tier, $dateWithoutTime, $skipDate);

        foreach ($casinoCommissions as $casinoCommission) {
            $shouldNotAddRecordWithNegativeNgr = $casinoCommission['ngr_commission'] <= 0;
            if ($shouldNotAddRecordWithNegativeNgr) {
                continue;
            }

            try {
                $this->whitelabelAffSlotCommissionRepository->insert(
                    $casinoCommission,
                    $tier,
                    $dateWithoutTime,
                );
            } catch (Exception $e) {
                $affiliateId = $casinoCommission['whitelabel_aff_id'];
                $this->fileLoggerService->error(
                   "Problem with adding the casino commission to the database. 
                       Date: $dateWithoutTime, Tier: $tier, Aff id: $affiliateId, Message: " . $e->getMessage()
               );
            }
        }

        $this->entityManager->flush();
    }
}
