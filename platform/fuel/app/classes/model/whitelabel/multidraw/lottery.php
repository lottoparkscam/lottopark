<?php

/**
 *
 */
class Model_Whitelabel_Multidraw_Lottery extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_multi_draw_lottery';

    /**
     *
     * @var array
     */
    public static $cache_list = [];

    /**
     * Get whitelabel lotteries
     * @param int $whitelabel_id
     * @return mixed
     */
    public static function get_whitelabel_lotteries(int $whitelabel_id)
    {
        $enabled_lotteries = self::find_by('whitelabel_id', $whitelabel_id);

        if (!is_array($enabled_lotteries)) {
            return [];
        }

        return $enabled_lotteries;
    }

    /**
     * Get whitelabel lotteries with sorted array
     *
     * @param int $whitelabel_id
     * @return array
     */
    public static function get_whitelabel_lotteries_id(int $whitelabel_id): array
    {
        $lotteries = self::get_whitelabel_lotteries($whitelabel_id);
        $return = [];

        foreach ($lotteries as $lottery) {
            array_push($return, $lottery['lottery_id']);
        }

        return $return;
    }

    /**
     *
     * @param int $whitelabel_id
     * @return bool
     */
    public static function clear_lotteries(int $whitelabel_id): bool
    {
        $lotteries = self::find_by('whitelabel_id', $whitelabel_id);

        if (!is_array($lotteries)) {
            return false;
        }

        foreach ($lotteries as $lottery) {
            $lottery->delete();
        }
        
        return true;
    }

    /**
     *
     * @param int $whitelabel_id
     * @param array $lotteries
     * @return void
     */
    public static function update_lotteries(
        int $whitelabel_id,
        array $lotteries
    ): void {
        foreach ($lotteries as $id => $row) {
            /** @var mixed $lottery */
            $lottery = self::forge();
            $lottery->whitelabel_id = $whitelabel_id;
            $lottery->lottery_id = $row;

            $lottery->save();
        }
    }
}
