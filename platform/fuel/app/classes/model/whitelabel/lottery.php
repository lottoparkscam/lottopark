<?php

/**
 *
 */
class Model_Whitelabel_Lottery extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_lottery';

    /**
     *
     * @var array
     */
    public static $cache_list = [];

    public static function find_by_lottery_id(int $id): ?array
    {
        return static::find([
            "where" => [
                "lottery_id" => $id,
                "is_enabled" => 1
            ]
        ]);
    }

    public static function find_for_whitelabel_and_lottery(int $whitelabel_id, int $lottery_id)
    {
        return self::find([
            "where" => [
                "whitelabel_id" => $whitelabel_id,
                "lottery_id" => $lottery_id
            ]
        ]);
    }

    public static function get_last_by_lottery_id(int $lottery_id)
    {
        $result = self::find([
            'where' => [
                'lottery_id' => $lottery_id
            ]
        ]) ?? [];

        return end($result);
    }
}
