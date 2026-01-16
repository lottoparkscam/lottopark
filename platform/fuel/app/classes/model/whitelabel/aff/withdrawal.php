<?php

use Services\Logs\FileLoggerService;

/**
 * @deprecated
 */
class Model_Whitelabel_Aff_Withdrawal extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_aff_withdrawal';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [
        "model_whitelabel_aff_withdrawal.affwithdrawals"
    ];
    
    /**
     *
     * @param array $whitelabel
     * @return null|array
     */
    public static function get_whitelabel_aff_withdrawals(array $whitelabel):? array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $expiredTime = Helpers_Whitelabel::get_expired_time();
        $key = self::$cache_list[0] . '.' . $whitelabel['id'];
        
        // for now join not needed, maybe later
        $query = "SELECT 
            whitelabel_aff_withdrawal.* 
        FROM whitelabel_aff_withdrawal
        -- JOIN withdrawal ON withdrawal.id = whitelabel_withdrawal.withdrawal_id
        WHERE whitelabel_aff_withdrawal.whitelabel_id = :whitelabel
        ORDER BY id";
        
        $db = DB::query($query);
        $db->param(":whitelabel", $whitelabel['id']);
        
        try {
            try {
                $withdrawals = Lotto_Helper::get_cache($key);
            } catch (\CacheNotFoundException $e) {
                $withdrawals = $db->execute()->as_array();
                Lotto_Helper::set_cache($key, $withdrawals, $expiredTime);
            }
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
            $withdrawals = $db->execute()->as_array();
        }
        
        return $withdrawals;
    }
}
