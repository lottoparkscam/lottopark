<?php

use Carbon\Carbon;
use Services\Logs\FileLoggerService;

class Model_Lottery_Delay extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'lottery_delay';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [
        'model_lottery_delay.lotterydelay'
    ];
    
    /**
     *
     * @param array           $lottery
     * @param DateTime|Carbon $date
     *
     * @return array
     */
    public static function get_delay_for_lottery_date($lottery, $date)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $delay = null;
        $expired_time = Helpers_Whitelabel::get_expired_time();
        $key = self::$cache_list[0].'.'.$lottery['id'].'.'.$date->format("YmdHis");
        
        $query = "SELECT 
            date_delay 
        FROM lottery_delay 
        WHERE lottery_id = :lottery 
            AND date_local = :date 
        LIMIT 1";
        
        $db = DB::query($query);
        $db->param(":lottery", $lottery['id']);
        $db->param(":date", $date->format(Helpers_Time::DATETIME_FORMAT));
        
        try {
            try {
                $delay = Lotto_Helper::get_cache($key);
            } catch (\CacheNotFoundException $e) {
                /** @var object $db */
                $delay = $db->execute()->as_array();
                Lotto_Helper::set_cache($key, $delay, $expired_time);
            }
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
            $delay = $db->execute()->as_array();
        }
        
        return $delay;
    }
}
