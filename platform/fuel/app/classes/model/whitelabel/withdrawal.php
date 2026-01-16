<?php

use Services\Logs\FileLoggerService;

class Model_Whitelabel_Withdrawal extends Model_Model
{
    
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_withdrawal';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [
        "model_whitelabel_withdrawal.whitelabelwithdrawals"
    ];

    /**
     *
     * @param array $whitelabel
     * @return null|array
     */
    public static function get_whitelabel_withdrawals(array $whitelabel):? array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $expiredTime = Helpers_Whitelabel::get_expired_time();
        
        $whitelabel_id = 0;
        if (!empty($whitelabel) &&
            !empty($whitelabel['id']) &&
            !empty($whitelabel['type'])
        ) {
            if (Helpers_Whitelabel::is_V1($whitelabel['type'])) {
                $whitelabel_id = Helpers_General::WHITELABEL_ID_SPECIAL;
            } else {
                $whitelabel_id = $whitelabel['id'];
            }
        }
        
        $key = self::$cache_list[0] . '.' . $whitelabel_id;
        
        $query = "SELECT 
            whitelabel_withdrawal.*, 
            withdrawal.name 
        FROM whitelabel_withdrawal
        LEFT JOIN withdrawal ON withdrawal.id = whitelabel_withdrawal.withdrawal_id
        WHERE whitelabel_withdrawal.whitelabel_id = :whitelabel_id
        ORDER BY whitelabel_withdrawal.id";
        
        $db = DB::query($query);
        $db->param(":whitelabel_id", $whitelabel_id);
        
        try {
            try {
                $withdrawals = Lotto_Helper::get_cache($key);
            } catch (\CacheNotFoundException $e) {
                /** @var object $db */
                $withdrawals = $db->execute()->as_array();
                Lotto_Helper::set_cache($key, $withdrawals, $expiredTime);
            }
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
            /** @var object $db */
            $withdrawals = $db->execute()->as_array();
        }
        
        return $withdrawals;
    }
}
