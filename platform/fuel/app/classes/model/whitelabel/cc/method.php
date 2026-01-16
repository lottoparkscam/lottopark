<?php

use Services\Logs\FileLoggerService;

class Model_Whitelabel_CC_Method extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_cc_method';

    /**
     *
     * @var array
     */
    public static $cache_list = [
        "model_whitelabel_cc_method.ccmethods"
    ];

    /**
     * This is prepared for future purposes if (eventually will change) :)
     * And I think this is the best place for that function :D
     * @return int
     */
    public static function get_emerchant_method_id(): int
    {
        // As Credit card ID of current existent row in DB (at this moment it is only that gateway)
        return Helpers_Payment_Method::CC_EMERCHANT;
    }

    /**
     *
     * @param array $whitelabel_cc_methods
     * @return array
     */
    public static function prepare_payment_methods(array $whitelabel_cc_methods): array
    {
        $whitelabel_cc_methods_prepared = [];
        foreach ($whitelabel_cc_methods as $whitelabel_cc_method) {
            $whitelabel_cc_methods_prepared[$whitelabel_cc_method['id']] = $whitelabel_cc_method;
        }
        return $whitelabel_cc_methods_prepared;
    }

    /**
     * This is function which could not be used any more
     * till time that CC functionality will be run again
     * and in that case currency for CC should be develop
     *
     * @param array $whitelabel
     * @return null|array
     */
    public static function get_cc_methods_for_whitelabel($whitelabel): ?array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $expiredTime = Helpers_Whitelabel::get_expired_time();
        $key = self::$cache_list[0];
        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $key .= '.'.$whitelabel['id'];
        }

        $whitelabel_cc_methods = null;

        $query = "SELECT 
                whitelabel_cc_method.*,
                currency.id AS cid,
                currency.code AS currency_code,
                currency.rate AS currency_rate 
            FROM whitelabel_cc_method 
            INNER JOIN currency ON whitelabel_cc_method.payment_currency_id = currency.id 
            WHERE 1=0 ";

        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $query .= " AND whitelabel_id = :whitelabel";
        }

        $query .= " ORDER BY id";

        $db = DB::query($query);
        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $db->param(":whitelabel", $whitelabel['id']);
        }

        try {
            try {
                $whitelabel_cc_methods = Lotto_Helper::get_cache($key);
            } catch (\CacheNotFoundException $e) {
                $methods_res = $db->execute()->as_array();
                $whitelabel_cc_methods = self::prepare_payment_methods($methods_res);
                Lotto_Helper::set_cache($key, $whitelabel_cc_methods, $expiredTime);
            }
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
            $methods_res = $db->execute()->as_array();
            $whitelabel_cc_methods = self::prepare_payment_methods($methods_res);
        }

        return $whitelabel_cc_methods;
    }

    /**
     * This function should not be used any more,
     * If in the future CC payments will be run again
     * the first thing which is needed is to build
     * functionality for currencies for CC!
     *
     * @param array $whitelabel
     * @param int $method_id
     * @return null|array
     */
    public static function get_payment_currency_for_whitelabel($whitelabel, $method_id):? array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $res = null;
        $query = "SELECT 
                c.id AS id,
                c.code,
                c.rate 
            FROM whitelabel_cc_method wcm 
            INNER JOIN currency c ON wcm.payment_currency_id = c.id
            WHERE 1=1 ";
        
        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $query .= " AND wcm.whitelabel_id = :whitelabel_id";
        }
        
        if (!empty($method_id)) {
            $query .= " AND wcm.method = :method_id";
        }

        $query .= " LIMIT 1 ";
        
        try {
            $db = DB::query($query);
            
            if (!empty($whitelabel) && !empty($whitelabel['id'])) {
                $db->param(":whitelabel_id", $whitelabel['id']);
            }
            
            if (!empty($method_id)) {
                $db->param(":method_id", $method_id);
            }
            
            $res = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        if ($res === null || count($res) == 0) {
            return $result;
        }
        
        $result = $res[0];
                
        return $result;
    }
}
