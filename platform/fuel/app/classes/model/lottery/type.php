<?php

use Carbon\Carbon;
use Fuel\Core\DB;
use Helpers\ArrayHelper;
use Services\Logs\FileLoggerService;

class Model_Lottery_Type extends Model_Model
{
    use Model_Traits_Last_For_Lottery;

    /**
     *
     * @var string
     */
    protected static $_table_name = 'lottery_type';

    /**
     *
     * @var array
     */
    public static $cache_list = [
        "lottery_type.lotterytypefordate"
    ];

    /**
     *
     * @param array  $lottery
     * @param string $date String represented date
     *
     * @return null|array
     */
    public static function get_lottery_type_for_date(
        array $lottery,
        string $date
    ): ?array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $type = null;
        $expired_time = Helpers_Whitelabel::get_expired_time();
        $key = self::$cache_list[0] . '.' . $lottery['id'] . '.' . $date;

        $query = "SELECT 
            * 
        FROM lottery_type 
        WHERE lottery_id = :lottery 
            AND (date_start <= :date 
                OR date_start IS NULL) 
        ORDER BY date_start DESC 
        LIMIT 1";

        $db = DB::query($query);
        $db->param(":lottery", $lottery['id']);
        $db->param(":date", $date);

        try {
            try {
                $type = Lotto_Helper::get_cache($key);
            } catch (\CacheNotFoundException $e) {
                $type = $db->execute()->as_array();
                $type = $type[0];
                Lotto_Helper::set_cache($key, $type, $expired_time);
            }
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
            $type = $db->execute()->as_array();
            $type = $type[0];
        }

        return $type;
    }

    /**
     * @param Carbon $date_min
     * @param Carbon $date_max
     * @param int    $lottery_id
     *
     * @return array
     */
    public static function between_dates_for_lottery(Carbon $date_min, Carbon $date_max, int $lottery_id): array
    {
        $db = DB::select('*')
            ->from('lottery_type')
            ->where('lottery_id', '=', $lottery_id)
            ->and_where_open()
                ->where('date_start', '=', $date_max)
                ->or_where(DB::expr('date_start IS NULL'))
            ->and_where_close()
            ->and_where_open()
                ->where('date_end', '=', $date_min)
                ->or_where(DB::expr('date_end IS NULL'))
            ->and_where_close()
            ->order_by('date_start', 'DESC');

        return $db->execute()->as_array();
    }

    /**
     * @param array $draws
     * @param int   $lottery_id
     *
     * @return array
     */
    public static function get_lottery_types(array $draws, int $lottery_id): array
    {
        // We assume that draws are sorted from the newest to the oldest
        $date_min = Carbon::parse(ArrayHelper::last($draws)['date']);
        $date_max = Carbon::parse(ArrayHelper::first($draws)['date']);

        return self::between_dates_for_lottery($date_min, $date_max, $lottery_id);
    }
}
