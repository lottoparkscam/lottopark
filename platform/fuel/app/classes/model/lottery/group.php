<?php

use Services\Logs\FileLoggerService;

class Model_Lottery_Group extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'lottery_group';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [
        'model_lottery_group.lotterygroup'
    ];
    
    /**
     *
     * @param int $group_id
     * @return array
     */
    public static function get_lotteries_for_group($group_id)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $lotteries = null;
        $expiredTime = Helpers_Whitelabel::get_expired_time();
        $key = self::$cache_list[0].'.'.$group_id;
        
        $query = "SELECT 
                * 
            FROM lottery_group lg
            LEFT JOIN lottery l ON l.id = lg.lottery_id
            WHERE group_id = :group
            AND l.is_enabled = 1
            AND l.is_temporarily_disabled = 0
            ORDER BY lg.id";
        
        $db = DB::query($query);
        $db->param(":group", $group_id);
        
        try {
            try {
                $lotteries = Lotto_Helper::get_cache($key);
            } catch (\CacheNotFoundException $e) {
                $lotteries = $db->execute()->as_array();
                Lotto_Helper::set_cache($key, $lotteries, $expiredTime);
            }
        } catch (Exception $e) {
            $fileLoggerService->error($e->getMessage());
            $lotteries = $db->execute()->as_array();
        }
        
        return $lotteries;
    }
}
