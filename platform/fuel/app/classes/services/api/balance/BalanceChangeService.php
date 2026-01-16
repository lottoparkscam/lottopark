<?php


namespace Services\Api\Balance;

use Exception;
use Helpers_Currency;
use Models\WhitelabelUser;
use Model_Whitelabel_User_Balance_Change_Limit_Log;
use Modules\Account\Balance\RegularBalance;
use Repositories\WhitelabelRepository;
use Services\Api\Reply;
use Services\Logs\FileLoggerService;
use Throwable;
use Wrappers\Db;

class BalanceChangeService
{
    private ChargeService $chargeService;

    private Db $db;

    private RegularBalance $regularBalance;

    private WhitelabelRepository $whitelabelRepository;
    private FileLoggerService $fileLoggerService;

    private array $errorMessage;

    public function __construct(
        ChargeService  $chargeService,
        Db $db,
        RegularBalance $regularBalance,
        WhitelabelRepository $whitelabelRepository,
        FileLoggerService $fileLoggerService
    ) {
        $this->chargeService = $chargeService;
        $this->db = $db;
        $this->regularBalance = $regularBalance;
        $this->whitelabelRepository = $whitelabelRepository;
        $this->fileLoggerService = $fileLoggerService;
    }

    public function getError(): array
    {
        return $this->errorMessage;
    }

    /**
     * @param float $amount
     * @param string $currencyCode
     * @param WhitelabelUser $whitelabelUser
     * @return bool
     * @throws Exception
     */
    public function changeUserBalance(
        float $amount,
        string $currencyCode,
        WhitelabelUser $whitelabelUser
    ): bool {
        $this->chargeService->setWhitelabel($whitelabelUser->whitelabel);
        $debit = $amount < 0;
        $amount = abs($amount);
        $this->db->start_transaction();

        try {
            if ($debit) {
                $changeBalanceFailed = !$this->debit($amount, $currencyCode, $whitelabelUser);
            } else {
                $changeBalanceFailed = !$this->charge($amount, $currencyCode, $whitelabelUser);
            }

            if ($changeBalanceFailed) {
                return false;
            }

            $this->db->commit_transaction();

            // little prevention of throw fatal error after reach max_execution_lime
            set_time_limit(5);
        } catch (Throwable $throwable) {
            $this->db->rollback_transaction();
            $this->fileLoggerService->error(
                $throwable->getMessage()
            );
            $this->errorMessage = [['Error occurred. Please contact us if problem persists.'], Reply::BAD_REQUEST];
            return false;
        }

        return true;
    }

    /**
     * @param float $amount
     * @param string $currencyCode
     * @param WhitelabelUser $whitelabelUser
     * @return bool
     * @throws Exception
     */
    private function debit(
        float $amount,
        string $currencyCode,
        WhitelabelUser $whitelabelUser
    ): bool {
        $amountIsBiggerThanUserBalance = $amount > $whitelabelUser->balance;

        if ($amountIsBiggerThanUserBalance) {
            $this->errorMessage = [['amount' => 'Wrong balance amount. Amount is bigger than user balance.'], Reply::BAD_REQUEST];
            return false;
        }

        $this->regularBalance->debit(
            $whitelabelUser->id,
            $amount,
            $currencyCode
        );
        $this->regularBalance->dispatch();

        $isBalanceChangeGlobalLimitEnabledInApi = $whitelabelUser->whitelabel->isBalanceChangeGlobalLimitEnabledInApi;
        $isChangeGlobalLimitEnabled = $whitelabelUser->whitelabel->isReducingBalanceIncreasesLimits;
        if ($isBalanceChangeGlobalLimitEnabledInApi && $isChangeGlobalLimitEnabled) {
            $amountInWhitelabelCurrency = (float)Helpers_Currency::convert_to_any(
                $amount,
                $currencyCode,
                $whitelabelUser->whitelabel->currency->code
            );

            // update global limit
            $this->whitelabelRepository->updateFloatField(
                $whitelabelUser->whitelabel->id,
                'user_balance_change_limit',
                $amountInWhitelabelCurrency
            );

            // add log that whitelabel's global limit has changed
            Model_Whitelabel_User_Balance_Change_Limit_Log::add_log(
                $whitelabelUser->whitelabel->id,
                $amountInWhitelabelCurrency
            );
        }

        return true;
    }

    /**
     * @param float $amount
     * @param string $currencyCode
     * @param WhitelabelUser $whitelabelUser
     * @return bool
     * @throws Exception
     */
    private function charge(
        float $amount,
        string $currencyCode,
        WhitelabelUser $whitelabelUser
    ): bool {
        $isBalanceChangeLimitReached = $this->chargeService->isBalanceAmountLimitExceeded($amount, $whitelabelUser);

        if ($isBalanceChangeLimitReached) {
            $this->errorMessage = [['amount' => 'Wrong balance amount. Limit has been reached.'], Reply::BAD_REQUEST];
            return false;
        }

        $isBalanceChangeGlobalLimitEnabledInApi = $whitelabelUser->whitelabel->isBalanceChangeGlobalLimitEnabledInApi;
        $userBalanceChangeLimit = $whitelabelUser->whitelabel->userBalanceChangeLimit;
        $maxBalanceAmountPerWhitelabel = $userBalanceChangeLimit < 0 ? 0 : $userBalanceChangeLimit;

        if ($isBalanceChangeGlobalLimitEnabledInApi && $amount > $maxBalanceAmountPerWhitelabel) {
            $this->errorMessage = [['amount' => 'Insufficient balance on account'], Reply::BAD_REQUEST];
            return false;
        }

        $this->regularBalance->increase(
            $whitelabelUser->id,
            $amount,
            $currencyCode
        );
        $this->regularBalance->dispatch();

        if ($isBalanceChangeGlobalLimitEnabledInApi) {$amountInWhitelabelCurrency = (float)Helpers_Currency::convert_to_any(
            $amount,
            $currencyCode,
            $whitelabelUser->whitelabel->currency->code
        ) * -1;

            // update global limit
            $this->whitelabelRepository->updateFloatField(
                $whitelabelUser->whitelabel->id,
                'user_balance_change_limit',
                $amountInWhitelabelCurrency
            );

            // add log that whitelabel's global limit has changed
            Model_Whitelabel_User_Balance_Change_Limit_Log::add_log(
                $whitelabelUser->whitelabel->id,
                $amountInWhitelabelCurrency
            );
        }

        return true;
    }
}
