<?php

namespace Services\MiniGame\Dto;

final class MiniGameData
{
    private string $name;
    private string $slug;
    private bool $isEnabled;
    private float $multiplier;
    private array $availableBets;
    private float $defaultBet;
    private string $balance;
    private string $bonusBalance;
    private array $history;
    private ?int $freeSpinCount;
    private ?int $usedFreeSpinCount;
    private ?bool $hasUsedAllSpins;
    private ?float $freeSpinValue;

    public function __construct(
        string $name,
        string $slug,
        bool $isEnabled,
        float $multiplier,
        array $availableBets,
        float $defaultBet,
        string $balance,
        string $bonusBalance,
        array $history,
        ?int $freeSpinCount,
        ?int $usedFreeSpinCount,
        ?bool $hasUsedAllSpins,
        ?float $freeSpinValue,
    )
    {
        $this->name = $name;
        $this->slug = $slug;
        $this->isEnabled = $isEnabled;
        $this->multiplier = $multiplier;
        $this->availableBets = $availableBets;
        $this->defaultBet = $defaultBet;
        $this->balance = $balance;
        $this->bonusBalance = $bonusBalance;
        $this->history = $history;
        $this->freeSpinCount = $freeSpinCount;
        $this->usedFreeSpinCount = $usedFreeSpinCount;
        $this->hasUsedAllSpins = $hasUsedAllSpins;
        $this->freeSpinValue = $freeSpinValue;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'isEnabled' => $this->isEnabled,
            'multiplier' => $this->multiplier,
            'availableBets' => $this->availableBets,
            'defaultBet' => $this->defaultBet,
            'balance' => $this->balance,
            'bonusBalance' => $this->bonusBalance,
            'history' => $this->history,
            'freeSpinData' => [
                'freeSpinCount' => $this->freeSpinCount,
                'usedFreeSpinCount' => $this->usedFreeSpinCount,
                'freeSpinValue' => $this->freeSpinValue,
                'hasUsedAllSpins' => $this->hasUsedAllSpins,
            ],
        ];
    }
}
