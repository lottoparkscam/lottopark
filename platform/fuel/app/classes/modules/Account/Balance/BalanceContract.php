<?php

namespace Modules\Account\Balance;

use Models\WhitelabelUser;
use Services\Shared\DispatchAble;
use Models\WhitelabelRaffleTicket;

interface BalanceContract extends DispatchAble
{
    public function debit(int $userId, float $amount, string $currencyCode): void;

    public function debitByTicket(WhitelabelRaffleTicket $ticket): void;

    public function increase(int $userId, float $amount, string $currencyCode): void;

    public function hasSufficientBalanceToProcess(WhitelabelUser $user, float $amountInUserCurrency): bool;
    public function hasSufficientBalanceToProcessSingular(WhitelabelUser $user, float $amountInUserCurrency): bool;

    public function isWelcomeBonus(): bool;

    /**
     * @return string - irl the field from user table which suppose to be updated
     * (it's still not the best solution, but better than existing one).
     */
    public function source(): string;

    /**
     * @return string - ugly trick to retrieve HelperGenerals payment method
     */
    public function __toString(): string;

    public function getTicketAmountToPayInUserCurrency(WhitelabelRaffleTicket $ticket): float;
}
