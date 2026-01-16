<?php

namespace Modules\Mediacle\Models;

use Exception;
use Models\Whitelabel;
use Models\WhitelabelCampaign;
use Models\WhitelabelTransaction;
use Modules\Mediacle\Models\MediacleSalesData;

class SalesDataWhitelabelTransactionModelAdapter implements MediacleSalesData
{
    private WhitelabelTransaction $transaction;

    public function __construct(WhitelabelTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function getPlayerId(): string
    {
        return $this->transaction->whitelabel->prefix . 'U' . $this->transaction->user->token;
    }

    public function getBrand(): string
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = $this->transaction->whitelabel;
        return $whitelabel->name;
    }

    public function getTransactionDate(): string
    {
        return $this->transaction->date->format('mysql');
    }

    public function getDeposits(): float
    {
        return $this->transaction->is_deposit_type ? $this->transaction->amount_usd : 0.0;
    }

    public function getBets(): float
    {
        return $this->transaction->is_purchase_type ? $this->transaction->amount_usd : 0.0;
    }

    public function getCosts(): float
    {
        return $this->transaction->cost_usd ?: 0.0;
    }

    public function getPaymentCosts(): float
    {
        return $this->transaction->payment_cost_usd ?: 0.0;
    }

    public function getRoyalties(): float
    {
        return $this->transaction->margin_usd ?: 0.0;
    }

    public function getWins(): float
    {
        $sum = 0.0;

        if (!empty($this->transaction->whitelabel_tickets)) {
            foreach ($this->transaction->whitelabel_tickets as $ticket) {
                $sum += $ticket->prize_usd;
            }
        }

        if (!empty($this->transaction->whitelabel_raffle_ticket)) {
            $sum += $this->transaction->whitelabel_raffle_ticket->prize_usd;
        }

        return $sum;
    }

    public function getChargeBacks(): float
    {
        return 0.0;
    }

    public function getReleasedBonuses(): float
    {
        return 0.0;
    }

    public function getRevenues(): float
    {
        $sum = 0.0;

        if (!empty($this->transaction->whitelabel_tickets)) {
            foreach ($this->transaction->whitelabel_tickets as $ticket) {
                $sum += $ticket->income_usd;
            }
        }

        if (!empty($this->transaction->whitelabel_raffle_ticket)) {
            $sum += $this->transaction->whitelabel_raffle_ticket->income_usd;
        }

        return $sum;
    }

    public function getCurrencyRateToGbp(): ?float
    {
        return null;
    }

    public function getTrackingId(): ?string
    {
        return $this->transaction->user->whitelabel_user_aff->whitelabel_aff->token ?? null;
    }

    public function getFirstDepositDate(): ?string
    {
        return $this->transaction->user->first_deposit;
    }

    public function getPromoCode(): ?string
    {
        /** @var WhitelabelCampaign $campaign */
        $campaign = $this
                ->transaction
                ->user
                ->whitelabel_user_promo_code
                ->whitelabel_promo_code->whitelabel_campaign ?? null;
        if ($campaign !== null && $campaign->isRegister()) {
            return $this->transaction
                ->user
                ->whitelabel_user_promo_code
                ->whitelabel_promo_code
                ->whitelabel_campaign
                ->token;
        }
        return null;
    }

    /**
     * @throws Exception possible db exception if transaction save failed.
     */
    public function getTimeStamp(): string
    {
        $published = $this->transaction->published_at_timestamp !== null;
        if ($published) {
            return $this->transaction->published_at_timestamp;
        }

        $newPublishedAt = sprintf("%u", microtime(true) * 1000000000);
        $this->transaction->published_at_timestamp = $newPublishedAt;
        $this->transaction->saveOrThrow();
        // TODO: {Vordis 2021-05-18 16:32:44} could and should be done as mass insert,
        // I did it this way for fast release (MVP)
        return $this->transaction->published_at_timestamp;
    }

    public function getBtag(): ?string
    {
        return $this->transaction->user->whitelabel_user_aff->btag ?? null;
    }

    /** GGR is only for casino */
    public function getGgr(): float
    {
        return 0.0;
    }

    /** CBB is only for casino */
    public function getCasinoBonusBalance(): float
    {
        return 0.0;
    }
}
