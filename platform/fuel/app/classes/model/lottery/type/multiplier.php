<?php

use Fuel\Core\DB;

class Model_Lottery_Type_Multiplier extends Model_Model
{
    use Model_Traits_For_Lottery;

    /**
     * @var string
     */
    protected static $_table_name = 'lottery_type_multiplier';

    /**
     * @var array
     */
    public static $cache_list = [];

    /**
     * @param array $multipliers
     * @param int   $multiplier_key
     * @param array $grouped
     * @param array $multiplier
     */
    private static function group_multiplier(array $multipliers, int $multiplier_key, array &$grouped, array $multiplier): void
    {
        if (isset($grouped[$multiplier['lottery_id']]) === false) {
            $grouped[$multiplier['lottery_id']] = [
                $multiplier['multiplier'] => $multipliers[$multiplier_key]
            ];

            return;
        }
        $grouped[$multiplier['lottery_id']][$multiplier['multiplier']] = $multipliers[$multiplier_key];
    }

    /**
     * @return array
     */
    public static function for_ticket_saving(): array
    {
        $db = DB::select('*')
            ->from(self::$_table_name)
            ->order_by('id');
        $multipliers = Helpers_Cache::read_or_create($db);
        $grouped = [];
        foreach ($multipliers as $multiplier_key => $multiplier) {
            self::group_multiplier($multipliers, $multiplier_key, $grouped, $multiplier);
        }

        return $grouped;
    }

    /**
     * @param int $lottery_id
     *
     * @return array|null
     */
    public static function for_lottery(int $lottery_id): ?array
    {
        $db = DB::select('*')
            ->from(self::$_table_name)
            ->where('lottery_id', '=', $lottery_id)
            ->order_by('id');

        return Helpers_Cache::read_or_create($db);
    }

    /**
     * @param int $lottery_id
     *
     * @return mixed
     */
    public static function min_max_for_lottery(int $lottery_id)
    {
        $db = DB::select_array([
            [DB::expr('min(multiplier)'), 'min'],
            [DB::expr('max(multiplier)'), 'max'],
        ])->from(self::$_table_name)
            ->where('lottery_id', '=', $lottery_id)
            ->order_by('id');

        return Helpers_Cache::read_or_create($db)[0];
    }

}