<?php

use Carbon\Carbon;
use Models\Lottery;

/**
 * Helper for lotteries.
 *
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-06-14
 * Time: 16:38:05
 */
final class Helpers_Lottery
{
    /**
     * @var string
     */
    const TYPE_LOTTERY = 'lottery';
    /**
     * @var string
     */
    const TYPE_PICK = 'pick';
    /**
     * @var string
     */
    const TYPE_KENO = 'keno';
    /**
     * @var string
     */
    const TYPE_RAFFLE_OPEN = 'raffle-open';
    /**
     * @var string
     */
    const TYPE_RAFFLE_CLOSED = 'raffle-closed';

    /** @var array */
    const SPECIAL_CLOSING_TIMES = [22, 23];

    const KENO_DRAW_INTERVAL_IN_MINUTES = 4;

    const MEGA_MILLIONS_ID = 2;
    const SUPER_ENALOTTO_ID = 4;
    const ZAMBIA_ID = 16;
    const GGWORLD_ID = 17;
    const PERU_ID = 18;
    const GGWORLD_X_ID = 19;
    const GGWORLD_MILLION_ID = 20;
    const FLORIDA_LOTTO_ID = 21;
    const MEGASENA_ID = 22;
    const QUINA_ID = 23;
    const OTOSLOTTO_ID = 24;
    const HATOSLOTTO_ID = 25;
    const SETFORLIFE_UK_ID = 26;
    const THUNDERBALL_ID = 27;
    const LOTTO_AMERICA_ID = 28;
    const LOTTO_AT_ID = 29;
    const LOTTO_6AUS49_ID = 30;
    const SKANDINAV_LOTTO_ID = 31;
    const LOTTO_MULTI_MULTI_ID = 32;
    const KENO_ID = 33;

    const DOUBLE_JACK_ID = 34;
    const DOUBLE_JACK_X_ID = 35;
    const DOUBLE_JACK_M_ID = 36;
    const DOUBLE_JACK_KENO_ID = 37;

    const POLISH_KENO_ID = 38;
    const GREEK_KENO_ID = 39;
    const CZECH_KENO_ID = 40;
    const SLOVAK_KENO_ID = 41;
    const LATVIAN_KENO_ID = 42;
    const FINNISH_KENO_ID = 43;
    const FRENCH_KENO_ID = 44;
    const EURODREAMS_ID = 45;
    const HUNGARIAN_KENO_ID = 46;
    const ITALIAN_KENO_ID = 47;
    const WEEKDAY_WINDFALL_ID = 48;
    const SLOVAK_KENO_10_ID = 49;
    const GERMAN_KENO_ID = 50;
    const UKRAINIAN_KENO_ID = 51;
    const BELGIAN_KENO_ID = 52;
    const KENO_NEW_YORK_ID = 53;
    const EUROMILLIONS_SUPERDRAW_ID = 54;
    const LOTO_6_49_ID = 55;
    const MINI_POWERBALL_ID = 56;
    const BRAZILIAN_KENO_ID = 57;
    const SWEDISH_KENO_ID = 58;
    const AUSTRALIAN_KENO_ID = 59;
    const DANISH_KENO_ID = 60;
    const NORWEGIAN_KENO_ID = 61;
    const LITHUANIAN_KENO_ID = 62;
    const CROATIAN_KENO_ID = 63;
    const BELARUSIAN_KENO_ID = 64;
    const ESTONIAN_KENO_ID = 65;
    const CANADIAN_KENO_ID = 66;
    const MINI_MEGA_MILLIONS_ID = 67;
    const MINI_EUROMILLIONS_ID = 68;
    const MINI_EUROJACKPOT_ID = 69;
    const MINI_SUPERENALOTTO_ID = 70;

    /**
     * Slugs for lotteries. 
     *
     * @var string[] slugs in format name => slug
     */
    const SLUGS = [
        self::ZAMBIA_ID => 'lotto-zambia',
        self::PERU_ID => 'somoslotto-plus',
        self::GGWORLD_ID => 'gg-world',
        self::GGWORLD_X_ID => 'gg-world-x',
        self::GGWORLD_MILLION_ID => 'gg-world-million',
        self::KENO_ID => 'gg-world-keno',
        self::DOUBLE_JACK_ID => 'gg-world',
        self::DOUBLE_JACK_X_ID => 'gg-world-x',
        self::DOUBLE_JACK_M_ID => 'gg-world-million',
        self::DOUBLE_JACK_KENO_ID => 'gg-world-keno',
        self::POLISH_KENO_ID => Lottery::POLISH_KENO_SLUG,
        self::GREEK_KENO_ID => Lottery::GREEK_KENO_SLUG,
        self::CZECH_KENO_ID => Lottery::CZECH_KENO_SLUG,
        self::SLOVAK_KENO_ID => Lottery::SLOVAK_KENO_SLUG,
        self::LATVIAN_KENO_ID => Lottery::LATVIAN_KENO_SLUG,
        self::FINNISH_KENO_ID => Lottery::FINNISH_KENO_SLUG,
        self::FRENCH_KENO_ID => Lottery::FRENCH_KENO_SLUG,
        self::EURODREAMS_ID => Lottery::EURODREAMS_SLUG,
        self::HUNGARIAN_KENO_ID => Lottery::HUNGARIAN_KENO_SLUG,
        self::ITALIAN_KENO_ID => Lottery::ITALIAN_KENO_SLUG,
        self::SLOVAK_KENO_10_ID => Lottery::SLOVAK_KENO_10_SLUG,
        self::GERMAN_KENO_ID => Lottery::GERMAN_KENO_SLUG,
        self::UKRAINIAN_KENO_ID => Lottery::UKRAINIAN_KENO_SLUG,
        self::BELGIAN_KENO_ID => Lottery::BELGIAN_KENO_SLUG,
        self::KENO_NEW_YORK_ID => Lottery::KENO_NEW_YORK_SLUG,
        self::EUROMILLIONS_SUPERDRAW_ID => Lottery::EUROMILLIONS_SUPERDRAW_SLUG,
        self::LOTO_6_49_ID => Lottery::LOTO_6_49_SLUG,
        self::MINI_POWERBALL_ID => Lottery::MINI_POWERBALL_SLUG,
        self::BRAZILIAN_KENO_ID => Lottery::BRAZILIAN_KENO_SLUG,
        self::SWEDISH_KENO_ID => Lottery::SWEDISH_KENO_SLUG,
        self::AUSTRALIAN_KENO_ID => Lottery::AUSTRALIAN_KENO_SLUG,
        self::DANISH_KENO_ID => Lottery::DANISH_KENO_SLUG,
        self::NORWEGIAN_KENO_ID => Lottery::NORWEGIAN_KENO_SLUG,
        self::LITHUANIAN_KENO_ID => Lottery::LITHUANIAN_KENO_SLUG,
        self::CROATIAN_KENO_ID => Lottery::CROATIAN_KENO_SLUG,
        self::BELARUSIAN_KENO_ID => Lottery::BELARUSIAN_KENO_SLUG,
        self::ESTONIAN_KENO_ID => Lottery::ESTONIAN_KENO_SLUG,
        self::CANADIAN_KENO_ID => Lottery::CANADIAN_KENO_SLUG,
        self::MINI_MEGA_MILLIONS_ID => Lottery::MINI_MEGA_MILLIONS_SLUG,
        self::MINI_EUROMILLIONS_ID => Lottery::MINI_EUROMILLIONS_SLUG,
        self::MINI_EUROJACKPOT_ID => Lottery::MINI_EUROJACKPOT_SLUG,
        self::MINI_SUPERENALOTTO_ID => Lottery::MINI_SUPERENALOTTO_SLUG,
    ];

    /**
     * @var int[] IDs of lotteries with multiplier support
     */
    const LOTTERIES_WITH_MULTIPLIER_SUPPORT = [
        self::KENO_ID,
        self::POLISH_KENO_ID,
        self::GREEK_KENO_ID,
        self::CZECH_KENO_ID,
        self::SLOVAK_KENO_ID,
        self::LATVIAN_KENO_ID,
        self::FINNISH_KENO_ID,
        self::FRENCH_KENO_ID,
        self::HUNGARIAN_KENO_ID,
        self::ITALIAN_KENO_ID,
        self::SLOVAK_KENO_10_ID,
        self::GERMAN_KENO_ID,
        self::UKRAINIAN_KENO_ID,
        self::BELGIAN_KENO_ID,
        self::KENO_NEW_YORK_ID,
        self::BRAZILIAN_KENO_ID,
        self::SWEDISH_KENO_ID,
        self::AUSTRALIAN_KENO_ID,
        self::DANISH_KENO_ID,
        self::NORWEGIAN_KENO_ID,
        self::LITHUANIAN_KENO_ID,
        self::CROATIAN_KENO_ID,
        self::BELARUSIAN_KENO_ID,
        self::ESTONIAN_KENO_ID,
        self::CANADIAN_KENO_ID,
    ];

    const KENO_DEFAULT_NUMBERS_PER_LINE = 10;

    /**
     * @var int[] IDs of keno lotteries
     */
    const KENO_LOTTERIES_IDS = [
        self::KENO_ID,
        self::POLISH_KENO_ID,
        self::GREEK_KENO_ID,
        self::CZECH_KENO_ID,
        self::SLOVAK_KENO_ID,
        self::LATVIAN_KENO_ID,
        self::FINNISH_KENO_ID,
        self::FRENCH_KENO_ID,
        self::HUNGARIAN_KENO_ID,
        self::ITALIAN_KENO_ID,
        self::SLOVAK_KENO_10_ID,
        self::GERMAN_KENO_ID,
        self::UKRAINIAN_KENO_ID,
        self::BELGIAN_KENO_ID,
        self::KENO_NEW_YORK_ID,
        self::BRAZILIAN_KENO_ID,
        self::SWEDISH_KENO_ID,
        self::AUSTRALIAN_KENO_ID,
        self::DANISH_KENO_ID,
        self::NORWEGIAN_KENO_ID,
        self::LITHUANIAN_KENO_ID,
        self::CROATIAN_KENO_ID,
        self::BELARUSIAN_KENO_ID,
        self::ESTONIAN_KENO_ID,
        self::CANADIAN_KENO_ID,
    ];

    /**
     * @var string[] slugs of quick keno lotteries
     */
    const QUICK_KENO_SLUGS = [
        'gg-world-keno',
        'double-jack-keno',
        Lottery::POLISH_KENO_SLUG,
        Lottery::GREEK_KENO_SLUG,
        Lottery::CZECH_KENO_SLUG,
        Lottery::SLOVAK_KENO_SLUG,
        Lottery::ITALIAN_KENO_SLUG,
        Lottery::KENO_NEW_YORK_SLUG,
        Lottery::BRAZILIAN_KENO_SLUG,
    ];

    /**
     * @var int[] ids of mini lotteries
     */
    const MINI_LOTTERIES = [
        self::MINI_POWERBALL_ID => Lottery::MINI_POWERBALL_SLUG,
        self::MINI_MEGA_MILLIONS_ID => Lottery::MINI_MEGA_MILLIONS_SLUG,
        self::MINI_EUROMILLIONS_ID => Lottery::MINI_EUROMILLIONS_SLUG,
        self::MINI_EUROJACKPOT_ID => Lottery::MINI_EUROJACKPOT_SLUG,
        self::MINI_SUPERENALOTTO_ID => Lottery::MINI_SUPERENALOTTO_SLUG,
    ];

    /**
     * Get slug for lcs lottery.
     *
     * @param int $lottery_id of the lottery @see constants in this class.
     *
     * @return string
     */
    public static function get_slug(int $lottery_id): string
    {
        return self::SLUGS[$lottery_id];
    }

    /**
     * Get lottery_id for lcs lottery.
     *
     * @param string $slug $slug of the lottery @see constants in this class.
     *
     * @return int|false false if id was not found for provided slug. It shouldn't happen unless misconfigured
     */
    public static function get_lottery_id(string $slug): int
    {
        return array_search($slug, self::SLUGS, true);
    }

    /**
     * @param array $draw_date_times format ["Mon 1:00", "Mon 2:00", ... , "Sun 23:59"]
     *
     * @return array format [1 => ['00:00:00' , '12:00:00']...7 => ['23:59:59']]
     * @throws Exception Emits Exception in case of an error on creating DateTime object.
     */
    public static function group_datetimes_by_weekdays(array $draw_date_times): array
    {
        $new_array = [];
        foreach ($draw_date_times as $draw_date_time) {
            $draw_date_time_object = new DateTime('last ' . $draw_date_time);
            $weekday = $draw_date_time_object->format('N');
            if (isset($new_array[$weekday]) === false) {
                $new_array[$weekday] = [];
            }
            $new_array[$weekday][] = $draw_date_time_object->format('H:i');
        }

        return $new_array;
    }

    /**
     * Calculate last draw date.
     *
     * @param array    $draw_date_times format ["Mon 1:00", "Mon 2:00", ... , "Sun 23:59"]
     * @param DateTime $now             optional now date, otherwise it will be fetched automatically
     *
     * @return string
     */
    public static function calculate_last_draw_datetime(array $draw_date_times, DateTime $now = null): string
    {
        $now = $now ?: new DateTime();
        $draw_date_times = Helpers_Lottery::group_datetimes_by_weekdays($draw_date_times);
        $draw_days = array_keys($draw_date_times);
        // find first draw date that elapsed start from the last one
        for ($i = count($draw_date_times) - 1; $i >= 0; $i--) {
            $draw_time_index = 0;
            $now_day = (int)$now->format('N');
            $draw_day = $draw_days[$i];
            if (
                $now_day > $draw_days[$i] || // now has greater day
                // or now has the same day but greater time
                $draw_day === $now_day && $now->format(Helpers_Time::TIME_FORMAT) > $draw_date_times[$draw_day][$draw_time_index]
            ) {
                while (
                    // we want to find to find the time of the draw
                    isset($draw_date_times[$draw_day][$draw_time_index + 1])
                    && $now->format(Helpers_Time::TIME_FORMAT) > $draw_date_times[$draw_day][$draw_time_index]
                ) {
                    $draw_time_index++;
                }

                return (new DateTime('last ' . Helpers_Time::ISO_WEEK_DAYS[$draw_day] . ' ' . $draw_date_times[$draw_day][$draw_time_index]))
                    ->format(Helpers_Time::DATETIME_FORMAT);
            }
        }
        // if we reached here then now_day is lower than any of the draw dates
        // meaning we should go for the last week (last draw_date from array);
        $last_day = array_pop($draw_days);
        $last_day_times = array_pop($draw_date_times);
        $draw_time_index = 0;
        while (
            // we want to find to find the time of the draw
            isset($last_day_times[$draw_time_index + 1])
            && $now->format(Helpers_Time::TIME_FORMAT) > $last_day_times[$draw_time_index]
        ) {
            $draw_time_index++;
        }

        return (new DateTime('last ' . Helpers_Time::ISO_WEEK_DAYS[$last_day] . ' ' . $last_day_times[$draw_time_index]))
            ->format(Helpers_Time::DATETIME_FORMAT);
    }

    /**
     * @param string $draw_dates_json
     *
     * @return array keys: 'next_date_local', 'next_date_utc', 'last_date_local'
     */
    public static function calculate_draw_datetimes(string $draw_dates_json, string $timezone): array
    {
        $draw_dates = (array)json_decode($draw_dates_json);
        $result = [];
        $result['next_date_local'] = Helpers_Lottery::calculate_last_draw_datetime($draw_dates);
        $result['next_date_utc'] = Helpers_Time::utc("{$result['next_date_local']}", $timezone);
        $result['last_date_local'] = Helpers_Lottery::calculate_last_draw_datetime($draw_dates, new DateTime($result['next_date_local'], new DateTimeZone($timezone)));

        return $result;
    }

    /**
     * @param array       $lotteryWithProvider
     *
     * @param Carbon|null $given_date
     *
     * @return string Y-m-d date (compatible with lottery->next_date_local)
     * @throws Exception
     */
    public static function calculate_next_draw_datetime(array $lotteryWithProvider, ?Carbon $given_date = null): string
    { // TODO: {Vordis 2019-11-06 14:52:46} legacy logic wrapper
        // source platform\fuel\app\classes\forms\wordpress\lottery\basket.php:253-258
        if (Lotto_Helper::is_lottery_closed($lotteryWithProvider)) {
            $ticket_draw_date = Lotto_Helper::get_lottery_next_draw($lotteryWithProvider, true, null, 2);
        } else {
            $ticket_draw_date = Lotto_Helper::get_lottery_next_draw($lotteryWithProvider, true, $given_date);
        }

        return $ticket_draw_date->format(Helpers_Time::DATETIME_FORMAT);
    }

    /**
     *
     * @return array
     */
    public static function get_jackpot_translations(): array
    {
        $jackpot_translations = [
            'pending' => _("Pending"),
            'bn' => _("bn"),
            'm' => _("m")
        ];

        return $jackpot_translations;
    }

    public static function supports_ticket_multipliers(array $lottery): bool
    {
        return in_array($lottery['id'], Helpers_Lottery::LOTTERIES_WITH_MULTIPLIER_SUPPORT);
    }

    public static function supports_ticket_multipliers_by_lottery_id(int $lottery_id): bool
    {
        return in_array($lottery_id, Helpers_Lottery::LOTTERIES_WITH_MULTIPLIER_SUPPORT);
    }

    /**
     * @param Model_Lottery|array $lottery
     *
     * @return bool
     */
    public static function is_keno($lottery)
    {
        return $lottery['type'] === self::TYPE_KENO;
    }

    public static function isQuickKeno(Model_Lottery|array $lottery): bool
    {
        return in_array($lottery['slug'], self::QUICK_KENO_SLUGS);
    }

    public static function is_drawed_on_weekday(array $lottery, \Carbon\Carbon $date): bool
    {
        $draw_dates = (array)json_decode($lottery['draw_dates']);
        foreach ($draw_dates as $draw_date) {
            $draw_date = \Carbon\Carbon::createFromTimeString($draw_date, $lottery['timezone']);
            if ($draw_date->weekday() === $date->weekday()) {
                return true;
            }
        }


        return false;
    }

    /**
     * @param array $a first lottery data
     * @param array $b second lottery data
     *
     * @return int
     * @throws Exception rethrows DateTime exceptions
     */
    public static function sort_lotteries_by_last_date(array $a, array $b): int
    {
        $datea = Carbon::parse($a['last_date_local'], $a['timezone']);
        $dateb = Carbon::parse($b['last_date_local'], $b['timezone']);
        $datea->setTimezone("UTC");
        $dateb->setTimezone("UTC");
        if ($datea == $dateb) {
            return 0;
        }

        return ($datea > $dateb) ? -1 : 1;
    }

    /**
     * @param array $a first lottery data
     * @param array $b second lottery data
     *
     * @return int
     * @throws Exception
     */
    public static function sort_lotteries_by_next_date(array $a, array $b): int
    {
        $datea = Carbon::parse($a['last_date_local'], $a['timezone']);
        $dateb = Carbon::parse($b['last_date_local'], $b['timezone']);
        $datea->setTimezone("UTC");
        $dateb->setTimezone("UTC");
        if ($datea == $dateb) {
            return 0;
        }

        return ($datea < $dateb) ? -1 : 1;
    }

    public static function first_draw_time_of_weekday(array $drawDates, string $timezone, Carbon $weekdayDate): Carbon
    {
        $weekdayDate = $weekdayDate->clone();
        foreach ($drawDates as $drawDate) {
            $drawDate = Carbon::parse($drawDate, $timezone);
            if ($drawDate->weekday() === $weekdayDate->weekday()) {
                $weekdayDate->setTime($drawDate->hour, $drawDate->minute);

                return $weekdayDate;
            }
        }

        $weekday = $weekdayDate->shortEnglishDayOfWeek;
        $weekdayDateFormatted = $weekdayDate->format(Helpers_Time::DATE_FORMAT);
        throw new Exception("Could not find that weekday ($weekday) in the draw dates array for date $weekdayDateFormatted");
    }

    public static function getLotteries(): array
    {
        return Database_Service_Lottery::get_lotteries(function (array $whitelabel): array {
            return Model_Lottery::get_lotteries_for_whitelabel($whitelabel);
        });
    }

    public static function getPricing($lottery, $ticketMultiplier = 1): string
    {
        $convertedPrice = '0';
        if (!is_null($lottery) && is_array($lottery)) {
            $currency = Helpers_Currency::getUserCurrencyTable();
            $convertedPrice = Lotto_Helper::get_user_converted_price($lottery, $currency['id'], (int)$ticketMultiplier);
        }
        return $convertedPrice;
    }

    public static function isPending(array $lottery): bool
    {
        $nextDraw = Carbon::parse($lottery['next_date_local'], $lottery['timezone']);
        $now = Carbon::now($lottery['timezone']);
        return $nextDraw <= $now;
    }

    /**
     * Lotteries with enabled GGR mean that there should be no margin/royalties calculations, so the customer is not double charged in invoice.
     * @link https://gginternational.slite.com/app/docs/doJc-24iigCbc_ read ADR
     */
    public static function isGgrEnabled(string $lotteryType): bool
    {
        $ggrEnabledLotteryTypes = [
            self::TYPE_KENO
        ];

        if (in_array($lotteryType, $ggrEnabledLotteryTypes, true)) {
            return true;
        }

        return false;
    }

    public static function isGgrNotEnabled(string $lotteryType): bool
    {
        return !self::isGgrEnabled($lotteryType);
    }

    public static function getDrawDatesArray($start_time, $interval, $draws_daily, $breakStartTime = null, $breakEndTime = null): array
    {
        $draw_dates = [];
        foreach (Helpers_Time::ISO_WEEK_DAYS as $day) {
            $date = Carbon::parse($day . ' ' . $start_time);

            // Optional break times
            $breakStartCarbon = $breakStartTime ? Carbon::parse($day . ' ' . $breakStartTime) : null;
            $breakEndCarbon = $breakEndTime ? Carbon::parse($day . ' ' . $breakEndTime) : null;

            for ($i = 0; $i < $draws_daily; $i++) {
                $date->addMinutes($i == 0 ? 0 : $interval);// we don't want to add interval to start time!

                $isDrawDuringBreak = $breakStartCarbon && $breakEndCarbon && $date->between($breakStartCarbon, $breakEndCarbon, false);
                if ($isDrawDuringBreak) {
                    continue;
                }
                $draw_dates[] = $date->format('D H:i');
            }
        }
        return $draw_dates;
    }

    public static function lotteryMultipliersRows(int $lotteryID, int $multiplierMax): array
    {
        $multipliers = [];
        for ($i = 1; $i <= $multiplierMax; $i++) {
            $multipliers[] = [$i, $lotteryID];
        }

        return $multipliers;
    }
}
