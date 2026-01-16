<?php

namespace Services\MiniGame;

use Carbon\Carbon;
use Exception;
use Fuel\Core\DB;
use Helpers_Currency;
use Models\MiniGameTransaction;
use Models\Whitelabel;
use Repositories\MiniGameTransactionRepository;
use Repositories\Orm\WhitelabelUserRepository;
use Services\Logs\FileLoggerService;
use Services\MiniGame\ValueObject\MiniGameBalanceTransactionObject;
use Services\MiniGame\ValueObject\MiniGameTransactionObject;
use Throwable;

class MiniGameTransactionService
{
    private MiniGameTransactionRepository $transactionRepository;
    private WhitelabelUserRepository $whitelabelUserRepository;
    private FileLoggerService $loggerService;

    private Whitelabel $whitelabel;

    public function __construct(
        MiniGameTransactionRepository $transactionRepository,
        WhitelabelUserRepository $whitelabelUserRepository,
        FileLoggerService $loggerService,
        Whitelabel $whitelabel,
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->whitelabelUserRepository = $whitelabelUserRepository;
        $this->loggerService = $loggerService;
        $this->whitelabel = $whitelabel;

        $this->loggerService->setSource('api');
    }

    public function saveGameTransaction(MiniGameTransactionObject $transaction): void
    {
        $convertedAmounts = $this->convertCurrencyForTransaction($transaction);
        $transactionType = $transaction->isWin() ? MiniGameTransaction::TRANSACTION_TYPE_WIN : MiniGameTransaction::TRANSACTION_TYPE_LOSS;

        try {
            $this->transactionRepository->saveTransaction(
                new MiniGameTransaction([
                    'mini_game_id' => $transaction->getMiniGame()->id,
                    'whitelabel_user_id' => $transaction->getUser()->id,
                    'currency_id' => $transaction->getUser()->currency_id,
                    'token' => DB::expr('UUID_SHORT()'),
                    'amount' => $transaction->getBetAmountInEur(),
                    'amount_usd' => $convertedAmounts['amountInUsd'],
                    'amount_manager' => $convertedAmounts['amountInManagerCurrency'],
                    'prize' => $transaction->getPrizeAmountInEur(),
                    'prize_usd' => $convertedAmounts['prizeInUsd'],
                    'prize_manager' => $convertedAmounts['prizeInManagerCurrency'],
                    'type' => $transactionType,
                    'user_selected_number' => $transaction->getSelectedNumber(),
                    'system_drawn_number' => $transaction->getSystemDrawnNumber(),
                    'mini_game_user_promo_code_id' => $transaction->isFreeSpin() ? $transaction->getPromoCodeUserId() : null,
                    'is_bonus_balance_paid' => $transaction->isBonusBalancePaid(),
                    'created_at' => Carbon::now(),
                ])
            );
        } catch (Throwable $throwable) {
            $this->loggerService->error('[Transaction] Problem saving transaction to the database. Message: ' . $throwable->getMessage());
        }
    }

    public function updateUserBalance(MiniGameBalanceTransactionObject $balanceTransaction): bool
    {
        $userId       = $balanceTransaction->getUserId();
        $isFreeSpin   = $balanceTransaction->isFreeSpin();
        $isWin        = $balanceTransaction->isWin();
        $betAmount    = $balanceTransaction->getBetAmountInUserCurrency();
        $prizeAmount  = $balanceTransaction->getPrizeAmountInUserCurrency();

        $currentRealBalance  = $balanceTransaction->getUserBalance();
        $currentBonusBalance = $balanceTransaction->getUserBonusBalance();

        if ($isFreeSpin) {
            if ($isWin && $prizeAmount > 0) {
                $this->whitelabelUserRepository->updateUserBonusBalance($userId, $prizeAmount);
            }

            return true;
        }

        if ($currentRealBalance >= $betAmount) {
            try {
                $this->whitelabelUserRepository->updateUserBalance($userId, -$betAmount);
            } catch (Exception $e) {
                $this->loggerService->error('[Update User Balance] Error: ' . $e->getMessage());
            }

            if ($isWin && $prizeAmount > 0) {
                $this->whitelabelUserRepository->updateUserBalance($userId, $prizeAmount);
            }

            return false;
        } else {
            if ($currentBonusBalance >= $betAmount) {
                try {
                    $this->whitelabelUserRepository->updateUserBonusBalance($userId, -$betAmount);
                } catch (Exception $e) {
                    $this->loggerService->error('[Update User Bonus Balance] Error: ' . $e->getMessage());
                }

                if ($isWin && $prizeAmount > 0) {
                    $this->whitelabelUserRepository->updateUserBalance($userId, $prizeAmount);
                }
            }

            return true;
        }
    }

    private function convertCurrencyForTransaction(MiniGameTransactionObject $transaction): array
    {
        $amountInUsd = (float) Helpers_Currency::convert_to_USD($transaction->getBetAmountInEur(), AbstractMiniGameService::GAME_CURRENCY);
        $amountInManagerCurrency = (float) Helpers_Currency::convert_to_any(
            $transaction->getBetAmountInEur(),
            AbstractMiniGameService::GAME_CURRENCY,
            $this->whitelabel->currency->code
        );

        $prizeInUsd = (float) Helpers_Currency::convert_to_USD($transaction->getPrizeAmountInEur(), AbstractMiniGameService::GAME_CURRENCY);
        $prizeInManagerCurrency = (float) Helpers_Currency::convert_to_any(
            $transaction->getPrizeAmountInEur(),
            AbstractMiniGameService::GAME_CURRENCY,
            $this->whitelabel->currency->code
        );

        return [
            'amountInUsd' => $amountInUsd,
            'amountInManagerCurrency' => $amountInManagerCurrency,
            'prizeInUsd' => $prizeInUsd,
            'prizeInManagerCurrency' => $prizeInManagerCurrency,
        ];
    }
}
