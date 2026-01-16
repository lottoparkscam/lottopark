<?php

namespace Services\MiniGame\ValueObject;

use Models\WhitelabelUser;

final class MiniGameBalanceTransactionObject
{
    private WhitelabelUser $user;
    private float $betAmountInUserCurrency;
    private float $prizeAmountInUserCurrency;
    private bool $isWin;
    private bool $isFreeSpin;

    public function __construct(WhitelabelUser $user, float $betAmountInUserCurrency, float $prizeAmountInUserCurrency, bool $isWin, bool $isFreeSpin)
    {
        $this->user = $user;
        $this->betAmountInUserCurrency = $betAmountInUserCurrency;
        $this->prizeAmountInUserCurrency = $prizeAmountInUserCurrency;
        $this->isWin = $isWin;
        $this->isFreeSpin = $isFreeSpin;
    }

    public function getUserBalance(): float
    {
        return $this->user->balance;
    }

    public function getUserBonusBalance(): float
    {
        return $this->user->bonusBalance;
    }

    public function getUserId(): int
    {
        return $this->user->id;
    }

    public function getBetAmountInUserCurrency(): float
    {
        return $this->betAmountInUserCurrency;
    }

    public function getPrizeAmountInUserCurrency(): float
    {
        return $this->prizeAmountInUserCurrency;
    }

    public function calculateAmountChange(): float
    {
        if ($this->isFreeSpin) {
            return 0.0;
        }

        return ($this->isWin ? $this->prizeAmountInUserCurrency : 0.0) - $this->betAmountInUserCurrency;
    }

    public function isFreeSpin(): bool
    {
        return $this->isFreeSpin;
    }

    public function isWin(): bool
    {
        return $this->isWin;
    }
}
