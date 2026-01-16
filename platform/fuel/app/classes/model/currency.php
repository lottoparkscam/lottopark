<?php

use Services\Logs\FileLoggerService;

/**
 * @property-read int $rate
 */
class Model_Currency extends \Fuel\Core\Model_Crud
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'currency';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [
        "model_currency.allcurrencies"
    ];

    /**
     *
     * @param array $currencies
     * @return array
     */
    private static function prepare_all_currencies($currencies)
    {
        $ncurrencies = [];
        foreach ($currencies as $currency) {
            $ncurrencies[$currency['id']] = $currency;
        }
        return $ncurrencies;
    }

    /**
     *
     * @return null|array
     */
    public static function get_all_currencies()
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $query = "SELECT * 
            FROM currency 
            ORDER BY id";
        
        $db = DB::query($query);
        $currencies = null;
        
        $key = self::$cache_list[0];
        $expiredTime = Helpers_Whitelabel::get_expired_time();
        
        try {
            try {
                $currencies = Lotto_Helper::get_cache($key);
            } catch (\CacheNotFoundException $e) {
                /** @var object $db */
                $currencies = self::prepare_all_currencies($db->execute()->as_array());
                Lotto_Helper::set_cache($key, $currencies, $expiredTime);
            }
        } catch (Exception $e) {
            $fileLoggerService->error( 
                $e->getMessage()
            );
            $currencies = self::prepare_all_currencies($db->execute()->as_array());
        }
        
        return $currencies;
    }
}
