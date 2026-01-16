<?php

use Fuel\Core\Database_Query_Builder_Select;
use Fuel\Core\DB;
use Models\LotteryDraw;
use Services\Logs\FileLoggerService;

/**
 * @property string $date_local
 * @property string $id
 * @property string $lottery_type_id
 * @property-read int $lottery_id
 */
class Model_Lottery_Draw extends \Fuel\Core\Model_Crud
{
    use Model_Traits_Last_For_Lottery,
        Model_Traits_Set_Jackpot,
        Model_Traits_Mutate_Numbers,
        Model_Traits_Get_Lottery;

    /**
     *
     * @var string
     */
    protected static $_table_name = 'lottery_draw';

    /**
     *
     * @var array
     */
    public static $cache_list = [
        'model_lottery_draw.drawlistbylotteryid',
        'model_lottery_draw.drawbydate'
    ];

    /**
     *
     * @param array    $lottery
     * @param DateTime $date
     *
     * @return array
     */
    public static function get_draw_by_date($lottery, $date)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $draw = null;
        $expiredTime = Helpers_Whitelabel::get_expired_time();
        $key = self::$cache_list[1] . '.' . $lottery['id'] . '.' . trim($date);

        $query = "SELECT 
                id, 
                lottery_id,
      		    draw_no, 
                jackpot, 
                numbers, 
                bnumbers, 
                total_prize, 
                total_winners, 
                final_jackpot, 
                additional_data 
            FROM lottery_draw 
            WHERE lottery_id = :lottery 
            AND date_local = :date
            ORDER BY date_local DESC
            LIMIT 1";

        $db = DB::query($query);
        $db->param(":lottery", $lottery['id']);
        $db->param(":date", $date);

        try {
            try {
                $draw = Lotto_Helper::get_cache($key);
            } catch (\CacheNotFoundException $e) {
                /** @var object $db */
                $draw = $db->execute()->as_array();
                $draw = $draw[0];
                Lotto_Helper::set_cache($key, $draw, $expiredTime);
            }
        } catch (Exception $e) {
            $fileLoggerService->error($e->getMessage());
            /** @var object $db */
            $draw = $db->execute()->as_array();
            $draw = $draw[0];
        }

        return $draw;
    }

    /**
     *
     * @param array $lottery
     * @param string $currentDrawDate
     *
     * @return array
     */
    public static function get_draw_list_by_lottery(array $lottery, ?string $currentDrawDate = null)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $draws = null;
        $expiredTime = Helpers_Whitelabel::get_expired_time();

        if (empty($currentDrawDate)) {
        	$lastLotteryDraw = LotteryDraw::find('first', ['date_local', 'DESC']);
            /** @var object $lastLotteryDraw  */
        	$currentDrawDate = $lastLotteryDraw->dateLocal->format(Helpers_Time::DATE_FORMAT);
		}

        $key =  self::$cache_list[0] . '.' . $lottery['id'] . '.' . $currentDrawDate;

		$query = "SELECT * FROM (
						SELECT max(date_local) as date_local 
						FROM lottery_draw 
						WHERE lottery_id = :lottery
						AND date(date_local) <> :current_draw_date
						GROUP BY date(date_local) 
						UNION 
						SELECT date_local 
						FROM lottery_draw 
						WHERE lottery_id = :lottery
						AND date(date_local) = :current_draw_date
					) d ORDER BY date_local DESC
			";

        $db = DB::query($query);
        $db->param(":lottery", $lottery['id']);
		$db->param(":current_draw_date", $currentDrawDate);

        try {
            try {
                $draws = Lotto_Helper::get_cache($key);
            } catch (\CacheNotFoundException $e) {
                /** @var object $db */
                $draws = $db->execute()->as_array();
                Lotto_Helper::set_cache($key, $draws, $expiredTime);
            }
        } catch (Exception $e) {
            $fileLoggerService->error($e->getMessage());

            /** @var object $db */
            $draws = $db->execute()->as_array();
        }

        return $draws;
    }

    // Below override works as pseudo observer save event, we could even make it into true observer.
    /**
     * @param bool $validate
     * 
     * @return array|int
     */
    public function save($validate = true)
    {
        // mutate numbers if they are in array form.
        // NOTE: done this way, because should have lesser impact on performance (in comparison to magic __set)
        $this->mutate_numbers();

        return parent::save($validate);
    }

    /**
     * Get prizes for draw.
     *
     * @return Model_Lottery_Prize_Data[]|null null if not found, array of prize models otherwise.
     * @throws \Exception on database errors.
     */
    public function prizes(): ?array
    {
        if ($this->get_lottery()->is_keno()) {
            return $this->static_prizes();
        }

        return Model_Lottery_Prize_Data::find_by('lottery_draw_id', $this->id);
    }

    /**
     * Get prizes for draw joined with type data.
     *
     * @param array $columns use lpd (lottery prize data), ltd (lottery type data) prefixes for non unique fields
     *
     * @param bool $by_lottery_type determines whether we should get prizes by lottery type
     *                              (true for lotteries with static prizes)
     *
     * @return array|null null if not found, array of prize models otherwise.
     */
    public function prizes_with_type_data(array $columns = ['*'], bool $by_lottery_type = false): ?array
    {
        $query = $this->get_prizes_with_type_data_query($columns);
        if ($by_lottery_type) {

            /** @var object $query */
            return $query->where('ltd.lottery_type_id', '=', $this->lottery_type_id)
                ->execute()
                ->as_array();
        }

        /** @var object $query */
        return $query->where('lottery_draw_id', '=', $this->id)
            ->execute()
            ->as_array();
    }

    private function get_prizes_with_type_data_query(array $columns = ['*']): Database_Query_Builder_Select
    {
        return DB::select_array($columns)
            ->from(['lottery_prize_data', 'lpd'])
            ->join(['lottery_type_data', 'ltd'], 'LEFT')
            ->on('lottery_type_data_id', '=', 'ltd.id');
    }

    /**
     * Some lotteries (eg. Keno) may have prizes which are generated only once (and not for each new draw)
     *
     * @return array|null
     */
    private function static_prizes(): ?array
    {
        $lottery_type_data_ids = [];
        foreach (Model_Lottery_Type_Data::find_by('lottery_type_id', $this->lottery_type_id) as $type_data) {
            array_push($lottery_type_data_ids, $type_data['id']);
        }

        return Model_Lottery_Prize_Data::find([
            'where' => [['lottery_type_data_id', 'in', $lottery_type_data_ids]]
        ]);
    }

}
