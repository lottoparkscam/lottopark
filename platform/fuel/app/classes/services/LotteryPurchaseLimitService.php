<?php

namespace Services;

use Container;
use ErrorException;
use Models\Lottery;
use Models\WhitelabelLottery;
use Models\WhitelabelLotteryPurchaseLimit;
use Repositories\WhitelabelLotteryPurchaseLimitRepository;
use Repositories\WhitelabelLotteryRepository;
use Services\Logs\FileLoggerService;

class LotteryPurchaseLimitService
{
    private WhitelabelLotteryRepository $whitelabelLotteryRepository;
    private WhitelabelLotteryPurchaseLimitRepository $whitelabelLotteryPurchaseLimitRepository;
    private int $userId;
    private int $whitelabelId;
    private string $errorMessage = '';
    /**
     * Collected during isAllowedToPurchase checks, to not iterate twice
     * [WhitelabelLotteryId => linesCount]
     */
    private array $allowedPurchasesDetails = [];
    /** Set to true, only after evaluating isAllowedToPurchase */
    private bool $isAllowedToPurchase = false;

    public function __construct(WhitelabelLotteryRepository $whitelabelLotteryRepository, WhitelabelLotteryPurchaseLimitRepository $whitelabelLotteryPurchaseLimitRepository)
    {
        $this->whitelabelLotteryRepository = $whitelabelLotteryRepository;
        $this->whitelabelLotteryPurchaseLimitRepository = $whitelabelLotteryPurchaseLimitRepository;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function setWhitelabelId(int $whitelabelId): void
    {
        $this->whitelabelId = $whitelabelId;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * Use to check if number of ticket's lines are within purchase limit for ONLY one lottery.
     * It will short-circuit and return early, when amount of ticket lines is above lottery limit - no point to check user limit table.
     */
    private function isAllowedToPurchaseUsingBonusBalance(WhitelabelLottery $whitelabelLottery, int $numberOfTickets): bool
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $isBonusBalancePurchaseDisabled = !$whitelabelLottery->is_bonus_balance_in_use;
        if ($isBonusBalancePurchaseDisabled) {
            $exceptionMessage = "User should not get this far (possible hack attempt!). User ID: {$this->userId}, Whitelabel ID: {$this->whitelabelId}. Bonus balance purchases are disabled for this whitelabel lottery {$whitelabelLottery->id}";
            $fileLoggerService->error(
                $exceptionMessage
            );
            throw new ErrorException($exceptionMessage);
        }

        $isNoPurchaseLimit = $whitelabelLottery->bonusBalancePurchaseLimitPerUser === WhitelabelLottery::BONUS_BALANCE_PURCHASE_LIMIT_PER_USER_UNLIMITED;
        if ($isNoPurchaseLimit) {
            return true;
        }

        $lotteryPurchaseLimit = $whitelabelLottery->bonusBalancePurchaseLimitPerUser;
        if ($numberOfTickets <= $lotteryPurchaseLimit) {
            $whitelabelLotteryPurchaseLimit = $this->whitelabelLotteryPurchaseLimitRepository->findOneByUserIdAndWhitelabelLotteryId($this->userId, $whitelabelLottery->id);
            if (empty($whitelabelLotteryPurchaseLimit)) {
                return true;
            }

            $purchasedAlready = $whitelabelLotteryPurchaseLimit->counter;
            $isAllowedToPurchase = ($purchasedAlready + $numberOfTickets) <= $lotteryPurchaseLimit;
            if ($isAllowedToPurchase) {
                return true;
            }
            $numberUserCanPurchase = $lotteryPurchaseLimit - $purchasedAlready;
            $errorMessageUserRemaining = ' ' . sprintf(_('You have %1$d remaining to buy.'), $numberUserCanPurchase);
        }

        /** @var Lottery $lottery */
        $lottery = $whitelabelLottery->lottery;
        $errorMessageUserRemaining ??= '';
        $this->errorMessage = sprintf(_('Purchase of tickets for %1$s lottery is exceeding allowed bonus balance purchase of %2$s.'), $lottery->name, $whitelabelLottery->bonusBalancePurchaseLimitPerUser);
        $this->errorMessage .= $errorMessageUserRemaining;
        return false;
    }

    /**
     * Use to check that user can purchase entire basket, and number of ticket's lines are within purchase limit.
     * It will short-circuit and return early, when one lottery violates limit.
     * @throws ErrorException when service not configured with required data
     */
    public function isAllowedToPurchaseBasketUsingBonusBalance(array $basketItems): bool
    {
        if (empty($this->whitelabelId)) {
            throw new ErrorException('Whitelabel has to configured before using this method');
        }

        if (empty($basketItems)) {
            throw new ErrorException('Basket is empty, nothing to purchase');
        }

        if (empty($this->userId)) {
            throw new ErrorException('User has to be configured before using this method');
        }

        $summedBasketItems = self::sumBasketItemsForSameLotteries($basketItems);

        foreach ($summedBasketItems as $lotteryId => $linesCount) {
            $whitelabelLottery = $this->whitelabelLotteryRepository->withRelation(WhitelabelLottery::LOTTERY_RELATION)->getOneByLotteryIdForWhitelabel($lotteryId, $this->whitelabelId);
            $isNotAllowed = !$this->isAllowedToPurchaseUsingBonusBalance($whitelabelLottery, $linesCount);
            if ($isNotAllowed) {
                return false;
            }

            $this->allowedPurchasesDetails[$whitelabelLottery->id] = $linesCount;
        }

        $this->isAllowedToPurchase = true;
        return true;
    }

    /**
     * Basket can have multiple items
     * For example:
     * - lottery => 3, lines => 2[], lottery => 1, lines => 4[]
     * This function handles following case:
     * - lottery => 3, lines => 2[], lottery => 3, lines => 1[] && purchase limit is 2
     * Checking above lines separately can result allowing to purchase, but sum of these basket items is 3 - so above allowed limit
     * @return array lotteryId => linesCount
     */
    private static function sumBasketItemsForSameLotteries(array $basketItems): array
    {
        $summedBasketItems = [];
        foreach ($basketItems as $item) {
            if (array_key_exists($item['lottery'], $summedBasketItems)) {
                $summedBasketItems[$item['lottery']] += count($item['lines']);
                continue;
            }
            $summedBasketItems[$item['lottery']] = count($item['lines']);
        }

        return $summedBasketItems;
    }

    /**
     * Executes only when isAllowedToPurchaseBasketUsingBonusBalance returns true,
     * then all lotteries in the basket update purchase counter for the given user.
     */
    public function addOrUpdatePurchaseLimitEntriesForAllowedBasket(): bool
    {
        if ($this->isAllowedToPurchase) {
            $whitelabelLotteryPurchaseLimits = [];
            foreach ($this->allowedPurchasesDetails as $key => $value) {
                $whitelabelLotteryPurchaseLimit = new WhitelabelLotteryPurchaseLimit();
                $whitelabelLotteryPurchaseLimit->counter = $value;
                $whitelabelLotteryPurchaseLimit->whitelabelLotteryId = $key;
                $whitelabelLotteryPurchaseLimit->whitelabelUserId = $this->userId;
                $whitelabelLotteryPurchaseLimits[] = $whitelabelLotteryPurchaseLimit;
            }

            if (!empty($whitelabelLotteryPurchaseLimits)) {
                return $this->whitelabelLotteryPurchaseLimitRepository->insertOrUpdateEntries(
                    $whitelabelLotteryPurchaseLimits
                );
            }
        }

        return false;
    }
}
