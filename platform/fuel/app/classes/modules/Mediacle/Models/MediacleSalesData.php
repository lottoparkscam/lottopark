<?php

namespace Modules\Mediacle\Models;

interface MediacleSalesData extends TrackingIdAwareContract
{
    /**
     * An unique id associated with each player
     * @return string
     */
    public function getPlayerId(): string;

    public function getBrand(): string;

    /**
     * The date for which the data belongs to
     * @return string
     */
    public function getTransactionDate(): string;

    /**
     * The amount (in USD) of deposits made on the TRANSACTION_DATE
     * @return float
     */
    public function getDeposits(): float;

    /**
     * Total stakes (in USD) on the TRANSACTION_DATE
     * @return float
     */
    public function getBets(): float;

    public function getCosts(): float;

    public function getPaymentCosts(): float;

    public function getRoyalties(): float;

    /**
     * Total wins (in USD) on the TRANSACTION_DATE
     * @return float
     */
    public function getWins(): float;

    public function getGgr(): float;
    public function getCasinoBonusBalance(): float;

    /**
     * The amount (in USD) of chargebacks on the TRANSACTION_DATE
     * @return float
     */
    public function getChargeBacks(): float;

    /**
     * The amount (in USD) of bonuses awarded to player on TRANSACTION_DATE
     * @return float
     */
    public function getReleasedBonuses(): float;

    /**
     * The Net Gaming Revenue on the TRANSACTION_DATE
     * @return float
     */
    public function getRevenues(): float;

    /**
     * The Exchange Rate to convert the above amounts to GBP.
     * Edit: They decided that all fields above will be in USD, so they do not need this value atm.
     *
     * @return float|null
     */
    public function getCurrencyRateToGbp(): ?float;

    /**
     * The affiliate ID tagged to the player
     * @return string|null
     */
    public function getTrackingId(): ?string;

    public function getFirstDepositDate(): ?string;

    /**
     * Any promo code used by the player while signing up
     *
     * @return string
     */
    public function getPromoCode(): ?string;

    /** timestamp in nanoseconds */
    public function getTimeStamp(): string;
}
