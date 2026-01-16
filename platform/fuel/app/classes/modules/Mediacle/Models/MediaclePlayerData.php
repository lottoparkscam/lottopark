<?php

namespace Modules\Mediacle\Models;

interface MediaclePlayerData extends TrackingIdAwareContract, TimeStampAbleContract
{
    /**
     * An unique id associated with each player
     *
     * @return string
     */
    public function getPlayerId(): string;

    public function getBrand(): string;

    /**
     * Player’s country (2 characters length)
     *
     * @return string
     */
    public function getCountryCode(): ?string;

    /**
     * Date, time in format YYYY-mm-dd HH:ii:ss
     *
     * @return string
     */
    public function getAccountOpeningDate(): string;

    /**
     * Any promo code used by the player while signing up
     *
     * @return string
     */
    public function getPromoCode(): ?string;
}
