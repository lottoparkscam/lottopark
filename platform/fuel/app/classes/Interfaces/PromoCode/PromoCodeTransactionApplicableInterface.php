<?php

namespace Interfaces\PromoCode;

interface PromoCodeTransactionApplicableInterface
{
    public function getTransactionType(): int;

    public function usePromoCodeForWhitelabelTransaction(int $transactionId): void;
}