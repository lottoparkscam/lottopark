<?php

/**
* @author Marcin Klimek <marcin.klimek at gg.international>
* Date: 2020-05-06
* Time: 13:56:08
*/
final class Helper_Lottery // TODO: {Vordis 2020-05-06 13:56:43} all helpers should be singular in name
{

    /**
     * Calculate value for jackpot field.
     *
     * @param string $value value in normal decimal notation
     * @return float
     */
    public static function calculate_jackpot_value(string $value): float
    {
        return round((float)$value / 1000000, 8);
    }
}