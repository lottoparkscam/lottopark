<?php

namespace Helpers;

use Models\WhitelabelMultiDrawOption;

class MultiDrawHelper
{
    public static function calculateMultiDrawTicketPrice(
        WhitelabelMultiDrawOption $whitelabelMultiDrawOption,
        float $price
    ): float {
        $ticketsAmount = $whitelabelMultiDrawOption->tickets;
        $finalPrice = round($price * $ticketsAmount, 4);
        $discount = round($whitelabelMultiDrawOption->discount / 100, 4);
        $discount = round($finalPrice * $discount, 4);
        $finalPrice -= $discount;
        $finalPrice = round($finalPrice / $ticketsAmount, 4);
        $finalPrice = round($finalPrice, 2);
        return round($finalPrice * $ticketsAmount, 4);
    }
}