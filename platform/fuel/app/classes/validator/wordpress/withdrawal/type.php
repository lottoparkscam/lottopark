<?php

/**
 * Description of Validator_Wordpress_Withdrawal_Type
 */
class Validator_Wordpress_Withdrawal_Type
{
    /**
     *
     * @param string $value_to_process
     * @return bool
     */
    public static function check_min_value($value_to_process): bool
    {
        $return_value = false;
        
        $trimmed_value = trim($value_to_process);
        
        if ((int)$trimmed_value > 0) {
            $return_value = true;
        }
        
        return $return_value;
    }
}
