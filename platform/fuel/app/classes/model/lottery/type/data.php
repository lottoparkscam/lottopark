<?php

use Fuel\Core\DB;
use Helpers\ArrayHelper;
use Services\Logs\FileLoggerService;

class Model_Lottery_Type_Data extends Model_Model
{

    /**
     * @var int
     */
    const JACKPOT = 0;

    /**
     * @var int
     */
    const FIXED = 0;

    /**
     * @var int
     */
    const PARIMUTUEL = 1;

    /**
     *
     * @var string
     */
    protected static $_table_name = 'lottery_type_data';

    /**
     *
     * @var array
     */
    public static $cache_list = [
        "model_lottery_type_data.lotterytypedata"
    ];

    public static function get_lottery_type_data(array $lottery): array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $types = null;
        $expired_time = Helpers_Whitelabel::get_expired_time();
        $key = self::$cache_list[0] . '.' . $lottery['id'];

        $next_value = 0;
        $lottery_closed = Lotto_Helper::is_lottery_closed($lottery);
        if ($lottery_closed) {
            $next_value = 2;
        } else {
            $next_value = 1;
        }

        $lottery_real_next_draw = Lotto_Helper::get_lottery_real_next_draw(
            $lottery,
            $next_value
        );

        $lottery_type = Model_Lottery_Type::get_lottery_type_for_date(
            $lottery,
            $lottery_real_next_draw->format('Y-m-d')
        );

        $query = "SELECT 
            id,
            match_n, 
            match_b, 
            prize, 
            odds, 
            type, 
            estimated, 
            is_jackpot, 
            additional_data,
            slug
        FROM lottery_type_data
        WHERE lottery_type_id = :type 
        ORDER BY id";

        $db = DB::query($query);
        $db->param("type", $lottery_type['id']);

        try {
            try {
                $types = Lotto_Helper::get_cache($key);
            } catch (\CacheNotFoundException $e) {
                $types = $db->execute()->as_array();
                Lotto_Helper::set_cache($key, $types, $expired_time);
            }
        } catch (\Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
            $types = $db->execute()->as_array();
        }

        return $types;
    }

    /**
     * Get last models for lottery type.
     *
     * @param int $lottery_type_id id of the lottery type. NOTE: that it accepts string values as long as they are
     *                             numeric.
     * @param int $count           how many models counting from end should be returned.
     *
     * @return array|null null possible if lottery doesn't have any models yet.
     * @throws \Throwable on sql errors.
     */
    public static function tail_for_lottery_type(int $lottery_type_id, int $count): ?array
    {
        return self::find(
            [
                'where' => [
                    'lottery_type_id' => $lottery_type_id
                ],
                'order_by' => [
                    'id' => 'desc'
                ],
                'limit' => $count,
            ]
        );
    }


    /**
     * @param int $lottery_type_id
     *
     * @return array
     */
    public static function get_keno_prize_breakdown_data(int $lottery_type_id): array
    {
        $db_query = DB::select("match_n", "match_b", "prize")
            ->from('lottery_type_data')
            ->where('lottery_type_id', '=', $lottery_type_id);
        $lottery_type_data = Helpers_Cache::read_or_create($db_query) ?: [];

        $countMinSelected = $lottery_type_data[0]['match_n'];
        $countMaxSelected = 0;

        foreach ($lottery_type_data as $lottery_type_datum) {
            if ($lottery_type_datum['match_n'] > $countMaxSelected) {
                $countMaxSelected = $lottery_type_datum['match_n'];
            }
        }

        $countMaxMatched = $countMaxSelected + 1;
        $countMaxSelected = $countMaxSelected + 1 - $countMinSelected;
        
        $prize_breakdown_data = array_fill(0, $countMaxMatched, array_fill($countMinSelected, $countMaxSelected, ""));

        foreach ($lottery_type_data as $lottery_type_datum) {
            $prize_breakdown_data[$lottery_type_datum['match_b']][$lottery_type_datum['match_n']] = $lottery_type_datum['prize'];
        }
        foreach ($prize_breakdown_data as $key => $prize_breakdown_datum) {
            $prize_breakdown_data[$key] = ArrayHelper::reverse_with_keys($prize_breakdown_datum);
        }

        return ArrayHelper::reverse_with_keys($prize_breakdown_data);
    }

    public static function find_by_lottery_type_id(int $id): ?array
    {
        return self::find(
            [
                'where' => [
                    'lottery_type_id' => $id,
                ],
                'order_by' => 'id'
            ]
        );
    }

    public static function find_by_lottery_type_id_and_slug(int $id, string $slug): ?array
    {
        return self::find(
            [
                'where' => [
                    'lottery_type_id' => $id,
                    'slug' => $slug
                ],
                'order_by' => 'id',
                'limit' => 1
            ]
        );
    }
}
