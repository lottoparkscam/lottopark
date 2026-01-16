<?php

use Carbon\Carbon;
use Fuel\Core\Database_Query;
use Fuel\Core\DB;
use Models\Lottery;
use Services\Logs\FileLoggerService;

/**
 * @property int $id
 * @property string $price decimal (5,2) price of the line
 * @property string $slug
 * @property string $next_date_local date of the next draw in yy-mm-dd hh:mm:ss format.
 * @property string $timezone varchar(40) DateTimezone timezone.
 * @property mixed|string $last_update
 * @property bool $is_enabled
 * @property string $type
 * @property string|null $last_date_local
 * @property-read int $current_jackpot
 * @property-read int $currency_id 
 * @method static Model_Lottery|null find_one_by_slug(string $slug)
 * @deprecated
 */
class Model_Lottery extends \Model_Model
{
    use Model_Traits_Set_Jackpot,
        Model_Traits_Mutate_Numbers;

    protected static $_table_name = 'lottery';

    const LOTTERY_CLASSES = [
        'powerball' => 'Lotto_Lotteries_Powerball',
        'mega-millions' => 'Lotto_Lotteries_MegaMillions',
        'eurojackpot' => 'Lotto_Lotteries_Eurojackpot',
        'superenalotto' => 'Lotto_Lotteries_SuperEnalotto',
        'lotto-uk' => 'Lotto_Lotteries_UKLottery',
        'euromillions' => 'Lotto_Lotteries_Euromilions',
        'lotto-pl' => 'Lotto_Lotteries_LottoPL',
        'la-primitiva' => 'Lotto_Lotteries_LaPrimitiva',
        'bonoloto' => 'Lotto_Lotteries_Bonoloto',
        'oz-lotto' => 'Lotto_Lotteries_OzLotto',
        'powerball-au' => 'Lotto_Lotteries_PowerballAU',
        'saturday-lotto-au' => 'Lotto_Lotteries_SaturdayLottoAU',
        'monday-wednesday-lotto-au' => 'Lotto_Lotteries_MondayWednesdayLottoAU',
        'el-gordo-primitiva' => 'Lotto_Lotteries_ElGordo',
        'lotto-fr' => 'Lotto_Lotteries_LottoFR',
        'florida-lotto' => 'Lotto_Lotteries_LottoFL',
        'mega-sena' => 'Lotto_Lotteries_MegaSena',
        'quina' => 'Lotto_Lotteries_Quina',
        'otoslotto' => 'Lotto_Lotteries_OtosLotto',
        'hatoslotto' => 'Lotto_Lotteries_HatosLotto',
        'set-for-life-uk' => 'Lotto_Lotteries_SetForLifeUK',
        'thunderball' => 'Lotto_Lotteries_Thunderball',
        'lotto-america' => 'Lotto_Lotteries_LottoAmerica',
        'lotto-at' => 'Lotto_Lotteries_LottoAT',
        'lotto-6aus49' => 'Lotto_Lotteries_Lotto6Aus49',
        Lottery::EURODREAMS_SLUG => 'Lotto_Lotteries_EuroDreams',
        Lottery::WEEKDAY_WINDFALL_SLUG => 'Lotto_Lotteries_WeekdayWindfall',
        Lottery::EUROMILLIONS_SUPERDRAW_SLUG => 'Lotto_Lotteries_EuromillionsSuperdraw',
        Lottery::LOTO_6_49_SLUG => 'Lotto_Lotteries_LoteriaRomana',
        Lottery::MINI_POWERBALL_SLUG => 'Lotto_Lotteries_MiniPowerball',
        Lottery::MINI_MEGA_MILLIONS_SLUG => 'Lotto_Lotteries_MiniMegaMillions',
        Lottery::MINI_EUROMILLIONS_SLUG => 'Lotto_Lotteries_MiniEuromillions',
        Lottery::MINI_EUROJACKPOT_SLUG => 'Lotto_Lotteries_MiniEurojackpot',
        Lottery::MINI_SUPERENALOTTO_SLUG => 'Lotto_Lotteries_MiniSuperEnalotto',
    ];

    const LOTTERY_CLASSES_KENO = [
        Lottery::POLISH_KENO_SLUG => 'Lotto_Lotteries_PolishKeno',
        Lottery::GREEK_KENO_SLUG => 'Lotto_Lotteries_GreekKeno',
        Lottery::CZECH_KENO_SLUG => 'Lotto_Lotteries_CzechKeno',
        Lottery::SLOVAK_KENO_SLUG => 'Lotto_Lotteries_SlovakKeno',
        Lottery::LATVIAN_KENO_SLUG => 'Lotto_Lotteries_LatvianKeno',
        Lottery::FINNISH_KENO_SLUG => 'Lotto_Lotteries_FinnishKeno',
        Lottery::FRENCH_KENO_SLUG => 'Lotto_Lotteries_FrenchKeno',
        Lottery::HUNGARIAN_KENO_SLUG => 'Lotto_Lotteries_HungarianKeno',
        Lottery::ITALIAN_KENO_SLUG => 'Lotto_Lotteries_ItalianKeno',
        Lottery::SLOVAK_KENO_10_SLUG => 'Lotto_Lotteries_SlovakKeno10',
        Lottery::GERMAN_KENO_SLUG => 'Lotto_Lotteries_GermanKeno',
        Lottery::UKRAINIAN_KENO_SLUG => 'Lotto_Lotteries_UkrainianKeno',
        Lottery::BELGIAN_KENO_SLUG => 'Lotto_Lotteries_BelgianKeno',
        Lottery::KENO_NEW_YORK_SLUG => 'Lotto_Lotteries_KenoNewYork',
        Lottery::BRAZILIAN_KENO_SLUG => 'Lotto_Lotteries_BrazilianKeno',
        Lottery::SWEDISH_KENO_SLUG => 'Lotto_Lotteries_SwedishKeno',
        // Lottery::AUSTRALIAN_KENO_SLUG => 'Lotto_Lotteries_AustralianKeno',
        Lottery::DANISH_KENO_SLUG => 'Lotto_Lotteries_DanishKeno',
        Lottery::NORWEGIAN_KENO_SLUG => 'Lotto_Lotteries_NorwegianKeno',
        // Lottery::LITHUANIAN_KENO_SLUG => 'Lotto_Lotteries_LithuanianKeno',
        // Lottery::CROATIAN_KENO_SLUG => 'Lotto_Lotteries_CroatianKeno',
        // Lottery::BELARUSIAN_KENO_SLUG => 'Lotto_Lotteries_BelarusianKeno',
        // Lottery::ESTONIAN_KENO_SLUG => 'Lotto_Lotteries_EstonianKeno',
        // Lottery::CANADIAN_KENO_SLUG => 'Lotto_Lotteries_CanadianKeno',
    ];

    public static $cache_list = [
        "model_lottery.lotteriesorderbyid",
        "model_lottery.lotteriesforwl",
        "model_lottery.lotteriesall",
        "model_lottery.lotteriesallenabled",
        "model_lottery.lotteriesallforwl",
        "model_lottery.lotteriesreallyallforwl"
    ];

    public static function get_lotteries_order_by_id(): array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $expiredTime = Helpers_Whitelabel::get_expired_time();
        $key = self::$cache_list[0];

        $query = "SELECT 
                lottery.*, currency.code AS currency, fc.code AS force_currency
            FROM lottery 
            JOIN currency ON currency.id = lottery.currency_id 
            LEFT JOIN currency fc ON fc.id = lottery.force_currency_id 
            ORDER BY id";

        $db = DB::query($query);

        try {
            try {
                $lotteries = Lotto_Helper::get_cache($key);
            } catch (\CacheNotFoundException $e) {
                /** @var object $db */
                $lotteries = $db->execute()->as_array();
                Lotto_Helper::set_cache($key, $lotteries, $expiredTime);
            }
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
            /** @var object $db */
            $lotteries = $db->execute()->as_array();
        }

        return $lotteries;
    }

    private static function prepare_lotteries(array $lotteries): array
    {
        $by_slug = [];
        $by_id = [];
        $by_last_date = [];
        $by_next_date = [];

        foreach ($lotteries as $key => $lottery) {
            $lotteries[$key]['supports_ticket_multipliers'] = Helpers_Lottery::supports_ticket_multipliers($lottery);
            $by_slug[$lottery['slug']] = $lottery;
            $by_id[$lottery['id']] = $lottery;
            $by_last_date[] = $lottery;
            $by_next_date[] = $lottery;
        }

        usort($by_last_date, ["Helpers_Lottery", "sort_lotteries_by_last_date"]);
        usort($by_next_date, ["Helpers_Lottery", "sort_lotteries_by_next_date"]);

        $lotteries['__by_slug'] = $by_slug;
        $lotteries['__by_id'] = $by_id;
        $lotteries['__sort_lastdate'] = $by_last_date;
        $lotteries['__sort_nextdate'] = $by_next_date;

        return $lotteries;
    }

    /**
     *
     * @return array
     */
    public static function get_all_lotteries(): array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $expiredTime = Helpers_Whitelabel::get_expired_time();
        $key = self::$cache_list[2];

        $query = "SELECT 
                lottery.*, 
                fc.code AS force_currency, 
                currency.code AS currency 
            FROM lottery
            JOIN currency ON currency.id = lottery.currency_id 
            LEFT JOIN currency fc ON fc.id = lottery.force_currency_id 
            ORDER BY lottery.id";

        $db = DB::query($query);

        try {
            try {
                $lotteries = Lotto_Helper::get_cache($key);
            } catch (\CacheNotFoundException $e) {
                /** @var object $db */
                $lotteries = self::prepare_lotteries($db->execute()->as_array());
                Lotto_Helper::set_cache($key, $lotteries, $expiredTime);
            }
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
            /** @var object $db */
            $lotteries = self::prepare_lotteries($db->execute()->as_array());
        }

        return $lotteries;
    }

    /**
     *
     * @return array
     */
    public static function get_all_enabled_lotteries(): array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $expiredTime = Helpers_Whitelabel::get_expired_time();
        $key = self::$cache_list[3];

        $query = "SELECT 
                lottery.*, 
                fc.code AS force_currency, 
                currency.code AS currency 
            FROM lottery
            JOIN currency ON currency.id = lottery.currency_id 
            LEFT JOIN currency fc ON fc.id = lottery.force_currency_id 
            WHERE is_enabled = 1
            ORDER BY lottery.id";

        $db = DB::query($query);

        try {
            try {
                $lotteries = Lotto_Helper::get_cache($key);
            } catch (\CacheNotFoundException $e) {
                /** @var object $db */
                $lotteries = self::prepare_lotteries($db->execute()->as_array());
                Lotto_Helper::set_cache($key, $lotteries, $expiredTime);
            }
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
            /** @var object $db */
            $lotteries = self::prepare_lotteries($db->execute()->as_array());
        }

        return $lotteries;
    }

    private static function fetchLotteries(string $key, Database_Query $databaseQuery): array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        try {
            try {
                $lotteries = Lotto_Helper::get_cache($key);
            } catch (\CacheNotFoundException $e) {
                /** @var object $databaseQuery */
                $lotteries = $databaseQuery->execute()->as_array();
                Lotto_Helper::set_cache($key, $lotteries, Helpers_Whitelabel::get_expired_time());
            }
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
            $lotteries = $databaseQuery->execute()->as_array();
        }
        return self::prepare_lotteries($lotteries);
    }

    /* TODO: lol of name, need to change the name to getAllLotteriesForWl and the next one to getEnabledLotteriesForWl */

    /**
     * Function returns all lotteries, even those which are not enabled
     * with all needed data from other tables than lottery
     *
     * @param array $whitelabel
     * @return array
     */
    public static function get_really_all_lotteries_for_whitelabel($whitelabel): array
    {
        $key = self::$cache_list[5];
        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $key .= '.' . $whitelabel['id'];
        }

        $query = "SELECT 
            lottery.*, 
            currency.code AS currency, 
            fc.code AS force_currency, 
            wl.is_enabled AS wis_enabled, 
            wl.is_multidraw_enabled AS multidraws_enabled, 
            wl.id AS wid, 
            wl.ltech_lock, 
            wl.model, 
            wl.income, 
            wl.income_type, 
            wl.tier, 
            wl.volume, 
            wl.min_lines, 
            wl.should_decrease_prepaid, 
            lottery_provider.id AS lp_id, 
            provider, 
            fee, 
            min_bets, 
            max_bets, 
            multiplier, 
            closing_time, 
            closing_times, 
            lottery_provider.timezone AS lp_timezone, 
            lottery_provider.offset AS lp_offset, 
            lottery_provider.data 
        FROM lottery
        JOIN whitelabel_lottery wl ON wl.lottery_id = lottery.id
        JOIN currency ON currency.id = lottery.currency_id
        LEFT JOIN currency fc ON fc.id = lottery.force_currency_id 
        JOIN lottery_provider ON lottery_provider.id = wl.lottery_provider_id
        WHERE 1=1 ";

        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $query .= " AND wl.whitelabel_id = :whitelabel";
        }

        $query .= " ORDER BY lottery.id";

        $db = DB::query($query);
        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $db->param(":whitelabel", $whitelabel['id']);
        }

        return self::fetchLotteries($key, $db);
    }

    /**
     * Function returns all enabled lotteries with added all needed data from
     * other tables than lottery
     *
     * @param array $whitelabel
     * @return array
     */
    public static function get_all_lotteries_for_whitelabel($whitelabel): array
    {
        $key = self::$cache_list[4];
        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $key .= '.' . $whitelabel['id'];
        }

        $query = "SELECT 
            lottery.*, 
            currency.code AS currency, 
            fc.code AS force_currency, 
            wl.is_enabled AS wis_enabled, 
            wl.ltech_lock, 
            wl.is_multidraw_enabled AS multidraws_enabled, 
            wl.model, 
            wl.income, 
            wl.income_type, 
            wl.tier,
            wl.volume, 
            wl.min_lines, 
            wl.is_bonus_balance_in_use AS wis_bonus_balance_in_use, 
            wl.bonus_balance_purchase_limit_per_user,
            lottery_provider.id AS lp_id, 
            provider,  
            fee, 
            min_bets, 
            max_bets, 
            multiplier, 
            closing_time, 
            closing_times, 
            lottery_provider.timezone AS lp_timezone, 
            lottery_provider.offset AS lp_offset, 
            lottery_provider.data 
        FROM lottery
        JOIN whitelabel_lottery wl ON wl.lottery_id = lottery.id
        JOIN currency ON currency.id = lottery.currency_id 
        LEFT JOIN currency fc ON fc.id = lottery.force_currency_id 
        JOIN lottery_provider ON lottery_provider.id = wl.lottery_provider_id
        WHERE 1=1 ";

        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $query .= " AND wl.whitelabel_id = :whitelabel";
        }

        $query .= " AND lottery.is_enabled = 1
            ORDER BY lottery.id";

        $db = DB::query($query);

        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $db->param(":whitelabel", $whitelabel['id']);
        }

        return self::fetchLotteries($key, $db);
    }

    /**
     *
     * @param array $whitelabel
     * @return array
     */
    public static function get_lotteries_for_whitelabel(array $whitelabel): ?array
    {
        if (empty($whitelabel)) {
            return [];
        }

        $key = self::$cache_list[1];
        if (!empty($whitelabel['id'])) {
            $key .= '.' . $whitelabel['id'];
        }

        $query = "SELECT 
            lottery.*, 
            currency.code AS currency,
            fc.code AS force_currency, 
            wl.ltech_lock, 
            wl.model, 
            wl.income,
            wl.minimum_expected_income,
            wl.income_type, 
            wl.tier, 
            wl.volume, 
            wl.min_lines,
            wl.is_multidraw_enabled as multidraws_enabled, 
            wl.is_bonus_balance_in_use, 
            provider, 
            fee, 
            min_bets, 
            max_bets, 
            multiplier, 
            closing_time, 
            closing_times, 
            lottery_provider.timezone AS lp_timezone, 
            lottery_provider.offset AS lp_offset, 
            lottery_provider.data,
            lg.group_id 
        FROM lottery
        JOIN whitelabel_lottery wl ON wl.lottery_id = lottery.id
        JOIN currency ON currency.id = lottery.currency_id 
        LEFT JOIN currency fc ON fc.id = lottery.force_currency_id
        LEFT JOIN lottery_group lg ON lg.lottery_id = lottery.id
        JOIN lottery_provider ON lottery_provider.id = wl.lottery_provider_id
        WHERE 1=1 ";

        if (!empty($whitelabel['id'])) {
            $query .= " AND wl.whitelabel_id = :whitelabel ";
        }

        $query .= " AND lottery.is_enabled = 1 
            AND wl.is_enabled = 1
            ORDER BY lottery.id";

        $db = DB::query($query);

        if (!empty($whitelabel['id'])) {
            $db->param(":whitelabel", $whitelabel['id']);
        }

        return self::fetchLotteries($key, $db);
    }

    // Below override works as pseudo observer save event, we could even make it into true observer.
    /**
     * @param boolean $validate
     * @return void
     */
    public function save($validate = true)
    {
        // before every save set last_update field
        $this->last_update = Helpers_Time::now();
        // mutate numbers if they are in array form.
        // NOTE: done this way, because should have lesser impact on performance (in comparison to magic __set)
        $this->mutate_numbers('last_');

        parent::save($validate);
    }

    /**
     * Set jackpot usd value.
     *
     * @param string $value value in normal decimal notation
     * @return self
     */
    public function set_current_jackpot_usd(string $value): self
    {
        $this->set_jackpot($value, 'current_', '_usd');

        return $this;
    }

    /**
     * Set jackpot value.
     *
     * @param string $value value in normal decimal notation
     * @return self
     */
    public function set_current_jackpot(string $value): self
    {
        $this->set_jackpot($value, 'current_');

        return $this;
    }

    /**
     *
     * @param int $whitelabel_id
     * @return array
     */
    public static function get_all_lotteries_for_whitelabel_short(int $whitelabel_id): array
    {
        $params = [];
        $params[] = [
            ":whitelabel_id", $whitelabel_id
        ];

        $query_string = "SELECT 
            l.id,
            l.name
        FROM whitelabel_lottery wl 
        INNER JOIN lottery l 
            ON wl.lottery_id = l.id 
        WHERE wl.whitelabel_id = :whitelabel_id  
            AND l.is_enabled = 1
            AND l.is_temporarily_disabled = 0
            AND wl.is_enabled = 1
        ORDER BY l.id";

        // execute safe query
        $result = parent::execute_query($query_string, $params);

        // safely retrieve value
        return parent::get_array_result($result, []);
    }

    /**
     * Get single row of lottery based on lottery ID
     *
     * @param int $lottery_id
     * @return array|null
     */
    public static function get_single_row_by_id(int $lottery_id): ?array
    {
        // add non global params
        $params = [];
        $params[] = [":lottery_id", $lottery_id];

        $query_string = "SELECT 
            lottery.*, 
            currency.code AS currency, 
            fc.code AS force_currency, 
            wl.is_enabled AS wis_enabled, 
            wl.is_multidraw_enabled AS multidraws_enabled, 
            wl.id AS wid, 
            wl.model, 
            wl.income, 
            wl.income_type, 
            wl.tier, 
            wl.volume, 
            wl.min_lines, 
            lottery_provider.id AS lp_id, 
            provider, 
            fee, 
            min_bets, 
            max_bets, 
            multiplier, 
            closing_time, 
            closing_times, 
            lottery_provider.timezone AS lp_timezone, 
            lottery_provider.offset AS lp_offset, 
            lottery_provider.data 
        FROM lottery
        JOIN whitelabel_lottery wl ON wl.lottery_id = lottery.id
        JOIN currency ON currency.id = lottery.currency_id
        LEFT JOIN currency fc ON fc.id = lottery.force_currency_id 
        JOIN lottery_provider ON lottery_provider.id = wl.lottery_provider_id 
        WHERE lottery.id = :lottery_id 
        LIMIT 1";

        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result_row($result, [], 0);
    }

    /**
     * @return boolean true if lottery is disabled.
     */
    public function is_disabled(): bool
    {
        return !$this->is_enabled;
    }

    /**
     * @param string $slug
     * @return array
     * @throws \Throwable database failures and record not found
     */
    public static function for_slug_with_provider(string $slug): array
    {
        return DB::select_array([
            'lottery.*',
            'lp.closing_time',
            'lp.closing_times',
            ['lp.timezone', 'lp_timezone'],
            ['lp.offset', 'lp_offset'],
            ['lp.id', 'lottery_provider_id'],
        ])
            ->from(self::$_table_name)
            ->join(['lottery_provider', 'lp'], 'LEFT')
            ->on('lottery.id', '=', 'lp.lottery_id')
            ->where('slug', '=', $slug)
            ->execute()
            ->as_array()[0];
    }

    /**
     *
     * @param Exception $e
     * @param array $lottery
     * @return bool
     */
    public static function lottery_error($e, $lottery)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $add = '';
        if (!empty($e->getMessage())) {
            $add = ' [' . $e->getMessage() . ']';
        }
        $fileLoggerService->setSource('api');

        $isNotBadPrizeError = !str_contains($add, 'Bad prizes length');
        $isNotIncorrectDateError = !str_contains($add, 'Incorrect date count');
        $isNotIncorrectPrizeError = !str_contains($add, 'Incorrect prize count');
        $isNotConnectionError = !str_contains($add, 'Could not resolve host');
        $isNotTimeoutError = !str_contains($add, 'Operation timed out');
        $isNotBadDataError = !str_contains($add, 'Unable to read fetched data');
        $isNotEmptyResponse = !str_contains($add, 'Empty reply from server');
        $isNotWrongResponse = !str_contains($add, 'Attempt to read property "Draws" on null');

        $now = Carbon::now($lottery['timezone']);
        $fourHoursAfterLastDrawDatetime = Carbon::parse($lottery['next_date_local'], $lottery['timezone'])->addHours(4);
        $isFourHoursAfterDraw = $now->greaterThan($fourHoursAfterLastDrawDatetime);

        $shouldLogImmediately = $isNotBadPrizeError &&
            $isNotIncorrectDateError &&
            $isNotIncorrectPrizeError &&
            $isNotConnectionError &&
            $isNotTimeoutError &&
            $isNotBadDataError &&
            $isNotWrongResponse &&
            $isNotEmptyResponse;
        $shouldLogError = $shouldLogImmediately || $isFourHoursAfterDraw;
        if ($shouldLogError) {
            $lotteryName = $lottery['name'] ?? '';
            $fileLoggerService->error(
                "[Lottery name: $lotteryName] Data download error: " . $add . "."
            );
        }

        echo '0';
        return true;
    }

    public static function getLotteries(string $slug = null): array
    {
        $query = DB::select('lottery.*', ['fc.code', 'force_currency'], ['currency.code', 'currency'])
            ->from('lottery')
            ->join('currency', 'INNER')->on('currency.id', '=', 'lottery.currency_id')
            ->join(['currency', 'fc'], 'LEFT')->on('fc.id', '=', 'lottery.force_currency_id');

        if ($slug !== null) {
            $query->and_where('lottery.slug', '=', $slug);
        }
        $query->order_by('lottery.id');

        $db = DB::query($query);


        //TODO: decide if we should use cache here
        /** @var object $db */
        return self::prepare_lotteries($db->execute()->as_array());
    }


    /**
     * @param int $lottery_id
     *
     * @throws Exception rethrows database connection exceptions
     *
     * @return array
     */
    public static function get_multipliers(int $lottery_id): array
    {
        return DB::select_array(['id', 'multiplier'])
            ->from('lottery_type_multiplier')
            ->where('lottery_id', '=', $lottery_id)
            ->execute()
            ->as_array();
    }

    /**
     * @param int $lottery_id
     *
     * @throws Exception rethrows database connection exceptions
     *                   
     * @return array
     */
    public static function get_numbers_per_line(int $lottery_id): array
    {
        return DB::select_array(['npl.min', 'npl.max'])
            ->from(['lottery_type_numbers_per_line', 'npl'])
            ->join(['lottery_type', 'lt'], 'LEFT')
            ->on('lt.id', '=', 'npl.lottery_type_id')
            ->join(['lottery', 'l'], 'LEFT')
            ->on('l.id', '=', 'lt.lottery_id')
            ->where('l.id', '=', $lottery_id)
            ->execute()
            ->as_array()[0];
    }

    public function is_keno(): bool
    {
        return $this->type === Helpers_Lottery::TYPE_KENO;
    }
}
