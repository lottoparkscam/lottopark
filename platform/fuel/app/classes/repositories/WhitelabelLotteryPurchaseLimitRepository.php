<?php

namespace Repositories;

use Carbon\Carbon;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Container;
use Models\WhitelabelLotteryPurchaseLimit;
use Repositories\Orm\AbstractRepository;
use Stwarog\UowFuel\FuelEntityManager;
use Throwable;
use Services\Logs\FileLoggerService;

class WhitelabelLotteryPurchaseLimitRepository extends AbstractRepository
{
    private FuelEntityManager $fuelEntityManager;

    public function __construct(WhitelabelLotteryPurchaseLimit $model, FuelEntityManager $fuelEntityManager)
    {
        parent::__construct($model);
        $this->fuelEntityManager = $fuelEntityManager;
    }

    public function findOneByUserIdAndWhitelabelLotteryId(int $userId, int $whitelabelLotteryId): ?WhitelabelLotteryPurchaseLimit
    {
        $this->pushCriterias(
            [
                new Model_Orm_Criteria_Where('whitelabel_user_id', $userId),
                new Model_Orm_Criteria_Where('whitelabel_lottery_id', $whitelabelLotteryId),
            ]
        );

        return $this->findOne();
    }

    /**
     * Adds new user purchase limit entries.
     * If user already purchased some tickets, then updates entry with counter and updatedAt fields.
     *
     * There are 2 use cases handled:
     * - user has not purchased before
     * - user has purchased before, so we need to update counter
     *
     * Returns false if one of the add/update failed - caller should rollback db transaction.
     * Returns true if all add/updates succeeded.
     * @param WhitelabelLotteryPurchaseLimit[] $whitelabelLotteryPurchaseLimit
     */
    public function insertOrUpdateEntries(array $whitelabelLotteryPurchaseLimit): bool
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        foreach ($whitelabelLotteryPurchaseLimit as $item) {
            try {
                $existingEntryToUpdate = $this->findOneByUserIdAndWhitelabelLotteryId($item->whitelabelUserId, $item->whitelabelLotteryId);
                $limitEntry = new WhitelabelLotteryPurchaseLimit();

                if (!empty($existingEntryToUpdate)) {
                    $limitEntry = $existingEntryToUpdate;
                    $limitEntry->counter += $item->counter;
                } else {
                    $limitEntry->whitelabelLotteryId = $item->whitelabelLotteryId;
                    $limitEntry->whitelabelUserId = $item->whitelabelUserId;
                    $limitEntry->counter = $item->counter;
                    $limitEntry->createdAt = Carbon::now();
                }

                $limitEntry->updatedAt = Carbon::now();
                $this->fuelEntityManager->save($limitEntry);
            } catch (Throwable $exception) {
                $totalOperations = count($whitelabelLotteryPurchaseLimit);
                $currentItemArray[] = $item->to_array();
                $currentItemForLogJson = json_encode($currentItemArray);
                $fileLoggerService->error(
                    "Could not insert/update purchase limit entry for User ID: {$item->whitelabelUserId} and Whitelabel Lottery ID: {$item->whitelabelLotteryId}. 
                    The entire purchase transaction should be rollbacked with {$totalOperations} operations. 
                    The object that has broken transaction is {$currentItemForLogJson}.
                    Detailed exception message: {$exception->getMessage()}"
                );
                return false;
            }
        }

        try {
            $this->fuelEntityManager->flush();
        } catch (Throwable $exception) {
            $fileLoggerService->error(
                $exception->getMessage()
            );
            return false;
        }

        return true;
    }
}
