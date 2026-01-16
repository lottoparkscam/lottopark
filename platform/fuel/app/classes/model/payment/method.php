<?php

use Services\Logs\FileLoggerService;

class Model_Payment_Method extends Model_Model
{
    
    /**
     *
     * @var string
     */
    protected static $_table_name = 'payment_method';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [
        "model_payment_method.paymentmethods"
    ];

    /**
     *
     * @param array $methods
     * @return array
     */
    public static function prepare_payment_methods($methods)
    {
        $nmethods = [];
        foreach ($methods as $method) {
            $nmethods[$method['id']] = $method;
        }
        return $nmethods;
    }

    /**
     *
     * @return array
     */
    public static function get_payment_methods()
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $expiredTime = Helpers_Whitelabel::get_expired_time();
        $key = self::$cache_list[0];
        
        $query = "SELECT * 
            FROM payment_method 
            ORDER BY id";
        
        $db = DB::query($query);
        
        try {
            try {
                $methods = Lotto_Helper::get_cache($key);
            } catch (\CacheNotFoundException $e) {
                $methods = $db->execute()->as_array();
                $methods = self::prepare_payment_methods($methods);
                Lotto_Helper::set_cache($key, $methods, $expiredTime);
            }
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
            $methods = $db->execute()->as_array();
            $methods = self::prepare_payment_methods($methods);
        }
        
        return $methods;
    }
}
