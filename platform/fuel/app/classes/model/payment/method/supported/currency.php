<?php

/**
 *
 */
class Model_Payment_Method_Supported_Currency extends \Model_Model
{
    
    /**
     *
     * @var string
     */
    protected static $_table_name = 'payment_method_supported_currency';
    
    /**
     *
     * @var array
     */
    protected static $cache_list = [
        "payment_method_supported_currency.paymentmethodsupportedcurrencies"
    ];
    
    /**
     *
     * @param int $payment_method_id
     * @return array
     */
    public static function get_list_by_payment_method_id(int $payment_method_id): array
    {
        $params = [];
        $params[] = [
            ":payment_method_id", $payment_method_id
        ];
        
        $query_string = "SELECT 
            * 
        FROM payment_method_supported_currency 
        WHERE payment_method_id = :payment_method_id 
        ORDER BY code";
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        
        // safely retrieve value
        return parent::get_array_result($result, []);
    }
    
    /**
     *
     * @param int $payment_method_id
     * @param string $currency_code
     * @return int
     */
    public static function get_zero_decimal_value(
        int $payment_method_id,
        string $currency_code
    ): int {
        $params = [];
        $params[] = [
            ":payment_method_id", $payment_method_id
        ];
        $params[] = [
            ":currency_code", $currency_code
        ];
        
        $query_string = "SELECT 
            is_zero_decimal 
        FROM payment_method_supported_currency 
        WHERE payment_method_id = :payment_method_id 
            AND code = :currency_code ";
        
        $result = parent::execute_query($query_string, $params);
        
        return parent::get_array_result_item($result, 0, 0, 'is_zero_decimal');
    }
}
