<?php

use Services\Logs\FileLoggerService;

class Model_Lottery_Prize_Data extends \Fuel\Core\Model_Crud
{

    /**
     *
     * @var string
     */
    protected static $_table_name = 'lottery_prize_data';

    /**
     *
     * @var array
     */
    public static $cache_list = [
        "model_lottery_prize_data.drawprizedata"
    ];

    /**
     *
     * @param array $draw
     *
     * @return array
     */
    public static function get_draw_prize_data($draw)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $prizes = null;
        $expiredTime = Helpers_Whitelabel::get_expired_time();
        $key = self::$cache_list[0] . '.' . $draw['lottery_id'] . '.' . $draw['id'];

        $query = "SELECT 
                lottery_prize_data.id, 
                match_n, 
                match_b, 
                winners, 
                prizes, 
                type, 
                additional_data,
                lottery_type_multiplier.multiplier
            FROM lottery_prize_data
            JOIN lottery_type_data ON lottery_type_data.id = lottery_type_data_id
            LEFT JOIN lottery_type_multiplier ON lottery_prize_data.lottery_type_multiplier_id = lottery_type_multiplier.id
            WHERE lottery_draw_id = :draw 
            ORDER BY lottery_prize_data.id, lottery_type_multiplier.multiplier";

        $db = DB::query($query);
        $db->param(":draw", $draw['id']);

        try {
            try {
                $prizes = Lotto_Helper::get_cache($key);
            } catch (\CacheNotFoundException $e) {
                $prizes = $db->execute()->as_array();
                Lotto_Helper::set_cache($key, $prizes, $expiredTime);
            }
        } catch (Exception $e) {
            $fileLoggerService->error($e->getMessage());
            $prizes = $db->execute()->as_array();
        }

        return $prizes;
    }
}
