<?php

namespace Services\MiniGame\Dto;

final class GamePlayResult
{
    private bool $success;
    private ?MiniGameResult $result;
    private ?int $errorCode;

    public function __construct(bool $success, ?int $errorCode, ?MiniGameResult $result = null)
    {
        $this->success = $success;
        $this->result = $result;
        $this->errorCode = $errorCode;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getResult(): ?MiniGameResult
    {
        return $this->result;
    }

    public function getErrorCode(): ?int
    {
        return $this->errorCode;
    }
}
