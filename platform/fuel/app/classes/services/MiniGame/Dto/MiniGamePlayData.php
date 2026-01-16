<?php

namespace Services\MiniGame\Dto;

use Models\MiniGame;
use Models\WhitelabelUser;

final class MiniGamePlayData
{
    private MiniGame $miniGame;
    private WhitelabelUser $user;
    private float $betAmountInEur;
    private float $betAmountInUserCurrency;
    private ?int $userPromoCodeId;
    private bool $isFreeSpin;

    public function __construct(MiniGame $miniGame, WhitelabelUser $user, float $betAmountInEur, float $betAmountInUserCurrency, ?int $userPromoCodeId, bool $isFreeSpin)
    {
        $this->miniGame = $miniGame;
        $this->user = $user;
        $this->betAmountInEur = $betAmountInEur;
        $this->betAmountInUserCurrency = $betAmountInUserCurrency;
        $this->userPromoCodeId = $userPromoCodeId;
        $this->isFreeSpin = $isFreeSpin;
    }

    public function isSelectionValid(int $selectedNumber): bool
    {
        return in_array($this->betAmountInEur, $this->miniGame->availableBets) &&
            ($selectedNumber >= $this->miniGame->drawRangeStart && $selectedNumber <= $this->miniGame->drawRangeEnd);
    }

    public function hasSufficientBalance(): bool
    {
        return $this->user->balance >= $this->betAmountInUserCurrency || $this->user->bonusBalance >= $this->betAmountInUserCurrency;
    }

    public function getBetAmount(): float
    {
        return $this->betAmountInEur;
    }

    public function getBetAmountInUserCurrency(): float
    {
        return $this->betAmountInUserCurrency;
    }

    public function isFreeSpin(): bool
    {
        return $this->isFreeSpin;
    }

    public function getPromoCodeUserId(): ?int
    {
        return $this->userPromoCodeId;
    }
}
