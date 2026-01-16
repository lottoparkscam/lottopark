<?php

namespace Modules\Mediacle\Models;

use Carbon\Carbon;
use Modules\Mediacle\Models\MediacleSalesData;

class SalesDataSlotTransactionModelAdapter implements MediacleSalesData
{
    private array $transaction;

    public function __construct(array $transaction)
    {
        $this->transaction = $transaction;
    }

    public function getPlayerId(): string
    {
        return $this->transaction['user_token'];
    }

    public function getBrand(): string
    {
        return $this->transaction['whitelabel_name'];
    }

    public function getTransactionDate(): string
    {
        return $this->transaction['created_at'];
    }

    /**
     * Deposits are prepared in SalesDataWhitelabelTransactionModelAdapter
     * We save them in whitelabel_transaction
     */
    public function getDeposits(): float
    {
        return 0.0;
    }

    public function getBets(): float
    {
        return $this->transaction['bets'];
    }

    public function getCosts(): float
    {
        return $this->transaction['costs'];
    }

    public function getGgr(): float
    {
        return $this->transaction['ggr'];
    }

    public function getCasinoBonusBalance(): float
    {
        return $this->transaction['casino_bonus_balance'];
    }

    public function getPaymentCosts(): float
    {
        return 0.0;
    }

    public function getRoyalties(): float
    {
        return 0.0;
    }

    public function getWins(): float
    {
        return $this->transaction['wins'];
    }

    public function getChargeBacks(): float
    {
        return 0.0;
    }

    public function getReleasedBonuses(): float
    {
        return 0.0;
    }

    /**
     * Revenue = NGR = ggr - 0.08ggr - casino_bonus_balance
     * 0.008 is average tax
     */
    public function getRevenues(): float
    {
        return $this->transaction['revenue'];
    }

    public function getCurrencyRateToGbp(): ?float
    {
        return null;
    }

    public function getTrackingId(): ?string
    {
        return $this->transaction['tracking_id'] ?? null;
    }

    public function getFirstDepositDate(): ?string
    {
        return $this->transaction['first_deposit'];
    }

    public function getPromoCode(): ?string
    {
        return null;
    }

    public function getTimeStamp(): string
    {
        $createdAt = Carbon::create($this->transaction['created_at'])->timestamp;

        return $createdAt;
    }

    public function getBtag(): ?string
    {
        return $this->transaction['btag'] ?? null;
    }
}
