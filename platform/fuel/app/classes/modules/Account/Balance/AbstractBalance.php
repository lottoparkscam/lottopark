<?php

namespace Modules\Account\Balance;

use Exception;
use Wrappers\Db;
use Helpers_General;
use RuntimeException;
use Orm\RecordNotFound;
use Models\WhitelabelUser;
use Services_Currency_Calc;
use Webmozart\Assert\Assert;
use Models\WhitelabelRaffleTicket;
use Services\Shared\AbstractDispatchAble;
use Repositories\Orm\WhitelabelUserRepository;
use Repositories\Orm\WhitelabelUserBalanceLogRepository;

/**
 * Common interface for Bonus & Regular balance strategies.
 */
abstract class AbstractBalance extends AbstractDispatchAble implements BalanceContract
{
    protected WhitelabelUserBalanceLogRepository $userBalanceLogRepository;
    protected WhitelabelUser $userDao;
    protected WhitelabelUserRepository $userRepository;
    protected Services_Currency_Calc $currencyCalc;
    private Db $db;

    /**
     * Cumulated sums (+/-) of all transactions.
     * Index = user_id, value = [source = sums ] in user currency.
     */
    private array $sums = [];

    public function __construct(
        Db $db,
        WhitelabelUser $user,
        WhitelabelUserRepository $userRepository,
        WhitelabelUserBalanceLogRepository $userBalanceLogRepository,
        Services_Currency_Calc $currencyCalc
    ) {
        $this->userDao = $user;
        $this->userRepository = $userRepository;
        $this->currencyCalc = $currencyCalc;
        $this->db = $db;
        $this->userBalanceLogRepository = $userBalanceLogRepository;
    }

    public function isWelcomeBonus(): bool
    {
        return $this instanceof WelcomeBonusBalance;
    }

    /**
     * @param int $userId
     * @param float $amount
     * @param string $currencyCode
     *
     * @throws RuntimeException
     * @throws RecordNotFound
     * @throws Exception
     */
    public function debit(WhitelabelUser|int $userId, float $amount, string $currencyCode): void
    {
        $user = $userId instanceof WhitelabelUser ? $userId : $this->getAndVerifyUser($userId);
        if (!is_int($user->id)) {
            throw new Exception("User does not have correct id! Cannot debit balance!");
        }

        $this->verifyAmount($amount);
        $amountInUserCurrency = $this->currencyCalc->convert_to_any($amount, $currencyCode, $user->currency->code);

        $this->enqueue($user, $amountInUserCurrency * -1, $amount * -1, $currencyCode);

        $this->verifyHasSufficientBalance($user, $amountInUserCurrency);
    }

    /**
     * @param WhitelabelRaffleTicket $ticket
     *
     * @throws RuntimeException
     */
    public function debitByTicket(WhitelabelRaffleTicket $ticket): void
    {
        $user = $this->getAndVerifyUser($ticket->whitelabel_user_id);

        if ($this->isWelcomeBonus()) {
            $this->enqueue($user, null, $this->getTicketIncomeManager($ticket), null);

            return;
        }

        $amountInUserCurrency = $this->getTicketAmountToPayInUserCurrency($ticket);
        $this->verifyAmount($amountInUserCurrency);

        $this->enqueue($user, $amountInUserCurrency * -1, null, null);

        $this->verifyHasSufficientBalance($user, $amountInUserCurrency);
    }

    public function increase(WhitelabelUser|int $userId, float $amount, string $currencyCode): void
    {
        $user = $userId instanceof WhitelabelUser ? $userId : $this->getAndVerifyUser($userId);
        if (!is_int($user->id)) {
            throw new Exception("User does not have correct id! Cannot increase balance!");
        }

        $this->verifyAmount($amount);
        $amountInUserCurrency = $this->currencyCalc->convert_to_any($amount, $currencyCode, $user->currency->code);
        $this->enqueue($user, $amountInUserCurrency, $amount, $currencyCode);
    }

    /**
     * @param int $userId
     * @return WhitelabelUser
     *
     * @throws RecordNotFound
     */
    protected function getAndVerifyUser(int $userId): WhitelabelUser
    {
        return $this->userDao->get_user_by_id($userId);
    }

    public function hasSufficientBalanceToProcess(WhitelabelUser $user, float $amountInUserCurrency): bool
    {
        if ($this->isWelcomeBonus()) {
            return true;
        }

        $hasEnquiredSums = isset($this->sums[$user->id][$this->source()]);
        $userEnquiredSums = $hasEnquiredSums ? array_sum($this->sums[$user->id][$this->source()]) : 0.0;
        $amountWithEnquiredSums = $user->{$this->source()} + $userEnquiredSums + $amountInUserCurrency;
        return $amountWithEnquiredSums >= $amountInUserCurrency; # source = balance or bonus_balance
    }

    public function hasSufficientBalanceToProcessSingular(WhitelabelUser $user, float $amountInUserCurrency): bool
    {
        if ($this->isWelcomeBonus()) {
            return true;
        }

        return $user->{$this->source()} >= $amountInUserCurrency; # source = balance or bonus_balance
    }

    public function __toString(): string
    {
        if ($this instanceof WelcomeBonusBalance) {
            return (string)Helpers_General::PAYMENT_TYPE_WELCOME_BONUS_BALANCE;
        } elseif ($this instanceof BonusBalance) {
            return (string)Helpers_General::PAYMENT_TYPE_BONUS_BALANCE;
        }
        return (string)Helpers_General::PAYMENT_TYPE_BALANCE;
    }

    public function getTicketAmountToPayInUserCurrency(WhitelabelRaffleTicket $ticket): float
    {
        return $ticket->transaction->amount;
    }

    public function getTicketIncomeManager(WhitelabelRaffleTicket $ticket): float
    {
        return $ticket->income_manager;
    }

    protected function enqueue(...$args): void
    {
        [$user, $amountInUserCurrency, $amount, $currencyCode] = $args;
        $this->sums[$user->id][$this->source()][] = $amountInUserCurrency;

        $this->set_task(function () use ($user, $amount, $currencyCode) {

            if ($this->isWelcomeBonus()) {
                $this->updateUserIncomeManager($user, $amount);
            } else {
                $this->updateUserBalanceTask($user, $amount, $currencyCode);
            }

            $this->userDao->clear_cache();
        });
    }

    /**
     * @param WhitelabelUser $user
     * @param float $amountInUserCurrency
     * @throws RuntimeException
     */
    private function verifyHasSufficientBalance(WhitelabelUser $user, float $amountInUserCurrency): void
    {
        if ($this->hasSufficientBalanceToProcess($user, $amountInUserCurrency) === false) {
            unset($this->task);
            throw new RuntimeException('No sufficient balance to continue.');
        }
    }

    private function verifyAmount(float $amount): void
    {
        Assert::greaterThan($amount, 0, 'Amount must be greater than 0');
    }

    public function reset(): void
    {
        $this->sums = [];
    }

    /**
     * @throws Exception
     */
    private function updateUserBalanceTask(WhitelabelUser $user, ?float $amount = null, ?string $currencyCode = null): void
    {
        foreach ($this->sums as $userId => $sources) {
            foreach ($sources as $source => $sums) {
                $sumsCount = count($sums);
                $sumInUserCurrency = array_sum($sums);
                $balanceChangeBeforeConversion = $sumsCount > 1 ? 0.00 : $amount;
                $balanceChangeBeforeConversionCurrencyCode = $sumsCount > 1 ? null : $currencyCode;

                // update user balance
                $this->userRepository->updateFloatField(
                    $userId,
                    $source,
                    $sumInUserCurrency,
                    ['last_update' => $this->db->expr('NOW()')]
                );

                // add record to user's balance change history
                $this->userBalanceLogRepository->addWhitelabelUserBalanceLog(
                    $user->id,
                    'Balance updated',
                    $sumInUserCurrency,
                    $user->currency->code,
                    $balanceChangeBeforeConversion ?: 0,
                    $balanceChangeBeforeConversionCurrencyCode ?: ''
                );
            }
        }
    }

    /**
     * @throws Exception
     */
    private function updateUserIncomeManager(WhitelabelUser $user, ?float $amount = null): void
    {
        $this->userRepository->updateFloatField(
            $user->id,
            ['pnl_manager', 'total_net_income_manager'],
            $amount,
            ['last_update' => $this->db->expr('NOW()')]
        );
    }
}
