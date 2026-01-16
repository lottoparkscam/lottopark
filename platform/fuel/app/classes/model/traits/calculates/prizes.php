<?php

/**
* This trait is aimed at lines and tickets.
* It injects prize calculation functionality.
*/
trait Model_Traits_Calculates_Prizes
{
    /**
     * Calculate prize_usd based on prize_local.
     *
     * @param integer $lottery_currency_id
     * @param array $currencies
     * @param string $prize_local
     * @return string
     */
    private static function calculate_prize_usd(int $lottery_currency_id, array $currencies, string $prize_local): string
    {
        return Helpers_Currency::get_recalculated_to_given_currency(
            $prize_local,
            $currencies[$lottery_currency_id],
            'USD'
        );
    }

    /**
     * Calculate prize in user currency, based on prize_local.
     *
     * @param integer $lottery_currency_id
     * @param integer $user_currency_id
     * @param array $currencies
     * @param string $prize_local
     * @return string
     */
    public static function calculate_user_prize(int $lottery_currency_id, int $user_currency_id, array $currencies, string $prize_local): string
    {
        return Helpers_Currency::get_recalculated_to_given_currency(
            $prize_local,
            $currencies[$lottery_currency_id],
            $currencies[$user_currency_id]['code']
        );
    }

    /**
     * Calculate prize with taxes.
     * NOTE: it will properly return the same value if taxes should not be calculated.
     *
     * @param string $prize
     * @param Model_Lottery_Provider $provider
     * @return float
     */
    public static function calculate_prize_net(string $prize, Model_Lottery_Provider $provider): float
    {
        if ($prize <= $provider->tax_min) { // prize is lower than taxable amount - don't calculate tax
            return $prize;
        }
        
        $tax_percentage = $provider->tax / 100;
        return (float)$prize - (float)$prize * $tax_percentage; // NOTE: $provider->tax=0 will return untouched prize
    }

    /**
     * Calculate prize in whitelabel currency (whitelabel that owns this ticket/s)
     *
     * @param integer $lottery_currency_id
     * @param integer $manager_currency_id
     * @param array $currencies
     * @param string $prize_local
     * @return string
     */
    private static function calculate_prize_manager(int $lottery_currency_id, int $manager_currency_id, array $currencies, string $prize_local): string
    {
        return Helpers_Currency::get_recalculated_to_given_currency(
            $prize_local,
            $currencies[$lottery_currency_id],
            $currencies[$manager_currency_id]['code']
        );
    }
    
}
