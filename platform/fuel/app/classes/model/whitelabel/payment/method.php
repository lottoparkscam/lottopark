<?php

use Fuel\Core\Database_Result;
use Services\Logs\FileLoggerService;

final class Model_Whitelabel_Payment_Method extends Model_Model
{
    
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_payment_method';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [
        "model_whitelabel_payment_method.paymentmethods",
        "model_whitelabel_payment_method.admin_paymentmethods",
    ];

    /**
     *
     * @param array $whitelabel_payment_methods_without_currency
     * @return array
     */
    public static function prepare_payment_methods(
        array $whitelabel_payment_methods_without_currency
    ): array {
        $whitelabel_payment_methods_prepared = [];
        foreach ($whitelabel_payment_methods_without_currency as $whitelabel_payment_method) {
            $whitelabel_payment_methods_prepared[$whitelabel_payment_method['id']] = $whitelabel_payment_method;

            // Rename from Apcopay CC to Credit Card
            if ($whitelabel_payment_method['payment_method_id'] == Helpers_Payment_Method::APCOPAY_CC) {
                $whitelabel_payment_methods_prepared[$whitelabel_payment_method['id']]['name'] = _("Credit Card");
                $whitelabel_payment_methods_prepared[$whitelabel_payment_method['id']]['pname'] = _("Credit Card");
            }
        }
        return $whitelabel_payment_methods_prepared;
    }

    /**
     *
     * @param array $whitelabel
     * @param int $cache_list_id Default is equal 0
     * @return array
     */
    public static function get_payment_methods_for_whitelabel(
        $whitelabel,
        $cache_list_id = 0
    ): array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $expiredTime = Helpers_Whitelabel::get_expired_time();
        $key = self::$cache_list[$cache_list_id];
        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $key .= '.' . $whitelabel['id'];
        }

        if (!empty(IS_CASINO)) {
            $key .= '_casino';
        }
        
        $query = "SELECT 
            wpm.*,
            pm.name AS pname 
        FROM whitelabel_payment_method wpm 
        INNER JOIN payment_method pm ON pm.id = wpm.payment_method_id";

        if (!empty(IS_CASINO)) {
            $query .= ' WHERE is_enabled_for_casino = 1';
        } else {
            $query .= ' WHERE 1=1';
        }
        
        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $query .= " AND wpm.whitelabel_id = :whitelabel_id ";
        }
            
        $query .= " ORDER BY wpm.language_id, wpm.order";
        
        $db = DB::query($query);
        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $db->param(":whitelabel_id", $whitelabel['id']);
        }
        
        try {
            try {
                $whitelabel_payment_methods = Lotto_Helper::get_cache($key);
            } catch (\CacheNotFoundException $e) {
                $whitelabel_payment_methods = $db->execute()->as_array();
                $whitelabel_payment_methods = self::prepare_payment_methods($whitelabel_payment_methods);
                Lotto_Helper::set_cache($key, $whitelabel_payment_methods, $expiredTime);
            }
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
            $whitelabel_payment_methods = $db->execute()->as_array();
            $whitelabel_payment_methods = self::prepare_payment_methods($whitelabel_payment_methods);
        }
        
        return $whitelabel_payment_methods;
    }

    /**
     *
     * @param int $whitelabel_id
     * @return array
     */
    public static function get_payment_methods_for_whitelabel_id($whitelabel_id)
    {
        $res = [];
        $query = DB::select('whitelabel_payment_method.*', ['payment_method.name', 'pname'])->from('whitelabel_payment_method')
        ->join('payment_method', 'INNER')->on('whitelabel_payment_method.payment_method_id', '=', 'payment_method.id')
        ->where('whitelabel_payment_method.show', '=', 1);

        if (!empty($whitelabel_id)) {
            $query->and_where('whitelabel_payment_method.whitelabel_id', '=', $whitelabel_id);
        }
        $query->order_by('whitelabel_payment_method.order');

        $res = $query->execute();
       
        return $res;
    }

    public static function get_all_payment_methods_for_whitelabel_id(int $whitelabel_id, bool $casinoOnlyEnabledPaymentMethods = false): Database_Result
    {
        $query = DB::select('whitelabel_payment_method.*')->from('whitelabel_payment_method')
            ->join('payment_method', 'INNER')->on('whitelabel_payment_method.payment_method_id', '=', 'payment_method.id');

        if (!empty($whitelabel_id)) {
            $query->and_where('whitelabel_payment_method.whitelabel_id', '=', $whitelabel_id);
        }
        if ($casinoOnlyEnabledPaymentMethods) {
            $query->and_where('payment_method.is_enabled_for_casino', '=', 1);
        }
        $query->order_by('whitelabel_payment_method.order');

        return $query->execute();
    }

    /**
     *
     * @param int $whitelabel_id
     * @return array
     */
    public static function get_payment_methods_short_for_crm_whitelabel_id($whitelabel_id)
    {
        $res = [];
        $query = DB::select('id', 'name')->from('whitelabel_payment_method');

        if (!empty($whitelabel_id)) {
            $query->and_where('whitelabel_id', '=', $whitelabel_id);
        }
        $query->order_by('order');

        $res = $query->execute();

        return $res;
    }

    /**
     * This function at this moment is unsed, becuase
     * function which call the function now call other
     * functions but it is left in the case that something
     * will change
     *
     * @param array $whitelabel
     * @param int $whitelabel_payment_method_id
     * @return null|array
     */
    public static function get_payment_currency_for_whitelabel(
        array $whitelabel,
        int $whitelabel_payment_method_id
    ):? array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $res = null;
        $query = "SELECT 
                c.id AS id, 
                c.code, 
                c.rate 
            FROM whitelabel_payment_method wpm 
            INNER JOIN currency c ON wpm.payment_currency_id = c.id
            WHERE 1=1 ";
        
        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $query .= " AND wpm.whitelabel_id = :whitelabel_id ";
        }
        
        if (!empty($whitelabel_payment_method_id)) {
            $query .= " AND wpm.id = :whitelabel_payment_method_id ";
        }

        $query .= " LIMIT 1 ";
        
        try {
            $db = DB::query($query);
            
            if (!empty($whitelabel) && !empty($whitelabel['id'])) {
                $db->param(":whitelabel_id", $whitelabel['id']);
            }
            
            if (!empty($whitelabel_payment_method_id)) {
                $db->param(":whitelabel_payment_method_id", $whitelabel_payment_method_id);
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
    
    /**
     *
     * @param int $whitelabel_payment_method_id
     * @return array
     */
    public static function get_single_by_id(
        int $whitelabel_payment_method_id = null
    ): array {
        $params = [];
        $params[] = [":id", $whitelabel_payment_method_id];
        
        $query_string = "SELECT 
            whitelabel_payment_method.*
        FROM whitelabel_payment_method 
        WHERE 1=1 ";
        
        if (!empty($whitelabel_payment_method_id)) {
            $query_string .= " AND whitelabel_payment_method.id = :id ";
        }
            
        $query_string .= "LIMIT 1";
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result_row($result, [], 0);
    }
}
