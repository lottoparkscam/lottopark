<?php

namespace Services\MiniGame\Dto;

final class GameApplyPromoCodeResult
{
    private bool $isSuccess;
    private ?string $message;

    public function __construct(bool $isSuccess, ?string $message)
    {
        $this->isSuccess = $isSuccess;
        $this->message = $message;
    }

    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function toArray(): array
    {
        return [
            'isSuccess' => $this->isSuccess(),
            'message' => $this->getMessage(),
        ];
    }
}
