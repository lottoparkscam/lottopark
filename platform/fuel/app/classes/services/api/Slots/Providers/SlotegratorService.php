<?php

namespace Services\Api\Slots\Providers;

use Controller_Api_Slots_Slotegrator;
use Exception;
use Exceptions\Slots\InsufficientWhitelabelLimitException;
use Helpers_Currency;
use Models\WhitelabelUser;
use Models\SlotGame;
use Models\SlotLog;
use Models\SlotOpenGame;
use Models\SlotTransaction;
use Models\WhitelabelSlotProvider;
use Modules\Account\Balance\CasinoBalance;
use Repositories\SlotTransactionRepository;
use Services\Api\Slots\LimitService;
use Services\Api\Slots\LoggerService;
use Services\Api\Slots\TransactionService;
use Services\Logs\FileLoggerService;
use Throwable;
use Wrappers\Db;

class SlotegratorService
{
    private WhitelabelUser $whitelabelUser;
    private WhitelabelSlotProvider $whitelabelSlotProvider;
    private LoggerService $loggerService;
    private array $requestData;
    private CasinoBalance $casinoBalance;
    private TransactionService $transactionService;
    private ?SlotGame $slotGame;
    private SlotOpenGame $slotOpenGame;
    private LimitService $limitService;
    private SlotTransactionRepository $slotTransactionRepository;
    private Db $db;
    private FileLoggerService $fileLoggerService;

    /** Slotegrator requires amount in 0.0000 format */
    private const ROUND_PRECISION = 4;

    public function __construct(
        CasinoBalance $casinoBalance,
        TransactionService $transactionService,
        LimitService $limitService,
        SlotTransactionRepository $slotTransactionRepository,
        Db $db,
        FileLoggerService $fileLoggerService
    ) {
        $this->casinoBalance = $casinoBalance;
        $this->transactionService = $transactionService;
        $this->limitService = $limitService;
        $this->slotTransactionRepository = $slotTransactionRepository;
        $this->db = $db;
        $this->fileLoggerService = $fileLoggerService;
    }

    public function configure(
        WhitelabelUser $whitelabelUser,
        WhitelabelSlotProvider $whitelabelSlotProvider,
        LoggerService $loggerService,
        ?SlotGame $slotGame,
        SlotOpenGame $slotOpenGame,
        array $requestData
    ): void {
        $this->whitelabelUser = $whitelabelUser;
        $this->whitelabelSlotProvider = $whitelabelSlotProvider;
        $this->loggerService = $loggerService;
        $this->slotGame = $slotGame;
        $this->slotOpenGame = $slotOpenGame;
        $this->requestData = $requestData;

        $isNotBalanceAction = $this->requestData['action'] !== SlotLog::ACTION_BALANCE;
        if ($isNotBalanceAction) {
            $this->transactionService->configure(
                $this->slotGame,
                $this->slotOpenGame,
                $this->whitelabelUser,
                $this->whitelabelSlotProvider
            );
        }
    }

    public function balance(): array
    {
        $userBalance = $this->getUserBalanceInRequestCurrency();
        $response = ['balance' => $userBalance];
        $this->loggerService->log($response);
        return $response;
    }

    public function bet(): array
    {
        $isUserDisabled = $this->whitelabelUser->isDeleted;
        if ($isUserDisabled) {
            $response = $this->createErrorResponse('This user is disabled');
            $this->loggerService->log($response, true);
            return $response;
        }

        $isWhitelabelSlotProviderDisabled = !$this->whitelabelSlotProvider->isEnabled;
        if ($isWhitelabelSlotProviderDisabled) {
            $response = $this->createErrorResponse('Casino for this whitelabel is disabled');
            $this->loggerService->log($response, true);
            return $response;
        }

        ['amount' => $amount, 'currency' => $currencyCode, 'transaction_id' => $transactionId] = $this->requestData;

        $userBalance = $this->getUserBalanceInRequestCurrency();

        $transaction = $this->slotTransactionRepository->findOneByProviderTransactionId(
            $transactionId,
            $this->whitelabelSlotProvider->id
        );
        $transactionAlreadyExists = !empty($transaction);
        if ($transactionAlreadyExists) {
            $response = $this->createBalanceResponse($userBalance, $transaction->token);
            $this->loggerService->log($response);
            return $response;
        }

        if ($this->isFreespinType()) {
            $amount = 0.00;
        }

        try {
            $this->db->start_transaction();

            // Remember to maintain the same query order in database transaction in each type of casino request
            // Different query order can cause deadlocks in database

            $transaction = $this->addTransaction();

            if ($this->isNotFreespinType()) {
                $this->changeUserBalance(-$amount, $currencyCode);
            }

            $amountInUsd = $transaction->amountUsd;
            $insufficientLimit = !$this->limitService->isMonthlyLimitSufficientPerWhitelabel(
                $amountInUsd,
                $this->whitelabelSlotProvider
            );

            if ($insufficientLimit) {
                throw new InsufficientWhitelabelLimitException();
            }

            $this->db->commit_transaction();
        } catch (InsufficientWhitelabelLimitException $exception) {
            $response = $this->createErrorResponse('Insufficient whitelabel limit to do bet transaction');
            $this->loggerService->log($response, true);
            return $response;
        } catch (Throwable $throwable) {
            $this->db->rollback_transaction();

            $insufficientUserBalance = $throwable->getMessage() === 'No sufficient balance to continue.';
            $code = $insufficientUserBalance ?
                Controller_Api_Slots_Slotegrator::INSUFFICIENT_FUNDS_ERROR_CODE :
                Controller_Api_Slots_Slotegrator::INTERNAL_ERROR_CODE;

            $response = $this->createErrorResponse('Error while debit user', $code);

            $otherLogThanInsufficientUserBalance = !$insufficientUserBalance;
            if ($otherLogThanInsufficientUserBalance) {
                $this->fileLoggerService->error(
                    'Error while debit user. Message: ' . $throwable->getMessage()
                );
            }

            $this->loggerService->log($response, true);
            return $response;
        }

        $userBalance = $this->getUserBalanceInRequestCurrency();
        $response = $this->createBalanceResponse($userBalance, $transaction->token);
        $this->loggerService->log($response);
        return $response;
    }

    public function win(): array
    {
        ['amount' => $amount, 'currency' => $currencyCode, 'transaction_id' => $transactionId] = $this->requestData;

        $transaction = $this->slotTransactionRepository->findOneByProviderTransactionId(
            $transactionId,
            $this->whitelabelSlotProvider->id
        );
        $transactionAlreadyExists = !empty($transaction);
        if ($transactionAlreadyExists) {
            $response = $this->createBalanceResponse($this->whitelabelUser->casinoBalance, $transaction->token);
            $this->loggerService->log($response, true);
            return $response;
        }

        if ($this->isFreespinType()) {
            $amount = 0.00;
        }

        try {
            $this->db->start_transaction();

            // Remember to maintain the same query order in database transaction in each type of casino request
            // Different query order can cause deadlocks in database

            $transaction = $this->addTransaction();

            if ($this->isNotFreespinType()) {
                $this->changeUserBalance($amount, $currencyCode);
            }

            $this->db->commit_transaction();
        } catch (Throwable $throwable) {
            $this->db->rollback_transaction();

            $response = $this->createErrorResponse('Error while charge user');

            $this->fileLoggerService->error(
                'Error while charge user. Message: ' . $throwable->getMessage()
            );

            $this->loggerService->log($response, true);
            return $response;
        }

        $userBalance = $this->getUserBalanceInRequestCurrency();
        $response = $this->createBalanceResponse($userBalance, $transaction->token);
        $this->loggerService->log($response);
        return $response;
    }

    public function refund(): array
    {
        [
            'amount' => $amount,
            'currency' => $currencyCode,
            'transaction_id' => $transactionId,
            'bet_transaction_id' => $betTransactionId
        ] = $this->requestData;

        $userBalance = $this->getUserBalanceInRequestCurrency();

        $transaction = $this->slotTransactionRepository->findOneByProviderTransactionId(
            $transactionId,
            $this->whitelabelSlotProvider->id
        );
        $transactionAlreadyExists = !empty($transaction);
        if ($transactionAlreadyExists) {
            $response = $this->createBalanceResponse($userBalance, $transaction->token);
            $this->loggerService->log($response);
            return $response;
        }

        try {
            $this->db->start_transaction();

            // Remember to maintain the same query order in database transaction in each type of casino request
            // Different query order can cause deadlocks in database

            $transactionToRefund = $this->slotTransactionRepository->findOneByProviderTransactionId(
                $betTransactionId,
                $this->whitelabelSlotProvider->id
            );
            $transactionToRefundNotExists = empty($transactionToRefund);
            $transactionToRefundIsWinAction = !empty($transactionToRefund) &&
                $transactionToRefund->action === SlotTransaction::ACTION_WIN;
            if ($transactionToRefundNotExists || $transactionToRefundIsWinAction) {
                // Add only refundTransaction, even if transactionToRefund not exists
                $transaction = $this->addTransaction(true);
                $response = $this->createBalanceResponse($userBalance, $transaction->token);
                $this->loggerService->log($response);
                $this->db->commit_transaction();
                return $response;
            }

            $transactionToRefundIsNotBetAction = $transactionToRefund->action !== SlotTransaction::ACTION_BET;
            if ($transactionToRefundIsNotBetAction) {
                $response = $this->createErrorResponse("Transaction given in bet_transaction_id with id $betTransactionId is not bet action");
                $this->loggerService->log($response, true);
                $this->db->commit_transaction();
                return $response;
            }

            $transactionToRefundIsAlreadyRefunded = $transactionToRefund->isCanceled;
            if ($transactionToRefundIsAlreadyRefunded) {
                $transaction = $this->addTransaction(true);
                $response = $this->createBalanceResponse($userBalance, $transaction->token);
                $this->loggerService->log($response);
                $this->db->commit_transaction();
                return $response;
            }

            $transaction = $this->addTransaction();
            $this->slotTransactionRepository->setTransactionAsCanceled($transactionToRefund);

            $this->changeUserBalance($amount, $currencyCode);

            if (round($amount, 2) != round($transactionToRefund->amount, 2)) {
                $this->fileLoggerService->error(
                    "Request to refund transaction $betTransactionId 
                    has amount $amount in $currencyCode 
                    but bet transaction has amount {$transactionToRefund->amount} 
                    in {$transactionToRefund->currency->code}"
                );
            }

            $this->db->commit_transaction();
        } catch (Throwable $throwable) {
            $this->db->rollback_transaction();

            $response = $this->createErrorResponse('Error while charge user');

            $this->fileLoggerService->error(
                'Error while charge user. Message: ' . $throwable->getMessage()
            );

            $this->loggerService->log($response, true);
            return $response;
        }

        $userBalance = $this->getUserBalanceInRequestCurrency();
        $response = $this->createBalanceResponse($userBalance, $transaction->token);
        $this->loggerService->log($response);
        return $response;
    }

    public function rollback(): array
    {
        [
            'currency' => $currencyCode,
            'transaction_id' => $transactionId,
            'rollback_transactions' => $transactionsToRollback
        ] = $this->requestData;

        $userBalance = $this->getUserBalanceInRequestCurrency();

        $transaction = $this->slotTransactionRepository->findOneByProviderTransactionId(
            $transactionId,
            $this->whitelabelSlotProvider->id
        );
        $transactionAlreadyExists = !empty($transaction);
        $transactionsToRollbackIds = array_column($transactionsToRollback, 'transaction_id');
        if ($transactionAlreadyExists) {
            $response = $this->createRollbackResponse($userBalance, $transaction->token, $transactionsToRollbackIds);
            $this->loggerService->log($response);
            return $response;
        }

        try {
            $this->db->start_transaction();

            // Remember to maintain the same query order in database transaction in each type of casino request
            // Different query order can cause deadlocks in database

            $foundTransactionsToRollback = $this->slotTransactionRepository->findAllByProviderTransactionIds(
                $transactionsToRollbackIds,
                $this->whitelabelSlotProvider->id
            );

            $finalUserBalanceChange = $this->getFinalUserBalanceChangeFromTransactions(
                $foundTransactionsToRollback,
                $currencyCode,
            );

            $transaction = $this->addTransaction();

            if (!empty($foundTransactionsToRollback)) {
                $this->slotTransactionRepository->setTransactionsAsCanceled($foundTransactionsToRollback);
            }

            $this->changeUserBalance($finalUserBalanceChange, $currencyCode);

            $this->db->commit_transaction();
        } catch (Throwable $throwable) {
            $this->db->rollback_transaction();

            $response = $this->createErrorResponse('Error while rollback transactions');

            $this->fileLoggerService->error(
                'Error while rollback transactions. Message: ' . $throwable->getMessage()
            );

            $this->loggerService->log($response, true);
            return $response;
        }

        $userBalance = $this->getUserBalanceInRequestCurrency();
        $response = $this->createRollbackResponse($userBalance, $transaction->token, $transactionsToRollbackIds);
        $this->loggerService->log($response);
        return $response;
    }

    public function createErrorResponse(
        string $message,
        string $code = Controller_Api_Slots_Slotegrator::INTERNAL_ERROR_CODE
    ): array {
        return [
            'error_code' => $code,
            'error_description' => $message
        ];
    }

    public function createBalanceResponse(float $balance, string $transactionId): array
    {
        return [
            'balance' => round($balance, self::ROUND_PRECISION),
            'transaction_id' => $transactionId
        ];
    }

    public function createRollbackResponse(float $balance, string $transactionId, array $rollbackTransactions): array
    {
        return [
            'balance' => round($balance, self::ROUND_PRECISION),
            'transaction_id' => $transactionId,
            'rollback_transactions' => $rollbackTransactions
        ];
    }

    private function isFreespinType(): bool
    {
        $typeNotExists = !key_exists('type', $this->requestData);
        if ($typeNotExists) {
            return false;
        }

        $type = $this->requestData['type'];
        return $type === SlotTransaction::TYPE_FREESPIN;
    }

    private function isNotFreespinType(): bool
    {
        return !$this->isFreespinType();
    }

    private function getUserBalanceInRequestCurrency(): float
    {
        $currencyCode = $this->requestData['currency'];
        $this->whitelabelUser->reload();

        return (float)Helpers_Currency::convert_to_any(
            $this->whitelabelUser->casinoBalance,
            $this->whitelabelUser->currency->code,
            $currencyCode,
            true
        );
    }

    /** @throws Exception */
    private function addTransaction(bool $forceZeroAmount = false): SlotTransaction
    {
        $action = $this->requestData['action'];
        $amount = $this->requestData['amount'] ?? 0.00;
        $currencyCode = $this->requestData['currency'];
        $transactionId = $this->requestData['transaction_id'];

        $typeNotExists = !key_exists('type', $this->requestData);
        if ($typeNotExists) {
            $type = null;
        } else {
            $type = $this->requestData['type'];
        }

        if ($this->isFreespinType() || $forceZeroAmount) {
            $amount = 0.00;
        }

        return $this->transactionService->addTransaction(
            (float)$amount,
            $currencyCode,
            $action,
            $type,
            $transactionId,
            $this->requestData
        );
    }

    /**
     * @param SlotTransaction[] $slotTransactions
     * @param string $currencyCode
     * @return float
     */
    private function getFinalUserBalanceChangeFromTransactions(array $slotTransactions, string $currencyCode): float
    {
        $finalUserBalanceChange = 0.00;
        foreach ($slotTransactions as $transactionToRollback) {
            $transactionToRollbackAction = $transactionToRollback->action;
            $transactionToRollbackAmount = (float)Helpers_Currency::convert_to_any(
                $transactionToRollback->amount,
                $transactionToRollback->currency->code,
                $currencyCode,
                false,
                self::ROUND_PRECISION
            );

            switch ($transactionToRollbackAction) {
                case SlotTransaction::ACTION_WIN:
                    $finalUserBalanceChange -= $transactionToRollbackAmount;
                    break;
                case SlotTransaction::ACTION_BET:
                    $finalUserBalanceChange += $transactionToRollbackAmount;
                    break;
            }
        }

        return $finalUserBalanceChange;
    }

    /**
     * @param float $finalUserBalanceChange
     * @param string $currencyCode
     * @throws Throwable
     */
    private function changeUserBalance(float $finalUserBalanceChange, string $currencyCode): void
    {
        if ($finalUserBalanceChange < 0) {
            $this->casinoBalance->debit(
                $this->whitelabelUser,
                abs($finalUserBalanceChange),
                $currencyCode
            );
            $this->casinoBalance->dispatch();
        } elseif ($finalUserBalanceChange > 0) {
            $this->casinoBalance->increase(
                $this->whitelabelUser,
                $finalUserBalanceChange,
                $currencyCode
            );
            $this->casinoBalance->dispatch();
        }
    }
}
