<?php

/**
 * Description of Validator_Wordpress_Currency
 */
class Validator_Wordpress_Currency
{
    /**
     *
     * @param string $value_to_process
     * @return bool
     */
    public static function is_currency_ok($value_to_process): bool
    {
        $ret_value = false;
        $regex = "/^([0-9]+[\,\.]{1}[0-9]+$)|(^[0-9]+$)/";
        
        $trimmed_value = trim($value_to_process);
        
        if (preg_match($regex, $trimmed_value)) {
            $ret_value = true;
        }
        
        return $ret_value;
    }
    
    /**
     *
     * @param string $value_to_check
     * @param string $value_to_compare
     * @return bool
     */
    public static function check_min_formatted($value_to_check, $value_to_compare): bool
    {
        $ret_val = false;
        $value_to_process = str_replace(",", ".", $value_to_check);
        $final_value = round($value_to_process, 2);
        
        $value_to_compare_to_process = str_replace(",", ".", $value_to_compare);
        $final_value_to_compare = round($value_to_compare_to_process, 2);
        
        if ($final_value >= $final_value_to_compare) {
            $ret_val = true;
        }
            
        return $ret_val;
    }
    
    /**
     *
     * @param string $value_to_check
     * @param string $value_to_compare
     * @return bool
     */
    public static function check_max_formatted($value_to_check, $value_to_compare): bool
    {
        $ret_val = false;
        $value_to_process = str_replace(",", ".", $value_to_check);
        $final_value = round($value_to_process, 2);
        
        $value_to_compare_to_process = str_replace(",", ".", $value_to_compare);
        $final_value_to_compare = round($value_to_compare_to_process, 2);
        
        if ($final_value <= $final_value_to_compare) {
            $ret_val = true;
        }
            
        return $ret_val;
    }
}
