<?php

namespace Services\MiniGame\ValueObject;

use Models\MiniGame;
use Models\WhitelabelUser;

final class MiniGameTransactionObject
{
    private MiniGame $miniGame;
    private WhitelabelUser $user;
    private float $betAmountInEur;
    private float $prizeAmountInEur;
    private bool $isWin;
    private int $selectedNumber;
    private int $systemDrawnNumber;
    private ?int $promoCodeUserId;
    private float $betAmountInUserCurrency;
    private float $prizeAmountInUserCurrency;
    private bool $isFreeSpin;
    private bool $isBonusBalancePaid = false;
    private array $additionalData;

    public function __construct(
        MiniGame $miniGame,
        WhitelabelUser $user,
        float $betAmountInEur,
        float $prizeAmountInEur,
        bool $isWin,
        int $selectedNumber,
        int $systemDrawnNumber,
        ?int $promoCodeUserId,
        float $betAmountInUserCurrency,
        float $prizeAmountInUserCurrency,
        bool $isFreeSpin,
        array $additionalData,
    ) {
        $this->miniGame = $miniGame;
        $this->user = $user;
        $this->betAmountInEur = $betAmountInEur;
        $this->prizeAmountInEur = $prizeAmountInEur;
        $this->isWin = $isWin;
        $this->selectedNumber = $selectedNumber;
        $this->systemDrawnNumber = $systemDrawnNumber;
        $this->promoCodeUserId = $promoCodeUserId;
        $this->betAmountInUserCurrency = $betAmountInUserCurrency;
        $this->prizeAmountInUserCurrency = $prizeAmountInUserCurrency;
        $this->isFreeSpin = $isFreeSpin;
        $this->additionalData = $additionalData;
    }

    public function getMiniGame(): MiniGame
    {
        return $this->miniGame;
    }

    public function getUser(): WhitelabelUser
    {
        return $this->user;
    }

    public function getBalanceBefore(): float
    {
        if ($this->isFreeSpin()) {
            return $this->getUserBalance();
        }

        return $this->getUserBalance() - $this->getBetAmountInUserCurrency();
    }

    public function getBalanceAfter(): float
    {
        $balanceBefore = $this->getBalanceBefore();

        if ($this->isWin()) {
            return $balanceBefore + $this->getPrizeAmountInUserCurrency();
        }

        return $balanceBefore;
    }

    public function getBonusBalanceBefore(): float
    {
        if ($this->isFreeSpin()) {
            return $this->getUserBonusBalance();
        }

        return $this->getUserBonusBalance() - $this->getBetAmountInUserCurrency();
    }

    public function getBonusBalanceAfter(): float
    {
        $bonusBalanceBefore = $this->getBonusBalanceBefore();

        if ($this->isFreeSpin()) {
            if ($this->isWin()) {
                return $this->getUserBonusBalance() + $this->getPrizeAmountInUserCurrency();
            }

            return $this->getUserBonusBalance();
        }

        if ($this->isWin()) {
            return $bonusBalanceBefore;
        }

        return $this->getUserBonusBalance() - $this->getBetAmountInUserCurrency();
    }

    public function getUserBalance(): float
    {
        return $this->user->balance;
    }

    public function getUserBonusBalance(): float
    {
        return $this->user->bonusBalance;
    }

    public function getUserCurrencyCode(): string
    {
        return $this->user->currency->code;
    }

    public function getBetAmountInEur(): float
    {
        return $this->betAmountInEur;
    }

    public function getPrizeAmountInEur(): float
    {
        return $this->prizeAmountInEur;
    }

    public function isWin(): bool
    {
        return $this->isWin;
    }

    public function getSelectedNumber(): int
    {
        return $this->selectedNumber;
    }

    public function getSystemDrawnNumber(): int
    {
        return $this->systemDrawnNumber;
    }

    public function getPromoCodeUserId(): ?int
    {
        return $this->promoCodeUserId;
    }

    public function getBetAmountInUserCurrency(): float
    {
        return $this->betAmountInUserCurrency;
    }

    public function getPrizeAmountInUserCurrency(): float
    {
        return $this->prizeAmountInUserCurrency;
    }

    public function isFreeSpin(): bool
    {
        return $this->isFreeSpin;
    }

    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }

    public function setBonusBalancePaid(bool $bonusBalancePaid): void
    {
        $this->isBonusBalancePaid = $bonusBalancePaid;
    }

    public function isBonusBalancePaid(): bool
    {
        return $this->isBonusBalancePaid;
    }
}
