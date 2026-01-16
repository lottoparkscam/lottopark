<?php

use Carbon\Carbon;
use Fuel\Core\Config;
use Helpers\CurrencyHelper;
use Helpers\UrlHelper;
use Models\
{WhitelabelAff, WhitelabelUserAff};
use Models\Lottery;
use Repositories\LotteryDelayRepository;
use Repositories\LottorisqLogRepository;
use Services\{
    AffService,
    Logs\FileLoggerService,
    LotteryPurchaseLimitService
};
use Fuel\Core\Validation_Error;
use Helpers\Wordpress\LanguageHelper;

/**
 * @deprecated
 * One class with tons of different helper functions
 */
class Lotto_Helper
{

    /**
     * Used only in dev for now
     *
     * @param object $job
     *
     * @return bool
     * @throws Exception
     */
    public static function release_imvalap_job($job)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        Config::load("imvalap.ini", true);
        $url = Config::get("imvalap.imvalap.url");
        $token = Config::get("imvalap.tokens.game" . $job->game_id);
        $data = [
            "token" => $token,
            "jobid" => $job->jobid
        ];

        try {
            $curl = curl_init($url . 'client_release_job');
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: text/xml', 'Cache-control: no-cache']);
            $response = curl_exec($curl);
            if ($response !== false) {
                libxml_use_internal_errors(true);
                $xmlres = new SimpleXMLElement(trim($response));
                if (!isset($xmlres->error)) {
                    $state = $xmlres->state;
                    $job->set([
                        "status" => "109" // released
                    ]);

                    return true;
                } else {
                    $add = "";
                    if (!empty($xmlres->error)) {
                        $add = ' [' . $xmlres->error . ']';
                    }
                    $errortype = 0;
                    throw new Exception("Couldn't release job at Imvalap" . $add . ".");
                }
            } else {
                // curl error
                $error = curl_error($curl);
                $errortype = 0;
                throw new Exception("CURL Error [" . $error . "].");
            }
            curl_close($curl);
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage(),
                'api'
            );

            return $e->getMessage();
        }
    }

    /**
     * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
     * Officially based on Fuel\Core\Cache::set, third parameter is bool
     * Maybe I am wrong and not that
     *
     * @param string $key
     * @param mixed  $value
     * @param int    $time
     */
    public static function set_cache($key, $value, $time)
    {
        $keys = explode('.', $key);
        $name = array_shift($keys);
        $path = implode('.', $keys);
        $arr = [];
        try {
            $arr = @Cache::get($name);
        } catch (\CacheNotFoundException $e) {
            // do nothing
        }
        if (!empty($path)) {
            Arr::set($arr, $path, $value);
        } else {
            $arr = $value;
        }
        Cache::set($name, $arr, $time);
    }

    /**
     *
     * @param string $key
     *
     * @return array I don't really know if it is really array
     * @throws \CacheNotFoundException
     */
    public static function get_cache($key)
    {
        $keys = explode('.', $key);
        $name = "";
        $name = array_shift($keys);
        $path = implode('.', $keys);

        // This method could throw an Exception
        // So, code could not go further
        $arr = @Cache::get($name);

        if ($arr === null) {
            throw new \CacheNotFoundException();
        }

        if (!empty($path)) {
            $arr = Arr::get($arr, $path);
        }

        if ($arr === null) {
            throw new \CacheNotFoundException();
        }

        return $arr;
    }

    /**
     *
     * @param array $array
     *
     * @return bool
     */
    public static function ksort_recursive(&$array)
    {
        if (!is_array($array)) {
            return false;
        }
        ksort($array);
        foreach ($array as &$arr) {
            self::ksort_recursive($arr);
        }

        return true;
    }

    /**
     *
     * @param string $key
     */
    public static function clear_cache_item($key)
    {
        $keys = explode('.', $key);
        $name = array_shift($keys);
        $path = implode('.', $keys);
        $arr = [];

        try {
            $arr = @Cache::get($name);
        } catch (\CacheNotFoundException $e) {
            // do nothing
        }
        if (!empty($path)) {
            Arr::delete($arr, $path);
            Cache::delete($name);
            Cache::delete_all($name);
            Cache::set($name, $arr, 60 * 60 * 24);
        } else {
            Cache::delete($name);
            Cache::delete_all($name);
        }
    }

    /**
     *
     * @return string
     */
    public static function get_current_order_sum()
    {
        $ord = Session::get("order");
        if (empty($ord) || !is_array($ord)) {
            return "0";
        }
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $lotteries = Model_Lottery::get_lotteries_for_whitelabel($whitelabel);
        $total_sum = "0";
        foreach ($ord as $item) {
            $lottery = $lotteries['__by_id'][$item['lottery']];
            $pricing = lotto_platform_get_pricing($lottery);
            $pricing_mul = bcmul($pricing, !empty($item['lines']) ? count($item['lines']) : 0, 2);
            $total_sum = bcadd($total_sum, $pricing_mul, 2);
        }

        return $total_sum;
    }

    /**
     *
     * @return string|null
     * @global array $sitepress Unused
     */
    public static function get_best_match_user_country()
    {
        global $sitepress;
        $is_user = Lotto_Settings::getInstance()->get("is_user");
        $country = null;
        if ($is_user) {
            $user = Lotto_Settings::getInstance()->get("user");
            if (!empty($user['country'])) {
                $country = $user['country'];
            } elseif (!empty($user['last_country'])) {
                $country = $user['last_country'];
            }
        } else {
            $geoip = Lotto_Helper::get_geo_IP_record(Lotto_Security::get_IP());
            $country = null;
            if ($geoip !== false) {
                $country = $geoip->country->isoCode;
            }
        }
        $wlanguage = LanguageHelper::getCurrentWhitelabelLanguage();
        if ($country === null && $wlanguage !== null) {
            $code = explode("_", $wlanguage['code']);
            $countries = self::get_localized_country_list();
            if (isset($countries[$code[1]])) {
                $country = $code[1];
            }
        }

        return $country;
    }

    /**
     *
     * @return array
     */
    public static function get_profiled_lotteries_for_user()
    {
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $highest_lotteries = Model_Whitelabel::get_lotteries_by_highest_jackpot_for_whitelabel($whitelabel['id']);
        $user_country = Lotto_Helper::get_best_match_user_country();
        $how_many = 4;

        $profiled_lotteries = [];
        $in_list = [];
        foreach ($highest_lotteries as $lottery) {
            if ($lottery['country_iso'] == $user_country) {
                $profiled_lotteries[] = $lottery;
                $in_list[] = $lottery['id'];
            }
            if (count($profiled_lotteries) == $how_many) {
                break;
            }
        }
        foreach ($highest_lotteries as $lottery) {
            if (count($profiled_lotteries) == $how_many) {
                break;
            }
            if (!in_array($lottery['id'], $in_list)) {
                $profiled_lotteries[] = $lottery;
                $in_list[] = $lottery['id'];
            }
        }

        return $profiled_lotteries;
    }

    /**
     *
     * @return string
     */
    public static function get_current_order_count()
    {
        $ord = Session::get("order");
        if (empty($ord) || !is_array($ord)) {
            return "0";
        }

        return count($ord);
    }

    /**
     *
     * @return string
     */
    public static function get_possible_order()
    {
        $wlanguage = LanguageHelper::getCurrentWhitelabelLanguage();
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $actual_order = Helpers_Currency::sum_order(false);

        $user_currency_tab = CurrencyHelper::getCurrentCurrency()->to_array();
        $user_currency_code = $user_currency_tab['code'];

        $lottery_currency_tab = $user_currency_tab;

        $wlanguage_currency_id = $wlanguage['currency_id'];
        $wlanguage_currency_result = Model_Currency::find_by_id($wlanguage_currency_id);
        if (
            !empty($wlanguage_currency_result) &&
            count($wlanguage_currency_result) > 0
        ) {
            $lottery_currency_tab = [
                "id" => $wlanguage_currency_result[0]->id,
                "code" => $wlanguage_currency_result[0]->code,
                "rate" => $wlanguage_currency_result[0]->rate,
            ];
        }

        $user_currency_id = $user_currency_tab['id'];
        $user_currency_data = Model_Whitelabel_Default_Currency::get_for_user(
            $whitelabel,
            $user_currency_id
        );
        $max_order_amount_for_user = $user_currency_data['max_order_amount'];

        $max_order = $max_order_amount_for_user;

        if ((string)$lottery_currency_tab['code'] !== (string)$user_currency_code) {
            $max_order = Helpers_Currency::get_recalculated_to_given_currency(
                $max_order_amount_for_user,
                $lottery_currency_tab,
                $user_currency_code
            );
        }

        $possible_order = $max_order - $actual_order;
        if ($possible_order < 0) {
            $possible_order = "0";
        }

        return $possible_order;
    }

    /**
     *
     * @param array $whitelabel_languages
     *
     * @return array
     */
    public static function prepare_languages(array $whitelabel_languages): array
    {
        $whitelabel_languages_prepared = [];

        foreach ($whitelabel_languages as $whitelabel_language) {
            $whitelabel_languages_prepared[$whitelabel_language['id']] = $whitelabel_language;
        }

        return $whitelabel_languages_prepared;
    }

    /**
     *
     * @param array  $whitelabel
     * @param string $country
     *
     * @return string
     */
    public static function get_language_id($whitelabel, $country)
    {
        $whitelabel_languages = Model_Whitelabel_Language::get_whitelabel_languages($whitelabel);
        foreach ($whitelabel_languages as $key => $whitelabel_language) {
            if (substr($whitelabel_language['code'], 3, 2) == $country) {
                return $whitelabel_language['id'];
            }
        }

        return $whitelabel_languages[0]['id'];
    }

    /**
     *
     * @param array $whitelabel
     * @param array $whitelabel_payment_methods_with_currencies
     *
     * @return array
     */
    public static function get_whitelabel_payment_methods_for_language(
        array $whitelabel,
        array $whitelabel_payment_methods_with_currencies
    ): array {
        $wlanguage = LanguageHelper::getCurrentWhitelabelLanguage();
        $user = Lotto_Settings::getInstance()->get("user");

        $language_id_by_last_country = 0;
        if (!empty($user['last_country'])) {
            $language_id_by_last_country = self::get_language_id($whitelabel, $user['last_country']);
        }

        $language_id_by_country = null;
        if (!empty($user['country'])) {
            $language_id_by_country = self::get_language_id($whitelabel, $user['country']);
        }

        $final_language_id = null;
        $whitelabel_languages = null;
        if ($language_id_by_country !== null) {
            $final_language_id = $language_id_by_country;
        } else {
            $whitelabel_languages = Model_Whitelabel_Language::get_whitelabel_languages($whitelabel);

            foreach ($whitelabel_languages as $key => $whitelabel_language) {
                if ($key == 0) {
                    continue;
                }
                if (
                    (int)$whitelabel_language['id'] === (int)$language_id_by_last_country ||
                    (int)$whitelabel_language['id'] === (int)$wlanguage['id']
                ) {
                    $final_language_id = (int)$whitelabel_language['id'];
                }
            }
        }

        if (
            $final_language_id === null &&
            !empty($whitelabel_languages) &&
            !empty($whitelabel_languages[0]['id'])
        ) {
            $final_language_id = (int)$whitelabel_languages[0]['id'];
        }

        $whitelabel_payment_methods_for_language_id = [];
        $language_methods = [];

        foreach ($whitelabel_payment_methods_with_currencies as $whitelabel_payment_method_with_currency) {
            $language_id = $whitelabel_payment_method_with_currency['language_id'];
            $whitelabel_payment_method_id = $whitelabel_payment_method_with_currency['id'];
            $language_methods[$language_id][$whitelabel_payment_method_id] = $whitelabel_payment_method_with_currency;
        }

        // At this moment only English (ID = 1)
        if (isset($language_methods[1])) {
            $whitelabel_payment_methods_for_language_id = $language_methods[1];
        }

        // At this moment only English (ID = 1)
        // In fact it seems that is not used, because all
        // payment methods are defined in English
        if ($final_language_id != 1 && isset($language_methods[$final_language_id])) {
            foreach ($language_methods[$final_language_id] as $key => $lmethod) {
                $whitelabel_payment_methods_for_language_id[$key] = $lmethod;
            }
        }

        return $whitelabel_payment_methods_for_language_id;
    }

    /**
     *
     * @return int
     */
    public static function get_possible_order_count()
    {
        $whitelabel = Container::get('whitelabel');
        $act_order = self::get_current_order_count();
        $max_order = $whitelabel['max_order_count'];
        $pos_order = $max_order - $act_order;
        if ($pos_order < 0) {
            $pos_order = 0;
        }

        return $pos_order;
    }

    /**
     *
     * @param array $lottery
     *
     * @return string
     */
    public static function get_lottery_short_name($lottery)
    {
        return $lottery['shortname'];
    }

    /**
     *
     * @param array $lottery
     *
     * @return string
     */
    public static function get_lottery_bonus_ball_name(array $lottery): string
    {
        // TODO: verify this
        $ballnames = [
            1 => _("and %d Powerball number"),
            2 => _("and %d Mega Ball number"),
            3 => _("and %d Euronumbers"),
            4 => "",
            5 => "",
            6 => _("and %d Lucky Stars numbers"),
            7 => "",
            8 => "",
            9 => "",
            10 => "",
            11 => "",
            12 => _("and %d Reintegro number"),
            13 => "",
            14 => "",
            15 => "",
            16 => "",
            Helpers_Lottery::GGWORLD_ID => _("and %d GG numbers"),
            18 => "",
            Helpers_Lottery::GGWORLD_X_ID => _("and %d GG numbers"),
            Helpers_Lottery::GGWORLD_MILLION_ID => _("and %d GG numbers"),
            21 => "",
            22 => "",
            23 => _("and %d Powerball number"),
            24 => "",
            25 => "",
            26 => _("and %d Life Ball number"),
            27 => _("and %d Thunderball number"),
            28 => _("and %d Star Ball number"),
            29 => "",
            30 => "",
            31 => "",
            32 => _("and a Plus number"),
            Helpers_Lottery::DOUBLE_JACK_ID => _("and %d DJ numbers"),
            Helpers_Lottery::DOUBLE_JACK_X_ID => _("and %d DJ numbers"),
            Helpers_Lottery::DOUBLE_JACK_M_ID => _("and %d DJ numbers"),
            Helpers_Lottery::EURODREAMS_ID => _("and %d Dream Number"),
            Helpers_Lottery::WEEKDAY_WINDFALL_ID => '',
            Helpers_Lottery::EUROMILLIONS_SUPERDRAW_ID => _("and %d Lucky Stars numbers"),
            Helpers_Lottery::LOTO_6_49_ID => '',
            Helpers_Lottery::MINI_POWERBALL_ID => _("and %d Powerball number"),
            Helpers_Lottery::MINI_MEGA_MILLIONS_ID => _('and %d Mega Ball number'),
            Helpers_Lottery::MINI_EUROMILLIONS_ID => _('and %d Lucky Stars numbers'),
            Helpers_Lottery::MINI_EUROJACKPOT_ID => _('and %d Euronumbers'),
            Helpers_Lottery::MINI_SUPERENALOTTO_ID => '',
        ];

        return $ballnames[$lottery['id']];
    }

    /**
     *
     * @param Validation_Error[] $error_list
     *
     * @return array
     */
    public static function generate_errors(array $error_list): array
    {
        $errors = [];
        foreach ($error_list as $field => $error) {
            $errors[$field] = $error->get_message();
        }

        return $errors;
    }

    /**
     *
     * @param string $text
     *
     * @return string
     */
    public static function strip_spaces($text)
    {
        $text = str_replace(" ", "", $text);

        return $text;
    }

    /**
     * This method will strip one of defined prefixes from http_host, assuming it will produce whitelabel domain.
     * IMPORTANT: it will search strictly and strip only once. e.g. manager.manager.lottopark = manager.lottopark but managermanager. will be left untouched.
     * That's because manager doesn't which start the string is not ended by . character.
     * Limit 1 is also kind of micro optimization - non greedy algorithm, for every check it will avoid rechecking.
     * More examples at platform/fuel/app/tests/unit/classes/helpers/LottoHelperTest.php
     */
    public static function getWhitelabelDomainFromUrl(): string
    {
        $prefixesToRemove = [
            '/^www\./',
            '/^api\./',
            '/^aff\./',
            '/^manager\./',
            '/^empire\./',
        ];

        $casinoPrefixes = UrlHelper::getCasinoPrefixes();
        foreach ($casinoPrefixes as $prefix) {
            $prefixesToRemove[] = "/^$prefix\./";
        }

        if (empty($_SERVER['HTTP_HOST'])) {
            Config::load("lotteries", true);
            return Config::get("lotteries.domain");
        }

        $domain = $_SERVER['HTTP_HOST'];
        return preg_replace($prefixesToRemove, '', $domain, 1);
    }

    /**
     *
     * @return string
     */
    public static function get_URL()
    {
        $host = self::getWhitelabelDomainFromUrl();

        return 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $host;
    }

    /**
     *
     * @param array  $columns
     * @param array  $default
     * @param string $link
     *
     * @return array
     */
    public static function get_sort(
        array $columns,
        array $default,
        string $link
    ): array {
        $sort = [];
        $get = Input::get();
        $headers = [
            'asc' => 'Asc',
            'desc' => 'Desc'
        ];

        // prepare default sort table
        foreach ($columns as $column => $defsort) {
            $sort[$column] = ['class' => 'headerUnSorted', 'order' => 'none'];

            if (
                !empty($get['sort']) &&
                !empty($get['sort_order']) &&
                in_array($get['sort'], array_keys($columns)) &&
                in_array($get['sort_order'], ['asc', 'desc'])
            ) {
                if ($column == $get['sort']) {
                    $sort[$column]['order'] = $get['sort_order'];
                    $sort[$column]['class'] = 'header' . $headers[$get['sort_order']];
                    $sort['db'] = $column . ' ' . strtoupper($get['sort_order']);
                }
            } elseif ($default[0] == $column) {
                $sort[$column]['order'] = $default[1];
                $sort[$column]['class'] = 'header' . $headers[$default[1]];
                $sort['db'] = $column . ' ' . strtoupper($default[1]);
            }

            $click = $get;
            $click['sort'] = $column;
            $click['sort_order'] = 'none';
            if (
                $sort[$column]['order'] == "asc" ||
                ($sort[$column]['order'] == "none" &&
                    $defsort == "desc")
            ) {
                $click['sort_order'] = "desc";
            } else {
                $click['sort_order'] = "asc";
            }

            //$click['sort_order'] = in_array($sort[$column]['order'], array('none', 'desc')) ? 'asc' : 'desc';
            unset($click['show_page']);
            $sort[$column]['link'] = $link . '?' . http_build_query($click);

            // additional links for mobile
            $click['sort_order'] = 'asc';
            $sort[$column]['link_a'] = $link . '?' . http_build_query($click);

            $click['sort_order'] = 'desc';
            $sort[$column]['link_d'] = $link . '?' . http_build_query($click);
        }

        return $sort;
    }

    /**
     *
     * @param int $count
     * @param int $range
     *
     * @return array
     */
    public static function get_random_values(int $count, int $range): array
    {
        $final = [];
        if ($count == 0) {
            return $final;
        }

        $random = null;

        do {
            $random = mt_rand(1, $range);
            if (!in_array($random, $final)) {
                array_push($final, $random);
            }
        } while (count($final) < $count);

        return $final;
    }

    public static function calculate_closing_time(int $lottery_id, string $lottery_timezone, Model_Lottery_Provider $lottery_provider, string $nextDrawDatetime): Carbon
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $nextDrawDate = Carbon::parse($nextDrawDatetime, $lottery_timezone);
        if (in_array($lottery_id, Helpers_Lottery::SPECIAL_CLOSING_TIMES)) {
            $nextDrawWeekday = $nextDrawDate->isoWeekday();
            $closing_times = json_decode($lottery_provider['closing_times'], true);

            if (empty($closing_times[$nextDrawWeekday])) {
                // If closing_times for day $nextDrawWeekday is not set, use default closing time
                $closing_time = $lottery_provider['closing_time'];
            } else {
                // TODO: {Vordis 2021-02-17 10:42:03} it doesnt handle multiple times per day
                $closing_time = $closing_times[$nextDrawWeekday];
            }
        } else {
            $closing_time = $lottery_provider['closing_time'];
        }

        return Carbon::parse($nextDrawDatetime, $lottery_timezone)
            ->setTimezone($lottery_provider['timezone'])
            ->setTimeFromTimeString($closing_time)
            ->subHours($lottery_provider['offset'])
            ->setTimezone("UTC"); // calculate to UTC so we can compare with server time
    }

    /**
     *
     * @param array    $lottery
     * @param DateTime $validto
     *
     * @return bool
     */
    public static function is_lottery_closed(
        array $lottery,
        $validto = null,
        array $whitelabel = null,
        Model_Lottery_Provider $lottery_provider = null
    ): bool {
        $fileLoggerService = Container::get(FileLoggerService::class);

        // This situation should not happend, but
        // unfortunately cound happend (especially on localhost)
        if (empty($lottery['next_date_local'])) {
            $error_message = "Setting next_date_local is empty for lottery: ";
            $error_message .= $lottery['id'];

            $fileLoggerService->error(
                $error_message
            );

            return true;
        }
        if ($lottery['type'] === Helpers_Lottery::TYPE_KENO) {
            return false;
        }

        $nextDrawDatetime = $lottery['next_date_local'];

        if ($validto !== null) {
            $nextDrawDatetime = $validto;
        }

        $whitelabel = $whitelabel ?: Lotto_Settings::getInstance()->get('whitelabel');

        if (empty($whitelabel)) {
            $whitelabel_lottery = Model_Whitelabel_Lottery::get_last_by_lottery_id($lottery['id']);
        } elseif ($lottery_provider === null) {
            $whitelabel_lottery = Model_Whitelabel_Lottery::find_for_whitelabel_and_lottery(
                $whitelabel['id'],
                $lottery['id']
            )[0];
        }

        $lottery_provider = $lottery_provider ?: Model_Lottery_Provider::find_by_pk($whitelabel_lottery['lottery_provider_id']);

        $now = Carbon::now();
        $closing_datetime = self::calculate_closing_time($lottery['id'], $lottery['timezone'], $lottery_provider, $nextDrawDatetime);
        $closing_datetime->subHour(); // close lottery 1h before provider close

        return $now->greaterThanOrEqualTo($closing_datetime);
    }

    /**
     * @param Model_Lottery|array $lottery
     * @param int                 $next
     *
     * @return Carbon
     * @throws Exception                      Rethrows Carbon exceptions
     */
    public static function get_lottery_real_next_draw($lottery, int $next = 1): Carbon
    {
        $isNextDateNotSet = $lottery['next_date_local'] === null;
        if ($isNextDateNotSet) {
            return self::get_lottery_next_draw($lottery, true, null, $next);
        }
        $now = Carbon::now();
        $nextDateLocal = Carbon::parse($lottery['next_date_local'], $lottery['timezone']);
        $isCurrentNextDateActual = $nextDateLocal > $now;
        if ($isCurrentNextDateActual) {
            return $nextDateLocal;
        }

        return self::get_lottery_next_draw($lottery, true, null, $next);
    }

    /**
     * E.g. Mon 20:30, Mon 20:40, Mon 20:50 should give 600, 600, (week seconds - 1200)
     * NOTE: We use UTC to avoid DST -> we are interested in absolute interval in seconds.
     */
    private static function calculateOffsetsInSecondsBetweenDrawDates(array $drawDates): array
    {
        $offsets = [];
        for ($i = 1; $i < count($drawDates); $i++) {
            $currentDateTimestamp = Carbon::createFromTimeString($drawDates[$i] . " next week", 'UTC')->timestamp; // cast to next week to avoid now dependency
            $previousDateTimestamp = Carbon::createFromTimeString($drawDates[$i - 1] . " next week", 'UTC')->timestamp;
            $offsets[] = $currentDateTimestamp - $previousDateTimestamp;
        }
        $firstDrawDateInNextWeek = Carbon::createFromTimeString($drawDates[0] . " next week", 'UTC')->addDays(7);
        $secondsInWeek = 604800;
        $currentDateTimestamp = $currentDateTimestamp ?? $firstDrawDateInNextWeek->timestamp - $secondsInWeek; // special case when there is only one draw date per week
        return array_merge(
            $offsets,
            [$firstDrawDateInNextWeek->timestamp - $currentDateTimestamp] // we always attach last draw date in week to first draw date in next week diff
        );
    }

    /**
     * This adjustment is aimed to handle scheduled draws. E.g. imagine that we have draws [mon 20, wed 20]
     * Provider delayed draw over two weeks and instead of e.g. 06 month 15, 17 we have something like 15, 02-07
     * Now in normal case it wouldn't consider next_date_local set from provider and just give us 17 instead of scheduled 02-07
     * Based on real case of quina, where we have draw from 14 to 23, moreover with pass on few draw dates over the way (unscheduled should delete them)
     * Current logic should work even if unscheduled didn't work.
     * @param Model_Lottery|array $lottery
     */
    private static function adjustNowBasedOnNextDateLocal(DateTimeZone $timezone, $lottery, bool &$nowAdjustedBasedOnNextDateLocal): Carbon
    {
        $nextDateLocal = $lottery['next_date_local'] ?? null;
        $now = Carbon::now($timezone);
        if (empty($nextDateLocal)) {
            return $now;
        }

        $nextDateLocalCarbon = Carbon::parse($nextDateLocal, $timezone);
        if ($nextDateLocalCarbon->isPast()) {
            return $now;
        }

        $now = $nextDateLocalCarbon->subSecond();
        Carbon::setTestNow($now);
        $nowAdjustedBasedOnNextDateLocal = true;
        return $now;
    }

    /**
     * todo: update doc
     * @param Model_Lottery|array $lottery    Current lottery
     * @param bool                $doesDelaysApply In the case that is false it is needed to
     *                                        return pure table without delay to compare with
     *                                        data pulled from deliver of dates (ex. superenalotto)
     * @param Carbon|null         $given_date In some cases function needs different date than last_date_local
     *                                        to calculate next date of draw - if it is empty it will pull
     *                                        last_date_local date from lottery
     * @param int                 $next
     *
     * @return Carbon
     */
    public static function get_lottery_next_draw(
        &$lottery,
        bool $doesDelaysApply = true,
        ?Carbon $fromDatetime = null,
        int $next = 1
    ): Carbon {
        $nowAdjustedBasedOnNextDateLocal = false;
        $drawDatetimes = json_decode($lottery['draw_dates']);
        $timezone = new DateTimeZone($lottery['timezone']);
        if ($fromDatetime !== null) {
            Carbon::setTestNow($fromDatetime);
            $now = Carbon::now($timezone);
        } else {
            $now = self::adjustNowBasedOnNextDateLocal($timezone, $lottery, $nowAdjustedBasedOnNextDateLocal);
        }

        /**
         * E.g. When last draw is 17-12-2022
         * It is Delay from 22-12-2022 to 23-12-2022
         * And next draw is set to 23-12-2022
         * Then this code fixes to get correct next_draw when it's before 22nd
         * Without this fix Carbon::setTestNow is set to one second before 23-12-2022
         * So the soonest next draw is after 23 - is got from drawDates
         * Because firstly here we take draw from drawDate and then check if the delay exists
         * We need to set setTestNow on second before 22 because this is real nextDraw before delay
         * Then we'll move it by delay to correct 23rd date
         */
        $lotteryDelayRepository = Container::get(LotteryDelayRepository::class);
        $isSuperena = (int)$lottery['id'] === Helpers_Lottery::SUPER_ENALOTTO_ID;
        $isNextDraw = !empty($lottery['next_date_local']);
        if ($doesDelaysApply && $isSuperena && $isNextDraw) {
            $nextDateLocalCarbon = Carbon::parse($lottery['next_date_local'], $timezone);
            $nextDrawDelayedFrom = $lotteryDelayRepository->getNextDrawBeforeDelay((int)$lottery['id'], $nextDateLocalCarbon);
            $isNextDrawDelayed = !empty($nextDrawDelayedFrom);
            if ($isNextDrawDelayed && $nowAdjustedBasedOnNextDateLocal) {
                $nextDrawDelayedFromCarbon = Carbon::parse($nextDrawDelayedFrom['date_local'], $timezone);
                $now = $nextDrawDelayedFromCarbon->subSecond();
                Carbon::setTestNow($now);
            }
        }

        $nowDrawDatetimeInAbsoluteComparableFormat = $now->format('NHi'); // e.g Mon 20:30 => 12030
        foreach ($drawDatetimes as $drawDatetimeIndex => $drawDatetime) { // go over draw dates until we find first not elapsed
            $drawDatetimeCarbon = Carbon::parse($drawDatetime, $timezone);
            $drawDateTimeInAbsoluteComparableFormat = $drawDatetimeCarbon->format('NHi');
            if ($drawDateTimeInAbsoluteComparableFormat > $nowDrawDatetimeInAbsoluteComparableFormat) {
                $nextDrawDatetime = $drawDatetimeCarbon;
                break;
            }
        }

        $isDateNotFoundInCurrentWeek = !isset($nextDrawDatetime);
        if ($isDateNotFoundInCurrentWeek) {
            $nextDrawDatetime = Carbon::parse($drawDatetimes[$drawDatetimeIndex = 0], $timezone); // in such case we know for sure that it must be the first date in next week
            if ($nextDrawDatetime->isPast()) { // NOTE: unfortunately if we have now date on the same day as draw e.g. Mon 20:40 Mon 20:50 then carbon will build Mon 20:40 on the same day as Mon 20:50
                $nextDrawDatetime->addDays(7);
            }
        }

        if ($fromDatetime !== null || $nowAdjustedBasedOnNextDateLocal) {
            Carbon::setTestNow();
        }

        if ($next > 1) { // simplified and independent calculation of future draws - calculate offsets between them and then add them to current draw as many times as needed
            $offsets = self::calculateOffsetsInSecondsBetweenDrawDates($drawDatetimes);
            $offsetsCount = count($offsets);
            while ($next-- > 1) {
                $nextDrawDatetime->addSeconds($offsets[$drawDatetimeIndex]);
                if (++$drawDatetimeIndex >= $offsetsCount) {
                    $drawDatetimeIndex = 0;
                }
            }
        }

        if ($doesDelaysApply) { // if delays apply we swap draw date for the delayed one. NOTE: that it is after long term calculation (next>1)
            $delay = Model_Lottery_Delay::get_delay_for_lottery_date($lottery, $nextDrawDatetime);
            if (isset($delay[0])) { // legacy
                $nextDrawDatetime = Carbon::createFromTimeString($delay[0]['date_delay'], $timezone);
            }

            /**
             * When it happens that there are two delays e.g. from 2022-12-17 20:00:00 to 2022-12-20 20:00:00
             * And from 2022-12-20 20:00:00 to 2022-12-22 20:00:00
             * And 2022-12-22 20:00:00 is date not from drawDates
             * It means that we would like to process both: 2022-12-20 and 2022-12-22
             * So when we download draw from 20th, it's currently later than 2022-12-20 20:00:00
             * So we need to take current set nextDraw and check if it has delays, later than now
             * In other case, next draw will be the date after 2022-12-20 20:00:00 took from drawDates
             * So 2022-12-22 20:00:00 will be skipped because it's not in drawDates
             * 2022-12-20 20:00:00 also will be skipped because it searches for dates later than 2022-12-20 20:00:00
             * So there won't be any chance to check delays for this date
             * This code fixes it
             *
             * Still could happen something like that:
             * lottery->lastDraw = 2022-12-17 20:00:00
             * lottery->nextDraw = 2022-12-20 20:00:00
             * delay from 2022-12-22 20:00:00 to 2022-12-23 20:00:00 (this date not exists in drawDates)
             * now: '2022-12-22 21:10:00'
             * It returns nextDraw '2022-12-24 20:00:00' instead of '2022-12-23 20:00:00'
             * @see platform/fuel/app/tests/feature/classes/lotto/LottoHelperTest.php::getLotteryNextDraw_withSingleDelayOnDrawNotInDrawDatesAndAfterNextNextDraw
             *
             * Properly we should check here if there is any record in lottery_delay which moves draw to date between
             * now and calculated next draw. No matter how many draws we skipped
             *
             * Now we just handle case with double delays
             */
            $realCurrentNextDrawOnLottery = Carbon::parse($lottery['next_date_local'], $timezone);
            $delay = Model_Lottery_Delay::get_delay_for_lottery_date($lottery, $realCurrentNextDrawOnLottery);
            $isDelay = isset($delay[0]);
            $isAfterNextDraw = $now->greaterThan($realCurrentNextDrawOnLottery);
            if ($isDelay && $isAfterNextDraw) {
                $hasDoubleDelay = $lotteryDelayRepository->isNextDrawDelayed((int)$lottery['id'], $realCurrentNextDrawOnLottery);
                $delayForCurrentNextDraw = Carbon::createFromTimeString($delay[0]['date_delay'], $timezone);
                $shouldSetNextDrawFromDelay = $hasDoubleDelay && $delayForCurrentNextDraw->greaterThan($now);
                if ($shouldSetNextDrawFromDelay) {
                    $nextDrawDatetime = $delayForCurrentNextDraw;
                }
            }
        }

        return $nextDrawDatetime;
    }

    /**
     *
     * @param array $lottery
     * @param array $type_data
     *
     * @return array
     */
    public static function prepare_prizes_for_lottorisq_insurance($lottery, $type_data)
    {
        $prizes = [];
        foreach ($type_data as $type) {
            if ($type['is_jackpot']) {
                $prizes[] = (string)round($lottery['current_jackpot'] * 1000000, 2);
            } elseif ($type['type'] == Helpers_General::LOTTERY_TYPE_DATA_PRIZE) {
                $prizes[] = $type['prize'];
            } elseif ($type['type'] == Helpers_General::LOTTERY_TYPE_DATA_ESTIMATED) {
                $prizes[] = $type['estimated'];
            } elseif ($type['type'] == Helpers_General::LOTTERY_TYPE_DATA_QUICK_PICK) {
                // do not include in prize breakdown [quickpick, uklottery]
            }
        }

        return $prizes;
    }

    /**
     *
     * @param array                             $lottery
     * @param Model_Whitelabel                  $whitelabel
     * @param Model_Whitelabel_User_Ticket      $ticket
     * @param Model_Whitelabel_User_Ticket_Slip $slip
     * @param array                             $sliplines
     *
     * @throws Exception
     */
    public static function process_lottorisq_slip(
        array $lottery,
        Model_Whitelabel $whitelabel,
        Model_Whitelabel_User_Ticket $ticket,
        Model_Whitelabel_User_Ticket_Slip $slip,
        array $sliplines,
        &$ticketsIdsToSetIsLtechInsufficientBalance,
        &$currenciesIdsToSetIsLtechInsufficientBalance,
    ) {
        /** @var LottorisqLogRepository $lottorisqLogRepository */
        $lottorisqLogRepository = Container::get(LottorisqLogRepository::class);

        $ltech_helper = new Helpers_Ltech($slip->whitelabel_ltech_id);
        $ltech_details = $ltech_helper->get_ltech_details();

        $wl_record = Model_Whitelabel_Lottery::find_by_pk($lottery['wid']);

        // Check if ticket purchase is locked for instance due to insufficient balance
        if (
            $ltech_details['whitelabel_ltech_record']['locked'] == 1 ||
            (int)$wl_record['ltech_lock'] === 1
        ) {
            $ticketsIdsToSetIsLtechInsufficientBalance[] = $ticket->id;
            $currenciesIdsToSetIsLtechInsufficientBalance[] = $ticket->currency_id;
            return false;
        }

        $gameids = [
            1 => "powerball", // Powerball
            2 => "megamillions", // Mega Millions
            3 => "eurojackpot", // Eurojackpot
            4 => "superenalotto", // SuperEnalotto
            5 => "lotto-uk", // UK National Lottery
            6 => "euromillions-at", // Euro Millions
            7 => "lotto-pl", // Polish Lotto
            8 => "la-primitiva", // La Primitiva (ES)
            9 => "bonoloto", // BonoLoto (ES)
            10 => "oz-lotto-au", // Oz Lotto (AU)
            11 => "powerball-au", // Powerball (AU)
            12 => "sat-lotto-au", // Saturday Lotto (AU)
            13 => "mon-wed-lotto-au", // Monday/Wednesday Lotto (AU)
            14 => "el-gordo-primitiva", // El Gordo (ES)
            15 => "lotto-fr", // Lotto FR
            21 => "lotto-fl-us", // Lotto FL
            22 => "megasena", // Mega-Sena
            23 => "quina", // Quina
            26 => "set-for-life", // Set For Life (UK)
            27 => "thunderball", // Thunderball (UK)
            28 => "lotto-america", // Lotto America
            29 => "lotto-at", // Lotto AT
            30 => "lotto-6aus49", // Lotto 6aus49
            31 => "skandinavlotto", // skandinavlotto
            32 => "multi-multi", // PL Lotto Multi-Multi
            33 => "gg-world-keno", // GG World Keno
        ];
        $dayofweek_arr = [
            1 => "monday",
            2 => "tuesday",
            3 => "wednesday",
            4 => "thursday",
            5 => "friday",
            6 => "saturday",
            7 => "sunday"
        ];

        $type = Model_Lottery_Type::get_lottery_type_for_date($lottery, $ticket->draw_date);
        if ($type === null) {
            throw new Exception('No lottery type.');
        }

        $type_data = Model_Lottery_Type_Data::find([
            'where' => [
                'lottery_type_id' => $type['id'],
            ],
            'order_by' => 'id'
        ]);

        $gameid = $gameids[$lottery['id']];
        $request = [];
        $request['type'] = $gameid;
        switch ($gameid) {
            case 'powerball':
                $request['draws'] = 1;
                $request['lines'] = [];
                foreach ($sliplines as $slipline) {
                    $numbers = explode(',', $slipline->numbers);
                    $bnumbers = $slipline->bnumbers;
                    foreach ($numbers as $key => $num) {
                        $numbers[$key] = intval($num);
                    }
                    $bnum = intval($bnumbers);
                    $rline = [
                        "random" => false,
                        "numbers" => [
                            "main" => $numbers,
                            "powerball" => $bnum
                        ]
                    ];
                    $request['lines'][] = $rline;
                }
                // TODO:
                $request['powerplay'] = false;
                if ($ticket->model == 1 && $ticket->is_insured) {
                    $prizes = self::prepare_prizes_for_lottorisq_insurance($lottery, $type_data);
                    $levels = [
                        "match-5-p",
                        "match-5",
                        "match-4-p",
                        "match-4",
                        "match-3-p",
                        "match-3",
                        "match-2-p",
                        "match-1-p",
                        "match-0-p"
                    ];
                    $tiers = array_combine($levels, $prizes);
                    $tiers = array_slice($tiers, 0, $ticket->tier);

                    $request['method'] = "insurance";
                    $request['prizes'] = $tiers;
                }
                break;
            case 'megamillions':
                $request['draws'] = 1;
                $request['lines'] = [];
                foreach ($sliplines as $slipline) {
                    $numbers = explode(',', $slipline->numbers);
                    $bnumbers = $slipline->bnumbers;
                    foreach ($numbers as $key => $num) {
                        $numbers[$key] = intval($num);
                    }
                    $bnum = intval($bnumbers);
                    $rline = [
                        "random" => false,
                        "numbers" => [
                            "main" => $numbers,
                            "megaball" => $bnum
                        ]
                    ];
                    $request['lines'][] = $rline;
                }
                // TODO:
                $request['megaplier'] = false;
                if ($ticket->model == 1 && $ticket->is_insured) {
                    $prizes = self::prepare_prizes_for_lottorisq_insurance($lottery, $type_data);
                    $levels = [
                        "match-5-m",
                        "match-5",
                        "match-4-m",
                        "match-4",
                        "match-3-m",
                        "match-3",
                        "match-2-m",
                        "match-1-m",
                        "match-0-m"
                    ];
                    $tiers = array_combine($levels, $prizes);
                    $tiers = array_slice($tiers, 0, $ticket->tier);

                    $request['method'] = "insurance";
                    $request['prizes'] = $tiers;
                }
                break;
            case 'eurojackpot':
                // TODO: subscriptions
                $drawdate = DateTime::createFromFormat(
                    Helpers_Time::DATETIME_FORMAT,
                    $ticket->draw_date,
                    new DateTimeZone("UTC")
                );
                $dayofweek = $drawdate->format('N');


                $request['weekdays'] = [$dayofweek_arr[$dayofweek]];
                $request['weeks'] = 1;

                $request['lines'] = [];
                foreach ($sliplines as $slipline) {
                    $numbers = explode(',', $slipline->numbers);
                    $bnumbers = explode(',', $slipline->bnumbers);
                    foreach ($numbers as $key => $num) {
                        $numbers[$key] = intval($num);
                    }
                    foreach ($bnumbers as $key => $bnum) {
                        $bnumbers[$key] = intval($bnum);
                    }

                    $rline = [
                        "random" => false,
                        "numbers" => [
                            "main" => $numbers,
                            "euro" => $bnumbers
                        ]
                    ];
                    $request['lines'][] = $rline;
                }

                if ($ticket->model == 1 && $ticket->is_insured) {
                    $prizes = self::prepare_prizes_for_lottorisq_insurance($lottery, $type_data);
                    $levels = [
                        "match-5-2",
                        "match-5-1",
                        "match-5",
                        "match-4-2",
                        "match-4-1",
                        "match-4",
                        "match-3-2",
                        "match-2-2",
                        "match-3-1",
                        "match-3",
                        "match-1-2",
                        "match-2-1"
                    ];

                    $tiers = array_combine($levels, $prizes);
                    $tiers = array_slice($tiers, 0, $ticket->tier);

                    $request['method'] = "insurance";
                    $request['prizes'] = $tiers;
                }
                break;
            case 'superenalotto':
                $request['draws'] = 1;
                $request['lines'] = [];
                foreach ($sliplines as $slipline) {
                    $numbers = explode(',', $slipline->numbers);
                    foreach ($numbers as $key => $num) {
                        $numbers[$key] = intval($num);
                    }
                    $rline = [
                        "random" => false,
                        "superstar" => false, // TODO:
                        "numbers" => [
                            "main" => $numbers,
                            "superstar" => null
                        ]
                    ];
                    $request['lines'][] = $rline;
                }

                // TODO: superstar & instant
                if ($ticket->model == 1 && $ticket->is_insured) {
                    $prizes = self::prepare_prizes_for_lottorisq_insurance($lottery, $type_data);
                    $levels = [
                        "match-6",
                        "match-5-j",
                        "match-5",
                        "match-4",
                        "match-3",
                        "match-2"
                    ];

                    $tiers = array_combine($levels, $prizes);
                    $tiers = array_slice($tiers, 0, $ticket->tier);

                    $request['method'] = "insurance";
                    $request['prizes'] = $tiers;
                }

                break;
            case 'lotto-uk':
                // TODO: subscriptions
                $drawdate = DateTime::createFromFormat(
                    Helpers_Time::DATETIME_FORMAT,
                    $ticket->draw_date,
                    new DateTimeZone("UTC")
                );
                $dayofweek = $drawdate->format('N');

                $dblottery = Model_Lottery::find_by_pk($lottery['id']);
                $lucky_draw_date = Lotto_Helper::get_lottery_next_draw(
                    $lottery,
                    true,
                    null,
                    2
                );
                $lucky_draw_date = $lucky_draw_date->format('N');

                $request['weekdays'] = [$dayofweek_arr[$dayofweek]];
                $request['weeks'] = 1;

                $request['lines'] = [];
                foreach ($sliplines as $slipline) {
                    $numbers = explode(',', $slipline->numbers);
                    foreach ($numbers as $key => $num) {
                        $numbers[$key] = intval($num);
                    }

                    $rline = [
                        "random" => false,
                        "numbers" => [
                            "main" => $numbers
                        ]
                    ];
                    $request['lines'][] = $rline;
                    $request['prize'] = [
                        "weekday" => $dayofweek_arr[$lucky_draw_date]
                    ];
                }

                // TODO: Raffles
                if ($ticket->model == 1 && $ticket->is_insured) {
                    $prizes = self::prepare_prizes_for_lottorisq_insurance($lottery, $type_data);
                    $levels = [
                        "match-6",
                        "match-5-b",
                        "match-5",
                        "match-4",
                        "match-3"
                    ];

                    $tiers = array_combine($levels, $prizes);
                    $tiers = array_slice($tiers, 0, $ticket->tier);

                    $request['method'] = "insurance";
                    $request['prizes'] = $tiers;
                }

                break;
            case 'euromillions-at':
                $request['draws'] = 1;
                $request['lines'] = [];
                foreach ($sliplines as $slipline) {
                    $numbers = explode(',', $slipline->numbers);
                    $bnumbers = explode(',', $slipline->bnumbers);

                    foreach ($numbers as $key => $num) {
                        $numbers[$key] = intval($num);
                    }
                    foreach ($bnumbers as $key => $bnum) {
                        $bnumbers[$key] = intval($bnum);
                    }
                    $rline = [
                        "random" => false,
                        "numbers" => [
                            "main" => $numbers,
                            "stars" => $bnumbers
                        ]
                    ];
                    $request['lines'][] = $rline;
                }

                if ($ticket->model == 1 && $ticket->is_insured) {
                    $prizes = self::prepare_prizes_for_lottorisq_insurance($lottery, $type_data);
                    $levels = [
                        "match-5-2",
                        "match-5-1",
                        "match-5",
                        "match-4-2",
                        "match-4-1",
                        "match-4",
                        "match-3-2",
                        "match-2-2",
                        "match-3-1",
                        "match-3",
                        "match-1-2",
                        "match-2-1",
                        "match-2"
                    ];

                    $tiers = array_combine($levels, $prizes);
                    $tiers = array_slice($tiers, 0, $ticket->tier);

                    $request['method'] = "insurance";
                    $request['prizes'] = $tiers;
                }
                break;
            case 'lotto-pl':
                $request['draws'] = 1;
                $request['lines'] = [];
                foreach ($sliplines as $slipline) {
                    $numbers = explode(',', $slipline->numbers);

                    foreach ($numbers as $key => $num) {
                        $numbers[$key] = intval($num);
                    }
                    $rline = [
                        "random" => false,
                        "numbers" => [
                            "main" => $numbers,
                        ]
                    ];
                    $request['lines'][] = $rline;
                }
                // TODO:
                $request['plus'] = false;
                $request['super'] = false;

                if ($ticket->model == 1 && $ticket->is_insured) {
                    $prizes = self::prepare_prizes_for_lottorisq_insurance($lottery, $type_data);
                    $levels = [
                        "match-6",
                        "match-5",
                        "match-4",
                        "match-3"
                    ];

                    $tiers = array_combine($levels, $prizes);
                    $tiers = array_slice($tiers, 0, $ticket->tier);

                    $request['method'] = "insurance";
                    $request['prizes'] = $tiers;
                }
                break;
            case 'la-primitiva':
                $request['draws'] = 1;
                $request['lines'] = [];
                $request['joker'] = false; // TODO: add joker
                foreach ($sliplines as $slipline) {
                    $numbers = explode(',', $slipline->numbers);

                    foreach ($numbers as $key => $num) {
                        $numbers[$key] = intval($num);
                    }

                    $rline = [
                        "random" => false,
                        "numbers" => [
                            "main" => $numbers
                        ]
                    ];
                    $request['lines'][] = $rline;
                }

                if ($slip->additional_data) {
                    //                    TODO: refactor into function
                    $additional_data = unserialize($slip->additional_data);
                    if (
                        isset($additional_data['refund']) &&
                        is_numeric($additional_data['refund'])
                    ) {
                        $reintegro = $additional_data['refund'];
                    } else {
                        $reintegro = Lotto_Helper::get_random_number();
                        $slip->additional_data = serialize(['refund' => $reintegro]);
                        $slip->save();
                    }
                    $request['numbers'] = [
                        "refund" => $reintegro
                    ];
                }

                if ($ticket->model == 1 && $ticket->is_insured) {
                    $prizes = self::prepare_prizes_for_lottorisq_insurance($lottery, $type_data);
                    $levels = [
                        "match-6-r",
                        "match-6",
                        "match-5-c",
                        "match-5",
                        "match-4",
                        "match-3",
                        "match-r"
                    ];

                    $tiers = array_combine($levels, $prizes);
                    $tiers = array_slice($tiers, 0, $ticket->tier);

                    $request['method'] = "insurance";
                    $request['prizes'] = $tiers;
                }
                break;
            case 'bonoloto':
                $request['draws'] = 1;
                $request['lines'] = [];
                foreach ($sliplines as $slipline) {
                    $numbers = explode(',', $slipline->numbers);

                    foreach ($numbers as $key => $num) {
                        $numbers[$key] = intval($num);
                    }

                    $rline = [
                        "random" => false,
                        "numbers" => [
                            "main" => $numbers
                        ]
                    ];
                    $request['lines'][] = $rline;
                }
                if ($ticket->model == 1 && $ticket->is_insured) {
                    $prizes = self::prepare_prizes_for_lottorisq_insurance($lottery, $type_data);
                    $levels = [
                        "match-6",
                        "match-5-c",
                        "match-5",
                        "match-4",
                        "match-3",
                        "match-r"
                    ];

                    $tiers = array_combine($levels, $prizes);
                    $tiers = array_slice($tiers, 0, $ticket->tier);

                    $request['method'] = "insurance";
                    $request['prizes'] = $tiers;
                }
                break;
            case 'el-gordo-primitiva':
                $request['draws'] = 1;
                $request['lines'] = [];
                foreach ($sliplines as $slipline) {
                    $numbers = explode(',', $slipline->numbers);

                    foreach ($numbers as $key => $num) {
                        $numbers[$key] = intval($num);
                    }
                    $rline = [
                        "random" => false,
                        "numbers" => [
                            "main" => $numbers
                        ]
                    ];
                    $request['lines'][] = $rline;
                }

                if ($slip->additional_data) {
                    $additional_data = unserialize($slip->additional_data);
                    if (
                        isset($additional_data['refund']) &&
                        is_numeric($additional_data['refund'])
                    ) {
                        $reintegro = $additional_data['refund'];
                    } else {
                        $reintegro = Lotto_Helper::get_random_number();
                        $slip->additional_data = serialize(['refund' => $reintegro]);
                        $slip->save();
                    }
                    $request['numbers'] = [
                        "key" => $reintegro
                    ];
                }

                if ($ticket->model == 1 && $ticket->is_insured) {
                    $prizes = self::prepare_prizes_for_lottorisq_insurance($lottery, $type_data);
                    $levels = [
                        "match-5-k",
                        "match-5",
                        "match-4-k",
                        "match-4",
                        "match-3-k",
                        "match-3",
                        "match-2-k",
                        "match-2",
                        "match-k"
                    ];

                    $tiers = array_combine($levels, $prizes);
                    $tiers = array_slice($tiers, 0, $ticket->tier);

                    $request['method'] = "insurance";
                    $request['prizes'] = $tiers;
                }
                break;

            case 'oz-lotto-au':
                $request['draws'] = 1;
                $request['lines'] = [];
                foreach ($sliplines as $slipline) {
                    $numbers = explode(',', $slipline->numbers);

                    foreach ($numbers as $key => $num) {
                        $numbers[$key] = intval($num);
                    }
                    $rline = [
                        "random" => false,
                        "numbers" => [
                            "main" => $numbers
                        ]
                    ];
                    $request['lines'][] = $rline;
                }

                if ($ticket->model == 1 && $ticket->is_insured) {
                    $prizes = self::prepare_prizes_for_lottorisq_insurance($lottery, $type_data);
                    $levels = [
                        "match-7",
                        "match-6-s",
                        "match-6",
                        "match-5-s",
                        "match-5",
                        "match-4",
                        "match-3-s"
                    ];

                    $tiers = array_combine($levels, $prizes);
                    $tiers = array_slice($tiers, 0, $ticket->tier);

                    $request['method'] = "insurance";
                    $request['prizes'] = $tiers;
                }
                break;
            case 'powerball-au':
                $request['draws'] = 1;
                $request['lines'] = [];
                foreach ($sliplines as $slipline) {
                    $numbers = explode(',', $slipline->numbers);
                    $bnumbers = $slipline->bnumbers;
                    foreach ($numbers as $key => $num) {
                        $numbers[$key] = intval($num);
                    }
                    $bnum = intval($bnumbers);
                    $rline = [
                        "random" => false,
                        "numbers" => [
                            "main" => $numbers,
                            "powerball" => $bnum
                        ]
                    ];
                    $request['lines'][] = $rline;
                }

                if ($ticket->model == 1 && $ticket->is_insured) {
                    $prizes = self::prepare_prizes_for_lottorisq_insurance($lottery, $type_data);
                    $levels = [
                        "match-7-p",
                        "match-7",
                        "match-6-p",
                        "match-6",
                        "match-5-p",
                        "match-4-p",
                        "match-5",
                        "match-3-p",
                        "match-2-p"
                    ];

                    $tiers = array_combine($levels, $prizes);
                    $tiers = array_slice($tiers, 0, $ticket->tier);

                    $request['method'] = "insurance";
                    $request['prizes'] = $tiers;
                }
                break;
            case 'sat-lotto-au':
                $request['draws'] = 1;
                $request['lines'] = [];
                foreach ($sliplines as $slipline) {
                    $numbers = explode(',', $slipline->numbers);

                    foreach ($numbers as $key => $num) {
                        $numbers[$key] = intval($num);
                    }
                    $rline = [
                        "random" => false,
                        "numbers" => [
                            "main" => $numbers
                        ]
                    ];
                    $request['lines'][] = $rline;
                }

                if ($ticket->model == 1 && $ticket->is_insured) {
                    $prizes = self::prepare_prizes_for_lottorisq_insurance($lottery, $type_data);
                    $levels = [
                        "match-6",
                        "match-5-s",
                        "match-5",
                        "match-4",
                        "match-3-s",
                        "match-1-s"
                    ];

                    $tiers = array_combine($levels, $prizes);
                    $tiers = array_slice($tiers, 0, $ticket->tier);

                    $request['method'] = "insurance";
                    $request['prizes'] = $tiers;
                }
                break;
            case 'mon-wed-lotto-au':
                $request['draws'] = 1;
                $request['lines'] = [];
                foreach ($sliplines as $slipline) {
                    $numbers = explode(',', $slipline->numbers);

                    foreach ($numbers as $key => $num) {
                        $numbers[$key] = intval($num);
                    }
                    $rline = [
                        "random" => false,
                        "numbers" => [
                            "main" => $numbers
                        ]
                    ];
                    $request['lines'][] = $rline;
                }

                if ($ticket->model == 1 && $ticket->is_insured) {
                    $prizes = self::prepare_prizes_for_lottorisq_insurance($lottery, $type_data);
                    $levels = [
                        "match-5-k",
                        "match-5",
                        "match-4-k",
                        "match-4",
                        "match-3-k",
                        "match-3",
                        "match-2-k",
                        "match-2",
                        "match-k"
                    ];

                    $tiers = array_combine($levels, $prizes);
                    $tiers = array_slice($tiers, 0, $ticket->tier);

                    $request['method'] = "insurance";
                    $request['prizes'] = $tiers;
                }
                break;
            case 'lotto-fr':
                $drawdate = DateTime::createFromFormat(
                    Helpers_Time::DATETIME_FORMAT,
                    $ticket->draw_date,
                    new DateTimeZone("UTC")
                );
                $dayofweek = $drawdate->format('N');

                $request['weekdays'] = [$dayofweek_arr[$dayofweek]];
                $request['weeks'] = 1;
                $request['lines'] = [];
                foreach ($sliplines as $slipline) {
                    $numbers = explode(',', $slipline->numbers);
                    $bnumbers = $slipline->bnumbers;
                    foreach ($numbers as $key => $num) {
                        $numbers[$key] = intval($num);
                    }
                    $bnum = intval($bnumbers);
                    $rline = [
                        "random" => false,
                        "numbers" => [
                            "main" => $numbers,
                            "chance" => $bnum
                        ]
                    ];
                    $request['lines'][] = $rline;
                }

                if ($ticket->model == 1 && $ticket->is_insured) {
                    $prizes = self::prepare_prizes_for_lottorisq_insurance($lottery, $type_data);
                    $levels = [
                        "match-5-c",
                        "match-5",
                        "match-4-c",
                        "match-4",
                        "match-3-c",
                        "match-3",
                        "match-2-c",
                        "match-2",
                        "match-c",
                        "raffle",
                    ];

                    $tiers = array_combine($levels, $prizes);
                    $tiers = array_slice($tiers, 0, $ticket->tier);

                    $request['method'] = "insurance";
                    $request['prizes'] = $tiers;
                }
                break;
            case 'lotto-fl-us':
                $request['draws'] = 1;
                $request['lines'] = [];
                foreach ($sliplines as $slipline) {
                    $numbers = explode(',', $slipline->numbers);
                    foreach ($numbers as $key => $num) {
                        $numbers[$key] = intval($num);
                    }
                    $rline = [
                        "random" => false,
                        "numbers" => [
                            "main" => $numbers,
                        ]
                    ];
                    $request['lines'][] = $rline;
                }

                if ($ticket->model == 1 && $ticket->is_insured) {
                    $prizes = self::prepare_prizes_for_lottorisq_insurance($lottery, $type_data);
                    $levels = [
                        "match-6",
                        "match-5",
                        "match-4",
                        "match-3",
                        "match-5-x",
                        "match-4-x",
                        "match-3-x",
                    ];

                    $tiers = array_combine($levels, $prizes);
                    $tiers = array_slice($tiers, 0, $ticket->tier);

                    $request['method'] = "insurance";
                    $request['prizes'] = $tiers;
                }
                break;
            case 'quina':
                $request['draws'] = 1;
                $request['lines'] = [];
                foreach ($sliplines as $slipline) {
                    $numbers = explode(',', $slipline->numbers);
                    foreach ($numbers as $key => $num) {
                        $numbers[$key] = intval($num);
                    }
                    $rline = [
                        "random" => false,
                        "numbers" => [
                            "main" => $numbers,
                        ]
                    ];
                    $request['lines'][] = $rline;
                }

                if ($ticket->model == 1 && $ticket->is_insured) {
                    $prizes = self::prepare_prizes_for_lottorisq_insurance($lottery, $type_data);
                    $levels = [
                        "match-5",
                        "match-4",
                        "match-3",
                        "match-2",
                    ];

                    $tiers = array_combine($levels, $prizes);
                    $tiers = array_slice($tiers, 0, $ticket->tier);

                    $request['method'] = "insurance";
                    $request['prizes'] = $tiers;
                }
                break;
            case 'megasena':
                $request['draws'] = 1;
                $request['lines'] = [];
                foreach ($sliplines as $slipline) {
                    $numbers = explode(',', $slipline->numbers);
                    foreach ($numbers as $key => $num) {
                        $numbers[$key] = intval($num);
                    }
                    $rline = [
                        "random" => false,
                        "numbers" => [
                            "main" => $numbers,
                        ]
                    ];
                    $request['lines'][] = $rline;
                }

                if ($ticket->model == 1 && $ticket->is_insured) {
                    $prizes = self::prepare_prizes_for_lottorisq_insurance($lottery, $type_data);
                    $levels = [
                        "match-6",
                        "match-5",
                        "match-4",
                    ];

                    $tiers = array_combine($levels, $prizes);
                    $tiers = array_slice($tiers, 0, $ticket->tier);

                    $request['method'] = "insurance";
                    $request['prizes'] = $tiers;
                }
                break;
            case 'set-for-life':
                $drawdate = DateTime::createFromFormat(
                    Helpers_Time::DATETIME_FORMAT,
                    $ticket->draw_date,
                    new DateTimeZone("UTC")
                );
                $dayofweek = $drawdate->format('N');
                $request['weekdays'] = [$dayofweek_arr[$dayofweek]];
                $request['lines'] = [];
                foreach ($sliplines as $slipline) {
                    $numbers = explode(',', $slipline->numbers);
                    $bnumbers = $slipline->bnumbers;
                    foreach ($numbers as $key => $num) {
                        $numbers[$key] = intval($num);
                    }
                    $bnum = intval($bnumbers);
                    $rline = [
                        "random" => false,
                        "numbers" => [
                            "main" => $numbers,
                            "life" => $bnum,
                        ]
                    ];
                    $request['lines'][] = $rline;
                }

                if ($ticket->model == 1 && $ticket->is_insured) {
                    $prizes = self::prepare_prizes_for_lottorisq_insurance($lottery, $type_data);
                    $levels = [
                        "match-5-l",
                        "match-5",
                        "match-4-l",
                        "match-4",
                        "match-3-l",
                        "match-3",
                        "match-2-l",
                        "match-2",
                    ];

                    $tiers = array_combine($levels, $prizes);
                    $tiers = array_slice($tiers, 0, $ticket->tier);

                    $request['method'] = "insurance";
                    $request['prizes'] = $tiers;
                }
                break;
            case 'thunderball':
                $drawdate = DateTime::createFromFormat(
                    Helpers_Time::DATETIME_FORMAT,
                    $ticket->draw_date,
                    new DateTimeZone("UTC")
                );
                $dayofweek = $drawdate->format('N');

                $request['weekdays'] = [$dayofweek_arr[$dayofweek]];
                $request['weeks'] = 1;
                $request['lines'] = [];
                foreach ($sliplines as $slipline) {
                    $numbers = explode(',', $slipline->numbers);
                    $bnumbers = $slipline->bnumbers;
                    foreach ($numbers as $key => $num) {
                        $numbers[$key] = intval($num);
                    }
                    $bnum = intval($bnumbers);
                    $rline = [
                        "random" => false,
                        "numbers" => [
                            "main" => $numbers,
                            "thunderball" => $bnum,
                        ]
                    ];
                    $request['lines'][] = $rline;
                }

                if ($ticket->model == 1 && $ticket->is_insured) {
                    $prizes = self::prepare_prizes_for_lottorisq_insurance($lottery, $type_data);
                    $levels = [
                        "match-5-t",
                        "match-5",
                        "match-4-t",
                        "match-4",
                        "match-3-t",
                        "match-3",
                        "match-2-t",
                        "match-1-t",
                        "match-0-t",
                    ];

                    $tiers = array_combine($levels, $prizes);
                    $tiers = array_slice($tiers, 0, $ticket->tier);

                    $request['method'] = "insurance";
                    $request['prizes'] = $tiers;
                }
                break;
            case 'lotto-america':
                $request['draws'] = 1;  // TODO: draws: 1, 2, 5, 10, 20, 26
                $request['lines'] = [];
                foreach ($sliplines as $slipline) {
                    $numbers = explode(',', $slipline->numbers);
                    $bnumbers = $slipline->bnumbers;
                    foreach ($numbers as $key => $num) {
                        $numbers[$key] = intval($num);
                    }
                    $bnum = intval($bnumbers);
                    $rline = [
                        "random" => false,
                        "numbers" => [
                            "main" => $numbers,
                            "star" => $bnum,
                        ]
                    ];
                    $request['lines'][] = $rline;
                }

                $request['bonus'] = false;

                if ($ticket->model == 1 && $ticket->is_insured) {
                    $prizes = self::prepare_prizes_for_lottorisq_insurance($lottery, $type_data);
                    $levels = [
                        "match-5-s",
                        "match-5",
                        "match-4-s",
                        "match-4",
                        "match-3-s",
                        "match-3",
                        "match-2-s",
                        "match-1-s",
                        "match-0-s",
                    ];

                    $tiers = array_combine($levels, $prizes);
                    $tiers = array_slice($tiers, 0, $ticket->tier);

                    $request['method'] = "insurance";
                    $request['prizes'] = $tiers;
                }
                break;
            case 'lotto-at':
                $request['draws'] = 1;
                $request['lines'] = [];
                foreach ($sliplines as $slipline) {
                    $numbers = explode(',', $slipline->numbers);
                    $bnumbers = $slipline->bnumbers;
                    foreach ($numbers as $key => $num) {
                        $numbers[$key] = intval($num);
                    }
                    $rline = [
                        "random" => false,
                        "numbers" => [
                            "main" => $numbers
                        ]
                    ];
                    $request['lines'][] = $rline;
                }

                if ($ticket->model == 1 && $ticket->is_insured) {
                    $prizes = self::prepare_prizes_for_lottorisq_insurance($lottery, $type_data);
                    $levels = [
                        "match-6",
                        "match-5-a",
                        "match-5",
                        "match-4-a",
                        "match-4",
                        "match-3-a",
                        "match-3",
                        "match-a",
                    ];

                    $tiers = array_combine($levels, $prizes);
                    $tiers = array_slice($tiers, 0, $ticket->tier);

                    $request['method'] = "insurance";
                    $request['prizes'] = $tiers;
                }
                break;
            case 'lotto-6aus49':
                $drawdate = DateTime::createFromFormat(
                    Helpers_Time::DATETIME_FORMAT,
                    $ticket->draw_date,
                    new DateTimeZone("UTC")
                );
                $dayofweek = $drawdate->format('N');

                $request['weekdays'] = [$dayofweek_arr[$dayofweek]];
                $request['weeks'] = 1;

                $request['lines'] = [];
                foreach ($sliplines as $slipline) {
                    $numbers = explode(',', $slipline->numbers);
                    $bnumbers = $slipline->bnumbers;
                    foreach ($numbers as $key => $num) {
                        $numbers[$key] = intval($num);
                    }
                    $rline = [
                        "random" => false,
                        "numbers" => [
                            "main" => $numbers,
                        ]
                    ];
                    $request['lines'][] = $rline;

                    $request['spiel77'] = false;
                    $request['super6'] = false;
                }

                if ($slip->additional_data) {
                    $additional_data = unserialize($slip->additional_data);
                    if (
                        isset($additional_data['super']) &&
                        is_numeric($additional_data['super'])
                    ) {
                        $super = $additional_data['super'];
                    } else {
                        $super = Lotto_Helper::get_random_number();
                        $slip->additional_data = serialize(['super' => $super]);
                        $slip->save();
                    }
                    $request['numbers'] = [
                        "ticket" => '123456' . $super
                    ];
                }

                if ($ticket->model == 1 && $ticket->is_insured) {
                    $prizes = self::prepare_prizes_for_lottorisq_insurance($lottery, $type_data);
                    $levels = [
                        "match-6-s",
                        "match-6",
                        "match-5-s",
                        "match-5",
                        "match-4-s",
                        "match-4",
                        "match-3-s",
                        "match-3",
                        "match-2-s",
                    ];

                    $tiers = array_combine($levels, $prizes);
                    $tiers = array_slice($tiers, 0, $ticket->tier);

                    $request['method'] = "insurance";
                    $request['prizes'] = $tiers;
                }
                break;
        }
        $requrl = $ltech_details['confirm'];
        $prefix = $ltech_details['prefix'];

        $request['callback'] = $requrl . $slip->id . '/' . $ltech_details['secret'];
        $request['meta'] = [
            'id' => $prefix . $slip->id
        ];

        $check_state = self::check_us_state($whitelabel, $ticket, $lottery);
        if ($check_state) {
            $request['location'] = $check_state;
        }

        // Check if ticket scans are enabled for this lottery
        if (
            (int)$lottery['scans_enabled'] === 1 &&
            Helpers_General::ticket_scan_availability($whitelabel, $lottery)
        ) {
            $request['scan'] = true;
        }

        $json = json_encode($request);

        $ltech_request = $ltech_helper->sendLtechRequest(
            'tickets',
            $request,
            $ltech_details['ltech_id'],
            $lottery['currency_id']
        );

        $response = $ltech_request['response'];
        $httpcode = $ltech_request['httpcode'];

        if ($response === false) {
            $errorcode = 1;
            throw new Exception("CURL error while sending ticket to Lottorisq - curl_exec failed");
        }

        $data = json_decode($response);
        if (isset($data->error) || !isset($data->id) || empty($data->id)) {
            if (
                $httpcode == "402" &&
                !$ltech_helper->check_lock_ltech() &&
                $ltech_details['whitelabel_ltech_record']['can_be_locked'] == 1
            ) {
                $ltech_helper->lock_ltech_sales($ltech_details, $lottery);

                $currencies = Helpers_Currency::getCurrencies();

                $lottorisqLogRepository->addWarningLog(
                    $whitelabel['id'],
                    $ticket->id,
                    $slip->id,
                    "L-TECH account was locked - not enough balance! Currency: " . $currencies[$lottery["currency_id"]]["code"],
                    [$response, $request],
                    $ltech_details['whitelabel_ltech_record']['id']
                );

                $ticketsIdsToSetIsLtechInsufficientBalance[] = $ticket->id;
                $currenciesIdsToSetIsLtechInsufficientBalance[] = $ticket->currency_id;
                return true;
            } elseif ($httpcode == Helpers_Ltech::INSUFFICIENT_BALANCE_HTTP_CODE) { // NOTE: to avoid spamming db with logs
                $ticketsIdsToSetIsLtechInsufficientBalance[] = $ticket->id;
                $currenciesIdsToSetIsLtechInsufficientBalance[] = $ticket->currency_id;
                return false; // NOTE: it's not checked by caller. But false is closer to truth
            }

            $errorcode = 1;
            throw new Exception("Error while sending ticket to Lottorisq " . var_export(
                [
                    'response' => $response,
                    'error_code' => $data->error->code ?? '',
                    'error_message' => $data->error->message ?? '',
                    'request_json' => $json,
                ],
                true
            ));
        }

        $risq_ticket = Model_Lottorisq_Ticket::forge();
        $risq_ticket->set([
            "whitelabel_user_ticket_slip_id" => $slip->id,
            "lottorisqid" => $data->id
        ]);
        $risq_ticket->save();
        $lottorisqLogRepository->addSuccessLog(
            $whitelabel['id'],
            $ticket->id,
            $slip->id,
            'Ticket (slip) created at Lottorisq system with id: ' . $data->id . '.',
            [$response, $request],
            $ltech_details['whitelabel_ltech_record']['id']
        );
    }

    /**
     *
     * @param Object $ticket
     * @param array  $lottery
     * @param array  $whitelabel
     *
     * @throws Exception
     */
    public static function process_imvalap_job($ticket, $lottery, $whitelabel)
    {
        Config::load("imvalap.ini", true);

        // in case of imvalap, we send slips in one packet, so all of them needs to be created in one transaction
        // so on error we just remove all slips, because we cannot assume they has been created

        $i = 0;
        $lines = Model_Whitelabel_User_Ticket_Line::find([
            "where" => [
                "whitelabel_user_ticket_id" => $ticket->id
            ],
            "order_by" => ["id" => "asc"]
        ]);

        $whitelabel_lottery = Model_Whitelabel_Lottery::find_for_whitelabel_and_lottery(
            $ticket['whitelabel_id'],
            $ticket['lottery_id']
        )[0];
        if ($lines !== null) {
            $slip = null;
            foreach ($lines as $line) {
                if ($i % $lottery['max_bets'] == 0) {
                    $slip = Model_Whitelabel_User_Ticket_Slip::forge();
                    $slip->set([
                        'whitelabel_user_ticket_id' => $ticket->id,
                        'whitelabel_lottery_id' => $whitelabel_lottery['id']
                    ]);
                    $slip->save();
                }
                $line->set([
                    'whitelabel_user_ticket_slip_id' => $slip->id
                ]);
                $line->save();
                $i++;
            }
        }

        $lines = Model_Whitelabel_User_Ticket_Line::find([
            "where" => [
                "whitelabel_user_ticket_id" => $ticket->id
            ],
            "order_by" => ["id" => "asc"]
        ]); // we want order by slip_id, but it's basically the same and id is faster

        $gameids = [
            1 => 8, // Powerball
            2 => 6, // Mega Millions
            3 => 3, // Eurojackpot
            5 => 15, // UK National Lottery
            6 => 1, // Euro Millions
        ];
        $gameid = $gameids[$lottery['id']];

        $xmlarr = [];
        foreach ($lines as $line) {
            $xmlarr[$line->whitelabel_user_ticket_slip_id][] = $line;
        }
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><bets></bets>');
        $xml->addAttribute("game", $gameid);
        $xml->addAttribute("date", $ticket->draw_date);
        $xml->addAttribute("totalbets", count($lines));

        foreach ($xmlarr as $key => $arrsub) {
            $subscription = $xml->addChild("subscription");
            $subscription->addAttribute("id", $ticket->id);
            $subscription->addAttribute("numbets", count($arrsub));
            $subscription->addAttribute("idslip", $key);
            foreach ($arrsub as $line) {
                $nums = explode(',', $line->numbers);
                $bnums = null;
                if (!empty($line->bnumbers)) {
                    $bnums = explode(',', $line->bnumbers);
                }
                $betstr = '';
                foreach ($nums as $num) {
                    $betstr .= sprintf('%02d', $num);
                }
                if ($bnums !== null) {
                    foreach ($bnums as $bnum) {
                        $betstr .= sprintf('%02d', $bnum);
                    }
                }
                $bet = $subscription->addChild('bet', $betstr);
            }
        }
        // insert bets into imvalap system
        $xmlstring = $xml->asXML();
        $token = Config::get("imvalap.tokens.game" . $gameid);
        $url = Config::get("imvalap.imvalap.url");
        $data = [
            "token" => $token,
            "data" => htmlentities($xmlstring)
        ];
        $curl = curl_init($url . 'insert_bets');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: text/xml', 'Cache-control: no-cache']);
        $response = curl_exec($curl);

        curl_close($curl);

        if ($response !== false) {
            libxml_use_internal_errors(true);
            $xmlres = new SimpleXMLElement(trim($response));
            if (!isset($xmlres->error) && isset($xmlres->jobid) && !empty($xmlres->jobid)) {
                // save jobid
                $job = Model_Imvalap_Job::find_by_jobid($xmlres->jobid);
                if ($job !== null && count($job)) {
                    $job = $job[0];
                } else {
                    $job = Model_Imvalap_Job::forge();
                    $job->set(["game_id" => $gameid, "jobid" => $xmlres->jobid, "status" => "107"]); // status opened
                    $job->save();
                }
                // let the system know, that we processed this ticket by creating an imvalap_subscription entry
                $sub = Model_Imvalap_Subscription::forge();
                $sub->set([
                    "whitelabel_user_ticket_id" => $ticket->id,
                    "imvalap_job_id" => $job->id
                ]);
                $sub->save();
                Model_Imvalap_Log::add_log(
                    Helpers_General::TYPE_SUCCESS,
                    $whitelabel['id'],
                    $ticket->id,
                    $job->id,
                    'Tickets added to the job.'
                );
            } else {
                // probably IP not authorized [save $xmlres->error in logs]
                $add = "";
                if (!empty($xmlres->error)) {
                    $add = ' [' . $xmlres->error . ']';
                }
                throw new Exception("Couldn't get job id from Imvalap" . $add . ".");
            }
        } else {
            // curl error
            $error = curl_error($curl);
            throw new Exception("CURL Error [" . $error . "].");
        }
    }

    /**
     *
     * @param Model_Whitelabel_User_Ticket $ticket
     *
     * @return void
     */
    public static function create_slips_for_ticket(Model_Whitelabel_User_Ticket $ticket): void
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        //Config::load("imvalap.ini", true);
        Config::load("lottorisq", true);

        $whitelabel = Model_Whitelabel::find_by_pk($ticket->whitelabel_id);
        $lotteries = Model_Lottery::get_really_all_lotteries_for_whitelabel($whitelabel);
        $ltech = Model_Whitelabel_Ltech::find([
            "where" => [
                "whitelabel_id" => $whitelabel->id,
                "is_enabled" => 1
            ]
        ]);

        $ltech_helper = new Helpers_Ltech($ltech !== null ? $ltech[0]['id'] : null);
        $ltech_details = $ltech_helper->get_ltech_details();

        if (!isset($lotteries['__by_id'][$ticket->lottery_id])) {
            $msg = "There is a problem with lottery settings. " .
                "No lottery within lotteries list. Lottery ID: " .
                $ticket->lottery_id . " " .
                "Ticket ID: " .
                $ticket->id . " " .
                "Whitelabel ID: " . $ticket->whitelabel_id;

            $fileLoggerService->error(
                $msg
            );

            // this should not fire normally
            // there is very small possibility if whitelabel or admin turns off the lottery
            // and user is in progress of payment
            return;
        }

        $lottery = $lotteries['__by_id'][$ticket->lottery_id];
        if ($lottery['provider'] != Helpers_General::PROVIDER_NONE) {            // not for insured/none
            switch ($lottery['provider']) {
                case Helpers_General::PROVIDER_IMVALAP:
                    $lottery = $lotteries['__by_id'][$ticket->lottery_id];
                    $ticket->set([
                        'lottery_provider_id' => $lottery['lp_id']
                    ]);
                    $ticket->save();

                    // moved to task/purchasetickets, see lottorisq below
                    //                    try {
                    //                        DB::start_transaction();
                    //                        self::process_imvalap_job($ticket, $lottery, $whitelabel);
                    //                        DB::commit_transaction();
                    //                    } catch (Exception $e) {
                    //                        DB::rollback_transaction();
                    //                        Model_Imvalap_Log::add_log(Helpers_General::TYPE_ERROR, $whitelabel['id'], $ticket->id, null, $e->getMessage());
                    //                    }
                    break;
                case Helpers_General::PROVIDER_LOTTERY_CENTRAL_SERVER:
                case Helpers_General::PROVIDER_LOTTORISQ: // lottorisq
                    // in case of lottorisq we want to process ticket slip by slip
                    // and if any slip fails, we need to retry to send only this one (in task/providerscheck)
                    // as others are good
                    $lottery = $lotteries['__by_id'][$ticket->lottery_id];

                    $i = 0;
                    $lines = Model_Whitelabel_User_Ticket_Line::find([
                        "where" => [
                            "whitelabel_user_ticket_id" => $ticket->id
                        ],
                        "order_by" => ["id" => "asc"]
                    ]);

                    $whitelabel_lottery = Model_Whitelabel_Lottery::find_for_whitelabel_and_lottery(
                        $ticket['whitelabel_id'],
                        $ticket['lottery_id']
                    )[0];

                    if ($lines !== null) {
                        $slip = null;
                        $previous_slip_id = null;
                        $slip_lines = [];

                        foreach ($lines as $line) {
                            $slip_lines[] = $line;
                            $i++;

                            if (
                                $i % $lottery['max_bets'] == 0 ||
                                count($lines) === $i
                            ) {
                                $slip_set = [
                                    'whitelabel_user_ticket_id' => $ticket->id,
                                    'whitelabel_lottery_id' => $whitelabel_lottery['id']
                                ];

                                if (
                                    $lottery['provider'] == Helpers_General::PROVIDER_LOTTORISQ
                                    && isset($ltech_details['ltech_id']) && !empty($ltech_details['ltech_id'])
                                ) {
                                    $slip_set['whitelabel_ltech_id'] = $ltech_details['ltech_id'];
                                }

                                $slip = Model_Whitelabel_User_Ticket_Slip::forge();
                                if (in_array($lottery['slug'], ['el-gordo-primitiva', 'la-primitiva'])) {
                                    $slip->additional_data = serialize(['refund' => Lotto_Helper::get_random_number()]);
                                }
                                if ($lottery['slug'] == 'lotto-6aus49') {
                                    $slip->additional_data = serialize(['super' => Lotto_Helper::get_random_number()]);
                                }
                                $slip->set($slip_set);
                                $slip->save();

                                foreach ($slip_lines as $slip_line) {
                                    $slip_line_set = [
                                        'whitelabel_user_ticket_slip_id' => $slip->id,
                                    ];

                                    $slip_line->set($slip_line_set);
                                    $slip_line->save();
                                }

                                // now process the slip
                                // DISABLED, now task/purchasetickets is covering it
                                // because of ticket draw date problem between our closing time and lottorisq closing time
                                $slip_lines = [];
                            }
                        }
                    }

                    $ticket_set = [
                        'lottery_provider_id' => $lottery['lp_id']
                    ];
                    $ticket->set($ticket_set);
                    $ticket->save();

                    break;
                default: // insured/none
                    break;
            }
        }
    }

    /**
     *
     * @param Model_Whitelabel_Transaction $transaction
     *
     * @return null
     */
    public static function create_slips(Model_Whitelabel_Transaction $transaction): void
    {
        if ((int)$transaction->status !== Helpers_General::STATUS_TRANSACTION_APPROVED) {
            return;
        }

        set_time_limit(0);
        $tickets = Model_Whitelabel_User_Ticket::find_by_whitelabel_transaction_id($transaction->id);
        if ($tickets !== null) {
            foreach ($tickets as $ticket) {
                self::create_slips_for_ticket($ticket);
            }
        }
    }

    /**
     *
     * @param array $caches
     */
    public static function clear_cache($caches = null)
    {
        if ($caches == null) {
            Cache::delete_all();
        } else {
            if (!is_array($caches)) {
                self::clear_cache_item($caches);
            } else {
                foreach ($caches as $cache) {
                    self::clear_cache_item($cache);
                }
            }
        }

        //      try {
        //            if ($caches == null) {
        //                Cache::delete_all();
        //            } else {
        //                if (!is_array($caches)) {
        //                    Cache::delete_all($caches);
        //                    Cache::delete($caches);
        //                } else {
        //                    foreach ($caches AS $cache) {
        //                        try {
        //                            Cache::delete_all($cache);
        //                            Cache::delete($cache);
        //                        } catch (Exception $e) {
        //                            echo $e->getMessage();
        //                            // do nothing
        //                        }
        //                    }
        //                }
        //            }
        //        } catch (Exception $e) {
        //            echo $e->getMessage();
        //            // do nothing <- we don't need to worry, Exceptions are fired when there's no data to delete
        //        }
    }

    /**
     *
     * @param string $url
     * @param array  $post
     * @param int    $timeout
     *
     * @return bool
     * @throws Exception
     */
    public static function load_API_url($url, $post, $timeout = 10)
    {
        //initialize curl
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        //WARN: can be enabled only in DEV ENV! Use curl .pem FILE instead!
        //well, dreams vs reality - we need reliability in download!!
        $ssl_verifypeer = 2;
        $ssl_verifyhost = 2;
        if (Helpers_General::is_development_env()) {
            $ssl_verifypeer = 0;
            $ssl_verifyhost = 0;
        }

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $ssl_verifypeer);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $ssl_verifyhost);

        //get answer
        $response = curl_exec($curl);

        if ($response === false) {
            throw new Exception(curl_error($curl));
        }

        if ($response) {
            return $response;
        }

        return false;
    }

    /**
     *
     * @param string $url
     * @param int    $timeout
     * @param bool   $accept
     * @param string $refer
     *
     * @return bool
     * @throws Exception
     */
    public static function load_HTML_url(
        $url,
        $timeout = 10,
        $accept = null,
        $refer = null
    ) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        //initialize curl
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0');
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // These statements prevent of rubbish within buffers
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($curl, CURLOPT_FORBID_REUSE, true);

        if ($accept !== null) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: ' . $accept]);
        }
        if ($refer !== null) {
            curl_setopt($curl, CURLOPT_REFERER, $refer);
        }
        $ssl_verifypeer = 2;
        $ssl_verifyhost = 2;
        if (Helpers_General::is_development_env()) {
            $ssl_verifypeer = 0;
            $ssl_verifyhost = 0;
        }

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $ssl_verifypeer);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $ssl_verifyhost);

        //get answer
        $response = curl_exec($curl);

        if ($response === false) {
            throw new Exception(curl_error($curl));
        }

        if ($response) {
            return mb_convert_encoding($response, 'HTML-ENTITIES', 'UTF-8');
        }

        return false;
    }

    public static function get_numbers_per_line_array(int $lottery_id): array
    {
        $numbers_per_line_data = Model_Lottery::get_numbers_per_line($lottery_id);
        $numbers_per_line_array = [];
        for ($i = $numbers_per_line_data['min']; $i <= $numbers_per_line_data['max']; $i++) {
            $numbers_per_line_array[] = $i;
        }

        return $numbers_per_line_array;
    }

    /**
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    private static function sort_timezone_list($a, $b)
    {
        if ($a[0] == $b[0]) {
            if ($a[2] == $b[2]) {
                return 0;
            }

            return ($a[2] < $b[2]) ? -1 : 1;
        }

        return ($a[0] < $b[0]) ? -1 : 1;
    }

    /**
     *
     * @param string $locale
     *
     * @return array
     */
    public static function get_timezone_list($locale = null)
    {
        $timezones = DateTimeZone::listIdentifiers();
        $list = [];
        $dt = new DateTime("now", new DateTimeZone("UTC"));
        foreach ($timezones as $timezone) {
            $dtzone = new DateTimeZone($timezone);
            $fullname = Lotto_View::format_time_zone($timezone, false, true, $locale);
            if ($fullname !== false) {
                $list[] = [$dtzone->getOffset($dt), $timezone, $fullname];
            }
        }
        usort($list, ['Lotto_Helper', 'sort_timezone_list']);

        $final = [];
        foreach ($list as $item) {
            $final[$item[1]] = $item[2];
        }

        return $final;
    }


    /**
     *
     * @param string $country
     *
     * @return string
     */
    public static function get_3_letter_country_code($country)
    {
        if (empty($country)) {
            return $country;
        }

        $time = filemtime(APPPATH . 'vendor/cldr/supplemental/supplementalData.xml');
        if (file_exists(APPPATH . 'vendor/cldr/countries-map-alpha3-' . $time . '.json')) {
            $map = json_decode(file_get_contents(APPPATH . 'vendor/cldr/countries-map-alpha3-' . $time . '.json'), true);

            return $map[$country];
        }
        $doc = new DOMDocument();
        $doc->load(APPPATH . 'vendor/cldr/supplemental/supplementalData.xml');
        $codes = $doc->getElementsByTagName('territoryCodes');
        $map = [];
        foreach ($codes as $code) {
            if (!empty($code->getAttribute('alpha3'))) {
                $map[$code->getAttribute('type')] = $code->getAttribute('alpha3');
            }
        }
        file_put_contents(
            APPPATH . 'vendor/cldr/countries-map-alpha3-' . $time . '.json',
            json_encode($map, JSON_UNESCAPED_UNICODE)
        );

        return $map[$country];
    }

    /**
     *
     * @return array
     */
    public static function get_telephone_prefix_list()
    {
        $time = filemtime(APPPATH . 'vendor/cldr/supplemental/telephoneCodeData.xml');
        if (file_exists(APPPATH . 'vendor/cldr/telephones-' . $time . '.json')) {
            return json_decode(file_get_contents(APPPATH . 'vendor/cldr/telephones-' . $time . '.json'), true);
        }
        $doc = new DOMDocument();
        $doc->load(APPPATH . 'vendor/cldr/supplemental/telephoneCodeData.xml');
        $countries = self::get_localized_country_list();
        $territories = $doc->getElementsByTagName('codesByTerritory');
        $prefixes = [];
        foreach ($territories as $territory) {
            $key = $territory->getAttribute('territory');
            $codes = $territory->getElementsByTagName('telephoneCountryCode');
            foreach ($codes as $code) {
                $prefixes[$key][] = $code->getAttribute('code');
            }
        }
        file_put_contents(
            APPPATH . 'vendor/cldr/telephones-' . $time . '.json',
            json_encode($prefixes, JSON_UNESCAPED_UNICODE)
        );

        return $prefixes;
    }

    /**
     * In fact at this function returns $countries - should it be like that?
     * Nothing functional happened here
     *
     * @param array $countries
     *
     * @return array
     */
    public static function filter_phone_countries(array $countries): array
    {
        // consider adding AC (Ascension Island), XK (Kosovo), 001 (World)
        // 001 & AC seems to be supported by libphonenumber
        // XK is supported via other countries in libphonenumber
        // none of them are in ISO, only AC is conditionally reserved
        // so we exclude them for now
        return $countries;
    }

    /**
     *
     * @param string $lang
     *
     * @return array
     */
    public static function get_localized_country_list(string $lang = null): array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $locale = $lang;
        if ($locale === null) {
            $locale = substr(Lotto_Settings::getInstance()->get("locale_default"), 0, 2);
        }

        $CLDR_folder = APPPATH . 'vendor/cldr';

        if (!file_exists($CLDR_folder . '/main/' . $locale . '.xml')) {
            $locale = 'en';
        }
        $main_file = $CLDR_folder . '/main/' . $locale . '.xml';
        $time = filemtime($main_file);

        $countries_file = $CLDR_folder . '/countries-' . $locale . '-' . $time . '.json';
        if (file_exists($countries_file)) {
            return json_decode(file_get_contents($countries_file), true);
        }

        $doc = new DOMDocument();
        $doc->load($main_file);

        $territories_from_tag = $doc->getElementsByTagName('territories');
        $territories = $territories_from_tag->item(0)->getElementsByTagName('territory');
        $countries = [];

        // generated with phantomjs crawler script (with subdivisions)
        $iso_countries = json_decode(file_get_contents(APPPATH . 'vendor/iso/scrap/countries.json'));
        // hack: add Kosovo
        if (!isset($iso_countries['XK'])) {
            $iso_countries[] = 'XK';
        }
        foreach ($territories as $territory) {
            $type = $territory->getAttribute('type');
            $alt = $territory->getAttribute('alt');
            $name = $territory->nodeValue;
            if (!is_numeric($type) && in_array($type, $iso_countries)) {
                if ($alt == 'variant') {
                    //$countries[$type] .= ' / '.$name;
                    // we don't need that ones neither
                } elseif ($alt == 'short') {
                    // we don't need that ones
                } else {
                    $countries[$type] = $name;
                }
            }
        }

        $collator = new Collator(Lotto_Settings::getInstance()->get("locale_default"));
        $collator->asort($countries);

        if (!(is_writable($CLDR_folder))) {
            try {
                throw new Exception("The main folder does not exist or is not writable: " . $CLDR_folder);
            } catch (Exception $e) {
                $fileLoggerService->error(
                    $e->getMessage()
                );
            }
        } else {
            file_put_contents($countries_file, json_encode($countries, JSON_UNESCAPED_UNICODE));
        }

        return $countries;
    }

    /**
     *
     * @param string $region
     * @param string $country
     *
     * @return bool
     */
    public static function check_region($region, $country)
    {
        // see Lotto_View for displaying list
        $subdivisions = json_decode(file_get_contents(APPPATH . 'vendor/iso/subdivisions.json'), true);
        if (empty($region)) {
            return true;
        }

        if (
            isset($subdivisions[$region]) &&
            $subdivisions[$region][0] == $country &&
            $subdivisions[$region][4] == 0
        ) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param string $width
     * @param array  $args
     *
     * @return string
     */
    public static function calculate_widget_width($width, $args)
    {
        $width = intval($width);
        $total_width = Lotto_Settings::getInstance()->get("widget_total_width");
        $current_width = $total_width;
        if ($total_width === null) {
            $total_width = 0;
        }
        $empty = false;
        if ($total_width == 0) {
            $empty = true;
        }
        $total_width += $width;
        $full = false;
        if ($total_width == 100) {
            $full = true;
            $total_width = 0;
        }
        Lotto_Settings::getInstance()->set("widget_total_width", $total_width);

        $sidebar_id = !empty($args['id']) ? $args['id'] : null;
        $widget_id = !empty($args['widget_id']) ? $args['widget_id'] : null;
        $widgets = wp_get_sidebars_widgets();
        $sidebar_widgets = !empty($widgets[$sidebar_id]) ? $widgets[$sidebar_id] : [];
        $key = array_search($widget_id, $sidebar_widgets);

        $margin_left = 15;
        $margin_right = 15;

        if ($full && $empty) {
            $margin_left = 0;
            $margin_right = 0;
        }

        $widgetHasSufficientData = $key !== false && isset($sidebar_widgets[$key - 1]);
        if ($widgetHasSufficientData) {
            if (
                $key === 0 ||
                strpos($sidebar_widgets[$key - 1], 'lotto_platform_widget_small') === false ||
                $empty
            ) {
                $margin_left = 0;
            } elseif (
                $key === count($sidebar_widgets) - 1 ||
                strpos($sidebar_widgets[$key + 1] ?? '', 'lotto_platform_widget_small') === false ||
                $full
            ) {
                $margin_right = 0;
            }
        }

        if (100 - $current_width < $width) {
            // no place for widget
            $width += $margin_left;
            $margin_left = 0;
        }

        $margins = $margin_left + $margin_right;

        $calc_text = "calc(" . $width . "%" .
            ($margins != 0 ? ' - ' . $margins . 'px' : '') .
            ')';
        $margin_left_text = $margin_left . 'px';
        $margin_right_text = $margin_right . 'px';

        return [
            $calc_text,
            $margin_left_text,
            $margin_right_text,
            $full
        ];
    }

    /**
     *
     * @param bool  $small
     * @param array $args
     */
    public static function widget_before($small = false, $args = [])
    {
        Lotto_Settings::getInstance()->set("widget_cnt", Lotto_Settings::getInstance()->get("widget_cnt") + 1);

        $settings = Lotto_Settings::getInstance();
        if ($settings->get("widget_check") === null) {
            $settings->set("widget_check", false);
        }
        if ($small == true) {
            if ($settings->get("widget_check") === false) {
                $settings->set("widget_check", true);
                include self::get_file_template('/widget/small/widgets-before.php');
                Lotto_Settings::getInstance()->set("widget_total_width", 0);
            }
        } else {
            if ($settings->get("widget_check") === true) {
                $settings->set("widget_check", false);
                include self::get_file_template('/widget/small/widgets-after.php');
            }
        }
    }

    /**
     *
     * @param string $file
     *
     * @return string
     */
    public static function get_file_template($file)
    {
        if (file_exists(get_stylesheet_directory() . $file)) {
            return get_stylesheet_directory() . $file;
        } else {
            return get_template_directory() . $file;
        }
    }

    /**
     *
     * @param int $sidebar_id
     *
     * @return string
     */
    public static function get_widget_home_area_classes($sidebar_id)
    {
        $classes = [];
        if (self::is_featured($sidebar_id, 'small', true)) {     // small on the top
            $classes[] = "widget-area-mobile-nmt";
        }
        if (self::is_featured($sidebar_id, 'large', true)) {     // large on the top
            $classes[] = "widget-area-nmt";
        }
        if (self::is_featured($sidebar_id, 'small', false)) {    // small on the bottom
            $classes[] = "widget-area-mobile-nmb";
        }
        if (self::is_featured($sidebar_id, 'large', false)) {    // large on the bottom
            $classes[] = "widget-area-nmb";
        }

        return (!empty($classes) ? ' ' . implode(" ", $classes) : '');
    }

    /**
     *
     * @param int $sidebar_id
     *
     * @return string
     */
    public static function get_widget_top_area_classes($sidebar_id)
    {
        $classes = [];
        if (self::is_featured($sidebar_id, 'small', true)) {     // small on the top
            //$classes[] = "widget-area-mobile-nmt";
            // always?
        }
        if (self::is_featured($sidebar_id, 'large', true)) {     // large on the top
            //$classes[] = "widget-area-nmt";
            // always?
        }
        if (self::is_featured($sidebar_id, 'small', false)) {    // small on the bottom
            $classes[] = "content-box-mobile-nmt";
        }
        if (self::is_featured($sidebar_id, 'large', false)) {    // large on the bottom
            $classes[] = "content-box-smt";
        }

        return (!empty($classes) ? ' ' . implode(" ", $classes) : '');
    }

    /**
     *
     * @param int $sidebar_id
     *
     * @return string
     */
    public static function get_widget_bottom_area_classes($sidebar_id)
    {
        $classes = [];
        if (self::is_featured($sidebar_id, 'small', true)) {     // small on the top
            $classes[] = "content-box-mobile-nmb";
        }
        if (self::is_featured($sidebar_id, 'large', true)) {     // large on the top
            $classes[] = "content-box-smb";
        }
        if (self::is_featured($sidebar_id, 'small', false)) {    // small on the bottom
            //$classes[] = "content-box-mobile-nmt";
        }
        if (self::is_featured($sidebar_id, 'large', false)) {    // large on the bottom
            //$classes[] = "content-box-nmt";
        }

        return (!empty($classes) ? ' ' . implode(" ", $classes) : '');
    }

    /**
     *
     * @param int $top_sidebar_id
     * @param int $bottom_sidebar_id
     *
     * @return string
     */
    public static function get_widget_main_area_classes($top_sidebar_id, $bottom_sidebar_id)
    {
        // TODO: also check if there is no widget, no margin?
        $classes = [];
        if (self::is_featured($bottom_sidebar_id, 'small', true)) {      // small on the top
            //$classes[] = "content-box-mobile-nmb";
        }
        if (self::is_featured($bottom_sidebar_id, 'large', true)) {      // large on the top
            //$classes[] = "content-box-nmb";
        }
        if (self::is_featured($bottom_sidebar_id, 'small', false)) {     // small on the bottom
            $classes[] = "content-area-mobile-nmb";
        }
        if (self::is_featured($bottom_sidebar_id, 'large', false)) {     // large on the bottom
            $classes[] = "content-area-nmb";
        }

        return (!empty($classes) ? ' ' . implode(" ", $classes) : '');
    }

    /**
     *
     * @param int $sidebar_id
     *
     * @return bool
     */
    public static function is_any_widget($sidebar_id)
    {
        $total_widgets = wp_get_sidebars_widgets();

        return $total_widgets[$sidebar_id] ? true : false;
    }

    /**
     *
     * @param int  $sidebar_id
     * @param int  $size
     * @param bool $first
     *
     * @return bool
     */
    public static function is_featured($sidebar_id, $size, $first = true)
    {
        if (empty($sidebar_id)) {
            return false;
        }

        $sizes = [
            "small" => 1,
            "large" => 2
        ];
        $size = $sizes[$size];
        // if first is featured widget, the large version does not have top padding
        // so we need to change the way padding are set up
        $total_widgets = wp_get_sidebars_widgets();
        if (!isset($total_widgets[$sidebar_id][0]) && $first) {
            return false;
        }
        if (!isset($total_widgets[$sidebar_id][count($total_widgets[$sidebar_id] ?? []) - 1]) && !$first) {
            return false;
        }

        if (empty($total_widgets[$sidebar_id][0])) {
            return false;
        }

        $first_widget = $total_widgets[$sidebar_id][0];

        $totalWidgetsCount = count($total_widgets[$sidebar_id]) - 1;
        if (!array_key_exists($totalWidgetsCount, $total_widgets[$sidebar_id])) {
            return false;
        }
        $last_widget = $total_widgets[$sidebar_id][$totalWidgetsCount];

        $type = explode("-", $first ? $first_widget : $last_widget);
        $id = $type[1];
        $type = $type[0];
        if ($type == "lotto_platform_widget_featured") {
            // now, let's check is it large one
            $settings = new Lotto_Widget_Featured();
            $settings = $settings->get_settings();

            $type = !empty($settings[$id]['type']) ? $settings[$id]['type'] : 1;

            if ($size == 2) {
                if ($type == 1) {
                    return false;
                }

                return true;
            } else {
                if ($type == 2) {
                    return false;
                }

                return true;
            }
        } elseif ($type == 'lotto_platform_widget_banner') {
            return $size != 2;
        }

        return false;
    }

    /**
     * AT THIS MOMENT THIS METHOD IS EMPTY BUT IT IS USED IN COUPLE PLACES
     * WITH WRONG AMOUNT OF PARAMS
     *
     * @param bool $small
     */
    public static function widget_after($small = false)
    {
    }

    /**
     *
     * @param int $sidebar_id
     */
    public static function widget_before_area($sidebar_id)
    {
        Lotto_Settings::getInstance()->set("widget_cnt", 0);
        $total_widgets = wp_get_sidebars_widgets();
        $count = count($total_widgets[$sidebar_id]);
        Lotto_Settings::getInstance()->set("widget_total_cnt", $count);
    }

    /**
     *
     * @param int $sidebar_id
     */
    public static function widget_after_area($sidebar_id)
    {
        $settings = Lotto_Settings::getInstance();
        if ($settings->get("widget_check") === true) {
            $settings->set("widget_check", false);
            include self::get_file_template('/widget/small/widgets-after.php');
        }
    }

    /**
     *
     * @return array
     */
    public static function get_cc_gateways()
    {
        return [1 => "eMerchantPay"];
    }

    /**
     * Whole functionality of this method is moved to new class named
     * Forms_Transaction_Accept
     *
     * @param Model_Whitelabel_Transaction $transaction
     * @param string                       $out_id
     * @param array                        $data
     * @param array                        $whitelabel
     *
     * @return int
     */
    public static function accept_transaction(
        Model_Whitelabel_Transaction $transaction = null,
        string $out_id = null,
        array $data = null,
        array $whitelabel = null
    ): int {
        $fileLoggerService = Container::get(FileLoggerService::class);

        if (!empty($data) && !is_array($data)) {
            $data = [$data];
        }

        try {
            $accept_transaction = new Forms_Transactions_Accept(
                $whitelabel,
                $transaction,
                $out_id,
                $data
            );

            $result = $accept_transaction->process_form();
        } catch (\Exception $e) {
            status_header(400);
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        return $result;
    }

    public static function acceptBonusBalanceTransaction(
        Model_Whitelabel_Transaction $transaction = null,
        string $out_id = null,
        array $additionalData = null,
        array $whitelabel = null,
        LotteryPurchaseLimitService $lotteryPurchaseLimitService
    ): int {
        $fileLoggerService = Container::get(FileLoggerService::class);
        try {
            $acceptTransaction = new Forms_Transactions_Accept(
                $whitelabel,
                $transaction,
                $out_id,
                $additionalData
            );
            $acceptTransaction->setLotteryPurchaseLimitService($lotteryPurchaseLimitService);

            $result = $acceptTransaction->process_form();
        } catch (\Exception $e) {
            status_header(400);
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        return $result;
    }

    /**
     *
     * @param WSSDK  $myWSSDK
     * @param string $customer_id
     * @param string $customer_email
     * @param array  $whitelabel
     * @param array  $transaction
     * @param array  $user
     * @param bool   $test_request
     */
    public static function e_merchant_pay_update_ccs(
        $myWSSDK,
        $customer_id,
        $customer_email,
        $whitelabel,
        $transaction,
        $user,
        $test_request
    ) {
        $ccs_customer = new \WSSDK\Model\CustomerGetCards($customer_id, $customer_email);
        $ccs = $myWSSDK->customerGetCardsRequest($ccs_customer, $test_request);
        $ccs = $ccs->send();

        $ccs_body = $ccs->getBody();
        $ccs_headers = $ccs->getHeaders();

        Model_Payment_Log::add_log(
            Helpers_General::TYPE_INFO,
            Helpers_General::PAYMENT_TYPE_CC,
            null,
            Helpers_Payment_Method::CC_EMERCHANT,
            $whitelabel['id'],
            $transaction->id,
            "Retrieved customer cards.",
            [
                $ccs_headers,
                var_export($ccs_body, true)
            ]
        );

        if ($ccs_body->num_records > 0 && !empty($ccs_body->card) && count($ccs_body->card) > 0) {
            foreach ($ccs_body->card as $card) {
                $ccorder_id = (string)$card->order_id;
                $cctype = (string)$card->type;
                $cccardnumber = (string)$card->card_number;
                $ccexpmonth = (string)$card->exp_month;
                $ccexpyear = (string)$card->exp_year;

                // does this card exists in our system?
                $res = Model_Emerchantpay_User_CC::find([
                    "where" => [
                        "whitelabel_user_id" => $user['id'],
                        "type" => $cctype,
                        "card_number" => $cccardnumber,
                        "exp_month" => intval($ccexpmonth),
                        "exp_year" => intval($ccexpyear)
                    ]
                ]);
                if ($res !== null && count($res) > 0) {
                    $res = $res[0];
                    // card exists in our system
                    // check if order differs
                    if ($ccorder_id != $res['order_id']) {
                        // before let's set others to not lastused
                        Model_Emerchantpay_User_CC::update_last_used($user['id']);
                        // the order differs, it means the user typed the same card data once again...
                        // let's update the order number in our database
                        // also why is he using the same data again? maybe he deleted the card earlier
                        $res->set(["order_id" => $ccorder_id, "is_deleted" => 0, "is_lastused" => 1]);
                        $res->save();
                    }
                } else {
                    // card does not exists, so we add it to our system
                    $newcard = Model_Emerchantpay_User_CC::forge();
                    $newcard->set([
                        "whitelabel_user_id" => $user['id'],
                        "order_id" => $ccorder_id,
                        "type" => $cctype,
                        "card_number" => $cccardnumber,
                        "exp_month" => $ccexpmonth,
                        "exp_year" => $ccexpyear,
                        "is_deleted" => 0,
                        "is_lastused" => 1
                    ]);
                    $newcard->save();
                }
            }
        }
    }

    /**
     *
     * @param string $month
     * @param string $year
     *
     * @return bool
     */
    public static function is_valid_card($month, $year)
    {
        // let's check date
        $month = intval($month);
        $year = intval($year);

        // support after 2069
        $dt = DateTime::createFromFormat("m y", $month . ' ' . $year, new DateTimeZone("UTC"));
        $days = cal_days_in_month(CAL_GREGORIAN, $dt->format('m'), $dt->format('Y'));

        // card is valid to last date of the month
        $dt = DateTime::createFromFormat(
            "d-m-Y H:i:s",
            $dt->format('d') . '-' . $dt->format('m') . '-' . $dt->format('Y') . ' 23:59:59',
            new DateTimeZone("UTC")
        );

        $now = new DateTime("now", new DateTimeZone("UTC"));
        if ($now > $dt) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param int $user_id
     *
     * @return array
     */
    public static function get_e_merchant_pay_saved_cards($user_id)
    {
        $saved_cards = Model_Emerchantpay_User_CC::find([
            "where" => [
                "whitelabel_user_id" => $user_id,
                "is_deleted" => 0
            ],
            "order_by" => ["id" => "ASC"]
        ]);
        $deleted = false;
        if (!empty($saved_cards)) {
            foreach ($saved_cards as $card) {
                if (!self::is_valid_card($card['exp_month'], $card['exp_year'])) {
                    $card->delete();
                    $deleted = true;
                }
            }
        }
        if ($deleted) {
            $saved_cards = Model_Emerchantpay_User_CC::find([
                "where" => [
                    "whitelabel_user_id" => $user_id,
                    "is_deleted" => 0
                ],
                "order_by" => ["id" => "ASC"]
            ]);
        }

        return $saved_cards;
    }

    /**
     *
     * @param string $ip
     *
     * @return bool|\GeoIp2\Model\Country
     */
    public static function get_geo_IP_record($ip)
    {
        spl_autoload_register(function ($class_name) {
            $class_name = str_replace("\\", DIRECTORY_SEPARATOR, $class_name);
            if (file_exists(APPPATH . 'vendor/geoip2/maxmind-db/src/' . $class_name . '.php')) {
                include APPPATH . 'vendor/geoip2/maxmind-db/src/' . $class_name . '.php';
            } elseif (file_exists(APPPATH . 'vendor/geoip2/geoip2/src/' . $class_name . '.php')) {
                include APPPATH . 'vendor/geoip2/geoip2/src/' . $class_name . '.php';
            }
        });

        try {
            $reader = new \GeoIp2\Database\Reader(APPPATH . 'vendor/geoip2/GeoIP2-Country.mmdb');
            $record = $reader->country($ip);

            return $record;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     *
     * @param string $ip
     *
     * @return bool|\GeoIp2\Model\City
     */
    public static function get_geo_IP_city_record($ip)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        spl_autoload_register(function ($class_name) {
            $class_name = str_replace("\\", DIRECTORY_SEPARATOR, $class_name);
            if (file_exists(APPPATH . 'vendor/geoip2/maxmind-db/src/' . $class_name . '.php')) {
                include APPPATH . 'vendor/geoip2/maxmind-db/src/' . $class_name . '.php';
            } elseif (file_exists(APPPATH . 'vendor/geoip2/geoip2/src/' . $class_name . '.php')) {
                include APPPATH . 'vendor/geoip2/geoip2/src/' . $class_name . '.php';
            }
        });

        try {
            $reader = new \GeoIp2\Database\Reader(APPPATH . 'vendor/geoip2/GeoIP2-City.mmdb');
            $record = $reader->city($ip);

            return $record;
        } catch (Exception $e) {
            $fileLoggerService->info(
                $e->getMessage()
            );

            return false;
        }
    }

    public static function check_user_aff(WhitelabelAff $whitelabelAff, WhitelabelUserAff $whitelabelUserAff): void
    {
        $affService = Container::get(AffService::class);
        $aff = $whitelabelAff->to_array();


        /** We saved whitelabel_aff_medium after user entered on generated link in app dashboard */
        if (!empty(Session::get('medium'))) {
            $medium_id = self::get_aff_medium_id($aff, Session::get('medium'));
            $whitelabelUserAff->set(['whitelabel_aff_medium_id' => $medium_id]);
        }

        /** We saved whitelabel_aff_campaign after user entered on generated link in app dashboard */
        if (!empty(Session::get('campaign'))) {
            $campaign_id = self::get_aff_campaign_id($aff, Session::get('campaign'));
            $whitelabelUserAff->set(['whitelabel_aff_campaign_id' => $campaign_id]);
        }

        /** We saved whitelabel_aff_content after user entered on generated link in app dashboard */
        if (!empty(Session::get('content'))) {
            $content_id = self::get_aff_content_id($aff, Session::get('content'));
            $whitelabelUserAff->set(['whitelabel_aff_content_id' => $content_id]);
        }

        $btag = $affService->getPropertyFromCookie('btag');

        if ($btag) {
            $whitelabelUserAff->set(['btag' => $btag]);
        }
    }

    /**
     *
     * @param array  $aff
     * @param string $medium
     *
     * @return int
     */
    public static function get_aff_medium_id($aff, $medium)
    {
        $isCasino = (bool) Input::get('is_casino', false) || IS_CASINO;

        $res = Model_Whitelabel_Aff_Medium::find_by([
            "whitelabel_aff_id" => $aff['id'],
            "medium" => $medium,
            'is_casino' => $isCasino,
        ]);

        if ($res !== null && count($res) > 0) {
            return $res[0]['id'];
        }

        $res = Model_Whitelabel_Aff_Medium::forge();
        $res->set([
            "whitelabel_aff_id" => $aff['id'],
            "medium" => $medium,
            'is_casino' => $isCasino,
        ]);
        $res->save();

        return $res->id;
    }

    public static function get_aff_campaign_id(array $aff, string $campaign): int
    {
        $isCasino = (bool) Input::get('is_casino', false) || IS_CASINO;

        $res = Model_Whitelabel_Aff_Campaign::find_by([
            "whitelabel_aff_id" => $aff['id'],
            "campaign" => $campaign,
            'is_casino' => $isCasino,
        ]);

        if ($res !== null && count($res) > 0) {
            return $res[0]['id'];
        }

        $res = Model_Whitelabel_Aff_Campaign::forge();
        $res->set([
            "whitelabel_aff_id" => $aff['id'],
            "campaign" => $campaign,
            'is_casino' => $isCasino,
        ]);
        $res->save();

        return $res->id;
    }

    public static function get_aff_content_id(array $aff, string $content): int
    {
        $isCasino = (bool) Input::get('is_casino', false) || IS_CASINO;

        $res = Model_Whitelabel_Aff_Content::find_by([
            "whitelabel_aff_id" => $aff['id'],
            "content" => $content,
            'is_casino' => $isCasino,
        ]);

        if ($res !== null && count($res) > 0) {
            return $res[0]['id'];
        }

        $res = Model_Whitelabel_Aff_Content::forge();
        $res->set([
            "whitelabel_aff_id" => $aff['id'],
            "content" => $content,
            'is_casino' => $isCasino,
        ]);
        $res->save();

        return $res->id;
    }

    public static function aff_count_click(array $aff, bool $unique = false)
    {
        $dt = new DateTime("now", new DateTimeZone("UTC"));
        $hour = $dt->format("H");
        $dt->setTime($hour, 0, 0);

        $medium = null;
        $campaign = null;
        $content = null;
        $where = [];

        /** We saved whitelabel_aff_medium after user entered on generated link in app dashboard */
        if (!empty(Session::get("medium"))) {
            $medium_id = self::get_aff_medium_id($aff, Session::get("medium"));
            $where["whitelabel_aff_medium_id"] = $medium_id;
        }

        /** We saved whitelabel_aff_c after user entered on generated link in app dashboard */
        if (!empty(Session::get("campaign"))) {
            $campaign_id = self::get_aff_campaign_id($aff, Session::get("campaign"));
            $where["whitelabel_aff_campaign_id"] = $campaign_id;
        }

        /** We saved whitelabel_aff_content after user entered on generated link in app dashboard */
        if (!empty(Session::get("content"))) {
            $content_id = self::get_aff_content_id($aff, Session::get("content"));
            $where["whitelabel_aff_content_id"] = $content_id;
        }

        $where["whitelabel_aff_id"] = $aff['id'];
        $where["date"] = $dt->format("Y-m-d H:i:s");

        $stat = Model_Whitelabel_Aff_Click::find([
            "where" => $where
        ]);

        if ($stat !== null && count($stat) > 0) {
            $stat = $stat[0];
        } else {
            $stat = Model_Whitelabel_Aff_Click::forge();
            $set = $where;
            $set["all"] = 0;
            $set["unique"] = 0;
            $stat->set($set);
            $stat->save();
        }

        if ($unique) {
            $stat->set(["unique" => $stat->unique + 1]);
        } else {
            $stat->set(["all" => $stat->all + 1]);
        }

        $stat->save();
    }

    /**
     * Body of this function is moved to Forms_Whitelabel_Aff_Commissions class
     * because in fact functionality is connected with that class - this is not
     * general functionality
     *
     * @param Model_Whitelabel_User $user
     * @param array                 $transaction
     * @param array                 $whitelabel
     * @param int                   $type
     */
    public static function count_aff_commission(
        $user,
        $transaction,
        $whitelabel,
        int $type
    ): void {
        $aff_commissions_obj = new Forms_Whitelabel_Aff_Commissions(
            $whitelabel,
            [],
            false
        );
        $aff_commissions_obj->calculateCommissionForAff(
            $user,
            $transaction,
            $type
        );
    }

    /**
     *
     * @param array $lottery
     */
    public static function update_estimated($lottery)
    {
        // step 1: get all lottery draw history

        $date = new DateTime("now", new DateTimeZone($lottery['timezone']));
        $type = Model_Lottery_Type::get_lottery_type_for_date($lottery, $date->format('Y-m-d'));
        $draws = Model_Lottery_Draw::find_by_lottery_type_id($type['id']); // all draws for current type

        $draws_ids = [];

        foreach ($draws as $draw) {
            $draws_ids[] = $draw['id'];
        }

        $type_estimated = [];

        // step 2: calculate average prize for each tier

        if (!empty($draws_ids) && count($draws_ids) > 0) {
            $sql = DB::query("SELECT * FROM lottery_prize_data WHERE lottery_draw_id IN (" . implode(",", $draws_ids) . ")");
            $res = $sql->execute()->as_array();

            foreach ($res as $row) {
                $td = $row['lottery_type_data_id'];
                if (!isset($type_estimated[$td])) {
                    $type_estimated[$td] = [0, 0];
                }

                if ($row['prizes'] != '0.00') {
                    $type_estimated[$td][0]++;
                    $type_estimated[$td][1] += $row['prizes'];
                }
            }
            // step 3: update estimated values to use by insurance calculation

            foreach ($type_estimated as $key => $value) {
                if ($value[0] == 0) {
                    continue;
                }
                $estimated = round($value[1] / $value[0], 2);

                $sql = DB::query("UPDATE lottery_type_data SET estimated = :estimated WHERE id = :id AND is_jackpot = 0 AND type = 1");
                $sql->param(":id", $key);
                $sql->param(":estimated", $estimated);
                $sql->execute();
            }

            $dblottery = Model_Lottery::find_by_pk($lottery['id']);
            $dblottery->estimated_updated = 1;
            $dblottery->save();

            Lotto_Helper::clear_cache(['model_lottery', 'model_lottery_prize_data' . $lottery['id'], 'lottery_type.lotterytypefordate.' . $lottery['id']]);
        } else {
            // TODO: some e-mail probably
            // draw type changed, estimated cannot be calculated, insurance is fucked up, what would lottorisq do?
        }
    }

    /**
     *
     * @param array $lottery
     * @param int   $to_currency_id
     *
     * @return string
     */
    public static function get_user_converted_price(
        array $lottery = null,
        int $to_currency_id = null,
        int $ticket_multiplier = 1
    ): string {
        if (is_null($lottery) || is_null($to_currency_id)) {
            return '0.00';
        }

        $to_currency_tab = Helpers_Currency::get_mtab_currency(
            true,
            null,
            $to_currency_id
        );

        $price = self::get_user_price($lottery);

        $converted_price = $price;
        if ((string)$lottery['currency'] !== (string)$to_currency_tab['code']) {
            $lottery_currency_tab = Helpers_Currency::get_mtab_currency(
                true,
                $lottery['currency']
            );

            $converted_price = Helpers_Currency::get_recalculated_to_given_currency(
                $price,
                $lottery_currency_tab,
                $to_currency_tab['code']
            );
        }

        $converted_price = round($converted_price * $ticket_multiplier, 2);

        return $converted_price;
    }

    /**
     * Get price of lottery plus income and (eventually) fee in lottery currency
     *
     * @param array $lottery
     *
     * @return float
     */
    public static function get_user_price($lottery)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $lottery_temp = [];
        $should_log = false;
        if (isset($lottery)) {
            $lottery_temp = $lottery;
        } else {
            $should_log = true;
        }

        // Those are default values in the case that at least one of them is null
        if (is_null($lottery['model'])) {
            $lottery_temp['model'] = 0;
            $should_log = true;
        }
        if (is_null($lottery['tier'])) {
            $lottery_temp['tier'] = 0;
            $should_log = true;
        }
        if (is_null($lottery['volume'])) {
            $lottery_temp['volume'] = 1000;
            $should_log = true;
        }
        if (is_null($lottery['income_type'])) {
            $lottery_temp['income_type'] = 0;
            $should_log = true;
        }
        if (is_null($lottery['income'])) {
            $lottery_temp['income'] = 1.00;
            $should_log = true;
        }
        // At this moment this is not default, but I assign 0 to fee to
        // avoid the error of no exist 'fee' value within table occured
        if (is_null($lottery['fee'])) {
            $lottery_temp['fee'] = 0;
            $should_log = true;
        }

        if ($should_log) {
            $message = "";
            if (!is_null($lottery['id'])) {
                $message .= "Id of the lottery: " . $lottery['id'] . ". ";
            } else {
                $message .= "Lottery variable has not ID and is probably empty. ";
            }
            if (isset($lottery)) {
                $message .= json_encode($lottery);
            }

            $fileLoggerService->error(
                "There is something wrong in lottery data. " . $message
            );
        }

        $lottery_in = $lottery_temp;

        $price = self::get_price(
            $lottery_in,
            $lottery_in['model'],
            $lottery_in['tier'],
            $lottery_in['volume']
        );

        $price = round($price[0] + $price[1], 2);

        if ($lottery_in['income_type'] == 0) {
            $price = round($price + $lottery_in['income'], 4);
        } else {
            //TODO: Test it
            $price += round($price * round($lottery_in['income'] / 100, 4), 4);
        }
        $ticket_price = round($lottery_in['price'] + $lottery_in['fee'], 2);

        if ($price < $ticket_price) {
            $price = $ticket_price;
        }

        return $price;
    }

    /**
     * Get price of lottery in lottery currency
     *
     * @param array $lottery
     * @param int   $model
     * @param int   $tiers
     * @param int   $volume
     *
     * @return array
     * @throws Exception
     */
    public static function get_price($lottery, $model, $tiers = null, $volume = 100000)
    {
        switch ($model) {
            case Helpers_General::LOTTERY_MODEL_PURCHASE:
                $fee = '0';
                if (!is_null($lottery['fee'])) {
                    $fee = $lottery['fee']; // It's actually float saved as string
                }

                return [round($lottery['price'] + $fee, 2), 0, 0]; // easy, huh? watch this
                break;
            case Helpers_General::LOTTERY_MODEL_MIXED: // insurance, mixed model
                if (self::should_insure($lottery, $tiers, $volume)) {
                    return array_merge(self::get_insurance_price($lottery, $tiers, $volume), [1]);
                }

                // we don't want to insure, return static price+fee
                return self::get_price($lottery, 0);
                break;
            case Helpers_General::LOTTERY_MODEL_PURCHASE_SCAN:
                $fee = 0;
                if (!is_null($lottery['fee'])) {
                    $fee = $lottery['fee'];
                }

                return [round($lottery['price'] + $fee, 2), 0, 0]; // easy, huh? watch this
                break;
            case Helpers_General::LOTTERY_MODEL_NONE:
                // no scan, no purchase, no insurance. Maximum risk
                return [0, 0, 0];
                break;
        }
        throw new Exception("Unexpected price type.");
    }

    /**
     *
     * @param array $lottery
     * @param int   $tiers
     * @param int   $volume
     *
     * @return bool|array
     */
    public static function get_insurance_price($lottery, $tiers, $volume = 100000)
    {
        Config::load("lottorisq", true);

        $multiple = Config::get("lottorisq.lottorisq.multiplier");
        $eur_currency_tab = Helpers_Currency::get_mtab_currency();

        $jackpot_multiplier = "1000000";
        $jackpot_eur_insurance = "150000000";

        $jackpot = round($lottery['current_jackpot'] * $jackpot_multiplier, 2);
        $jackpot_usd = round($lottery['current_jackpot_usd'] * $jackpot_multiplier, 2);
        $jackpot_eur = $jackpot;
        if ($eur_currency_tab['code'] !== $lottery['currency']) {
            $jackpot_eur = round($jackpot_usd * $eur_currency_tab['rate'], 2);
        }

        if ($lottery['provider'] != 1) {
            // other providers do not use insurance
            return false;
        }
        if ($jackpot_eur > $jackpot_eur_insurance) {
            // TODO: give whitelabel an option to insure even if the jackpot is higher than 150M EUR?
            // so the insurance will pay out only 150M out of total jackpot
            return false;
        }

        $type = Model_Lottery_Type_Data::get_lottery_type_data($lottery);

        $total_ins_price = 0.0;
        $total_total_wins_cost = 0.0;
        $total_wins_cost = 0;

        $type_counted = !empty($type) ? count($type) : 0;

        for ($i = 0; $i < $type_counted; $i++) {
            $item = $type[$i];
            $odds = $item['odds'];
            $prize = $item['is_jackpot'] ? $jackpot : $item['prize'];
            if (
                $item['type'] == Helpers_General::LOTTERY_TYPE_DATA_ESTIMATED &&
                !$item['is_jackpot'] && !empty($item['estimated'])
            ) {
                // use provided or previously calculated estimated information
                // this is for the prizes that are not constant and are subject to change (percentage)
                $prize = $item['estimated'];
            } elseif ($item['type'] == Helpers_General::LOTTERY_TYPE_DATA_QUICK_PICK) {
                // uk lottery quick pick
                // we can assume the prize is 2 GBP as this is the cost of the free ticket
                $prize = 2;
            }

            if ($i < $tiers) { // e.g. $tiers = 1, do only for $i = 0;
                $ins_price = round($prize * (1.0 / $odds) * $multiple, 2);
                $total_ins_price += $ins_price;
            } else { // for the non insured tiers calculate the average prize costs
                $wins = round((float)$volume / $odds); // >= 0.5 => 1
                if ($wins > 1 && $item['is_jackpot']) {
                    $wins = 1; // only one jackpot winner
                }
                $total_wins_cost = $wins * $prize;
                $total_total_wins_cost += $total_wins_cost;
            }
        }

        $total_total_wins_cost_div = round($total_ins_price / $volume, 2);

        return [
            $total_ins_price,
            $total_total_wins_cost_div
        ];
    }

    /**
     * the question: should we insure current tickets or purchase them?
     *
     * @param array $lottery
     * @param int   $tiers  tiers defined by each whitelabel
     * @param int   $volume volume based on the previous month data
     *
     * @return bool
     */
    public static function should_insure($lottery, $tiers, $volume = 100000)
    {
        // step 1: calculate insurance price
        $ins_cost = self::get_insurance_price($lottery, $tiers, $volume);
        if ($ins_cost === false) {
            return false;
        }
        $total_ins_add = $ins_cost[0] + $ins_cost[1];
        $total_ins_cost = $total_ins_add * $volume;

        // step 2: calculate ticket purchase costs
        $lottery_price_add = $lottery['price'] + $lottery['fee'];
        $tickets_cost = $volume * $lottery_price_add;

        // step 3: choose better approach
        if ($total_ins_cost >= $tickets_cost) {
            return false; // do not use insurance when cost is equal or greater than purchase
        }

        return true;
    }

    /**
     * TODO: export into proper place.
     * Get document root path.
     * NOTE: contains separator char at the end.
     *
     * @return string document root.
     */
    private static function get_document_root(): string
    {
        $fuel_root = DOCROOT;

        // get true root from fuel - discard platform
        return substr($fuel_root, 0, strpos($fuel_root, 'platform'));
    }

    /**
     * TODO: export into proper place, it can be a good trait or concrete helper.
     * Get theme directory path.
     *
     * @return string|null path to current theme or null on fatal failure.
     */
    public static function get_theme_directory(): ?string
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        // retrieve from whitelabel theme if possible
        // check if there is valid instance of whitelabel in settings
        if (
            ($whitelabel = Lotto_Settings::getInstance()->get("transaction_whitelabel")) !== null &&
            isset($whitelabel['theme'])
        ) {
            // success - now we have working instance of whitelabel - build path from it's theme
            $dir = self::get_document_root() . 'wordpress/wp-content/themes/';
            $dir .= $whitelabel['theme'];

            return $dir;
        }

        // check if we are in wordpress environment
        if (function_exists('get_stylesheet_directory')) {
            // wordpress env, get via wordpress function
            return get_stylesheet_directory();
        }

        $error_text = ' called in environment outside of wordpress failed, ' .
            'because there is no transaction_whitelabel in settings or ' .
            'it doesn\'t contain theme attribute! transaction_whitelabel ' .
            'should be set in accept_transaction method and this method ' .
            'should be called by hook.';

        // there is no usable whitelabel - log and result in null
        $fileLoggerService->error(
            __FUNCTION__ . $error_text
        );

        return null;
    }

    /**
     * Archetype of hooks.
     * Include file of given name
     *
     * @param string $name    name of the hook.
     * @param array  $globals array of globals in form array("keyName" => "value"...).
     *
     * @return bool true if file was successfully included.
     */
    private static function hook_(string $name, array $globals): bool
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        // get and validate file path
        if (($dir = self::get_theme_directory()) === null) { // check if dir is obtainable
            return false;
        }
        // dir is ok - create file path with it.
        $file_path = $dir . '/hooks/' . $name . '.php';
        if (!file_exists($file_path)) {
            return false; // file doesnt exist, return false
        }

        // try to include hook
        try {
            // register globals, if any
            foreach ($globals as $key => $value) {
                Lotto_Settings::getInstance()->set($key, $value);
            }
            include($file_path);
            // unregister globals, if any
            foreach ($globals as $key => $value) {
                Lotto_Settings::getInstance()->remove($key);
            }

            // success, return true
            return true;
        } catch (Exception $ex) {
            $fileLoggerService::error($ex->getMessage());

            // failure in hook execution, return false
            return false;
        }
    }

    /**
     * Include file of given name
     *
     * @param string $name
     *
     * @return bool true if file was successfully included.
     */
    public static function hook(string $name): bool
    {
        return self::hook_($name, []); // empty globals.
    }

    /**
     * Include file of given name, for lottomania only in PRODUCTION, for others hooks will be always executed, as with
     * other hook method.
     *
     * @param string $name    name of the hook.
     * @param array  $globals array of globals in form array("keyName" => "value"...).
     *
     * @return bool true if file was successfully included.
     */
    public static function hook_with_globals(string $name, array $globals): bool
    {
        // check if environment is production and execute hook.
        return self::hook_($name, $globals);
    }

    /**
     *
     * @param int $time_to_display
     *
     * @return int
     */
    public static function adjust_time_to_display($time_to_display)
    {
        $time_var_1 = 129600;   // 60 * 60 * (24 + 12)
        $time_var_2 = 43200;    // 60 * 60 * 12
        if ($time_to_display - time() >= $time_var_1) {
            $time_to_display = $time_to_display - $time_var_2;
        }

        return $time_to_display;
    }

    /**
     *
     * @param int $time_to_display
     *
     * @return int
     */
    public static function adjust_time_to_display_hours($time_to_display)
    {
        $time_var = 86400; // 24 * 60 * 60
        $days = floor(($time_to_display - time()) / $time_var);

        return time() + $days * $time_var;
    }

    /**
     *
     * @param string $command
     * @param int    $return_val
     * @param bool   $return_raw
     *
     * @return string
     */
    public static function execute_CLI(
        $command,
        &$return_val = null,
        $return_raw = false
    ): string {
        $output = [];
        $return_val = exec($command, $output);

        if (!$return_raw) {
            $output = implode("<br>", $output);
        } else {
            $output = implode($output);
        }

        return $output;
    }

    /**
     *
     * @param array  $whitelabel
     * @param string $file_name
     *
     * @return array
     */
    public static function get_wordpress_file_url_path(
        array $whitelabel,
        string $file_name
    ): array {
        // Generate file path
        $content_dir = 'wp-content/themes/' . strtolower($whitelabel['theme']);

        $path = self::get_document_root() . 'wordpress/' . $content_dir;
        $path .= '/' . $file_name;

        // Generate file url
        $url = UrlHelper::getHomeUrlWithoutLanguage('', $whitelabel['domain']) . '/';
        $url .= $content_dir;
        $url .= '/' . $file_name;

        return [
            'path' => $path,
            'url' => $url
        ];
    }

    /**
     *
     * @param Model_Whitelabel             $whitelabel
     * @param Model_Whitelabel_User_Ticket $ticket
     * @param array                        $lottery
     *
     * @return boolean
     */
    public static function check_us_state(
        Model_Whitelabel $whitelabel,
        Model_Whitelabel_User_Ticket $ticket,
        array $lottery
    ) {
        // Check if lottery is from USA
        if (
            !is_array($lottery) ||
            (is_array($lottery) && $lottery['country'] != "USA")
        ) {
            return false;
        }

        // Check if whitelabel have enabled sending us state information to l-tech
        if ($whitelabel->us_state_active == 0) {
            return false;
        }

        // Get geo location from user ticket IP
        $user_ip = $ticket->ip;
        $geo_ip = Lotto_Helper::get_geo_IP_city_record($user_ip);

        // First of fall, we would like to check if some variables are not empty/set
        if (
            empty($geo_ip->country->isoCode) ||
            empty($geo_ip->mostSpecificSubdivision->isoCode)
        ) {
            return false;
        }

        if ($geo_ip->country->isoCode != "US") {
            return false;
        }

        return $geo_ip->mostSpecificSubdivision->isoCode;
    }

    /**
     *
     * @return int
     */
    public static function get_random_number(int $from = 0, int $to = 9): int
    {
        return random_int($from, $to);
    }

    /**
     *
     * @return void
     */
    public static function multidraw_calculate(): void
    {
    }

    /**
     *
     * @param            $user
     * @param array|null $whitelabel
     *
     * @return string
     */
    public static function get_user_token($user, ?array $whitelabel = null): string
    {
        if (empty($whitelabel)) {
            $whitelabel = Container::get('whitelabel');
        }

        return $whitelabel['prefix'] . "U" . $user['token'];
    }

    /**
     *
     * @param Model_Whitelabel_Transaction $transaction
     *
     * @return string
     */
    public static function get_transaction_token(Model_Whitelabel_Transaction $transaction): string
    {
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $sign = (int)$transaction->type === Helpers_General::TYPE_TRANSACTION_PURCHASE ? "P" : "D";

        return $whitelabel['prefix'] . $sign . $transaction->token;
    }

    /**
     *
     * @param array $lottery
     *
     * @return null|array
     */
    public static function get_next_lottery_type($lottery)
    {
        if (Lotto_Helper::is_lottery_closed($lottery)) {
            $lottery_type = Model_Lottery_Type::get_lottery_type_for_date(
                $lottery,
                Lotto_Helper::get_lottery_next_draw($lottery, true, null, 2)->format(Helpers_Time::DATETIME_FORMAT)
            );
        } else {
            $lottery_type = Model_Lottery_Type::get_lottery_type_for_date(
                $lottery,
                Lotto_Helper::get_lottery_next_draw($lottery)->format(Helpers_Time::DATETIME_FORMAT)
            );
        }

        return $lottery_type;
    }

    /**
     *
     * @param array $lottery
     *
     * @return array
     * @throws Throwable
     */
    public static function get_warnings_for_lottery($lottery): array
    {
        $warnings = [];

        $now = new DateTime("now", new DateTimeZone("UTC"));
        $lottery_next_date_utc = $lottery['next_date_utc'];

        $next_draw_date_object = DateTime::createFromFormat(
            Helpers_Time::DATETIME_FORMAT,
            $lottery_next_date_utc,
            new DateTimeZone("UTC")
        );

        $before_next_date_utc = false;

        if ($now < $next_draw_date_object) {
            $before_next_date_utc = true;
        }

        $minimum_lines = $lottery['min_lines'] > 0 ? $lottery['min_lines'] : 1;

        /** @var Model_Whitelabel_Lottery $whitelabel_lottery */
        $whitelabel_lottery = Model_Whitelabel_Lottery::get_last_by_lottery_id($lottery['id']);

        /** @var Model_Lottery_Provider $lottery_provider */
        $lottery_provider = Model_Lottery_Provider::find_by_pk($whitelabel_lottery['lottery_provider_id']);

        $minimum_lines = max([$minimum_lines, $lottery_provider['min_bets']]);

        $lottery_is_closed = Lotto_Helper::is_lottery_closed($lottery);

        if (
            $minimum_lines > 1 ||
            $lottery['multiplier'] > 0 ||
            ($lottery_is_closed && $before_next_date_utc)
        ) {
            if ($lottery['multiplier'] > 0) {
                $warnings[] = Security::htmlentities(sprintf(_("You can order a multiple of %s lines only."), $lottery['multiplier']));
            } elseif ($minimum_lines > 1) {
                $multidrawMinLinesCount = in_array($lottery['slug'], Helpers_Lottery::MINI_LOTTERIES) ? 10 : 1;
                $warnings[] = Security::htmlentities(sprintf(_("Minimum lines for this lottery is %s.") . ' ' . _('For multi-draws it\'s %s.'), $minimum_lines, $multidrawMinLinesCount));
            }
        }

        if ($lottery_is_closed && $before_next_date_utc) {
            $warnings[] = Security::htmlentities(_("It's too late to buy a ticket for the nearest draw. Your order will be moved to the next draw!"));
        }

        return $warnings;
    }

    /**
     * Returns lotteries connected to the specified group
     *
     * @param int $group_id Lottery group
     *
     * @return array Lotteries in the group
     */
    public static function get_grouped_lotteries(int $group_id): array
    {
        $lotteries = lotto_platform_get_lotteries();
        $group_lotteries = Model_Lottery_Group::get_lotteries_for_group($group_id);
        $final_group_lotteries = [];
        if (!empty($group_lotteries)) {
            foreach ($group_lotteries as &$group) {
                if (!isset($lotteries["__by_id"][$group["lottery_id"]])) {
                    continue;
                }
                $glottery = $lotteries["__by_id"][$group["lottery_id"]];

                list(
                    $gtowin,
                    $gthousands
                ) = Lotto_View::get_jackpot_formatted_to_text(
                    $glottery["current_jackpot"],
                    $glottery["currency"],
                    Helpers_General::SOURCE_WORDPRESS,
                    $glottery["force_currency"]
                );

                $gpricing = lotto_platform_get_pricing($glottery);
                $gminimum_lines = $glottery['min_lines'] > 0 ? $glottery['min_lines'] : 1;

                $additional_fields = [];

                if ($glottery['multiplier'] != 0) {
                    $additional_fields[] = 'data-multiplier=' . $glottery['multiplier'];
                }
                $gltype = Lotto_Helper::get_next_lottery_type($glottery);

                $additional_fields[] = 'data-nrange="' . $gltype['nrange'] . '"';
                $additional_fields[] = 'data-ncount="' . $gltype['ncount'] . '"';
                $additional_fields[] = 'data-brange="' . $gltype['brange'] . '"';
                $additional_fields[] = 'data-bcount="' . $gltype['bcount'] . '"';
                $additional_fields[] = 'data-price="' . round($gpricing * 100, 2) . '"';
                $additional_fields[] = 'data-min="' . intval($gminimum_lines) . '"';
                $additional_fields[] = 'data-min_bets="' . intval($glottery['min_bets']) . '"';
                $additional_fields[] = 'data-max_bets="' . intval($glottery['max_bets']) . '"';

                $group['additional_fields'] = $additional_fields;
                $group['towin'] = $gtowin;
                $group['pricing'] = Lotto_View::format_currency($gpricing, lotto_platform_user_currency(), true);

                // TODO: move this conditions into get_lottery_real_next_draw()
                $now = Carbon::now();
                $next_date_local = Carbon::parse($glottery['next_date_local'], $glottery['timezone']);
                if (empty($glottery['next_date_local']) || $next_date_local > $now) {
                    $next_date_local = Lotto_Helper::get_lottery_real_next_draw($glottery);
                }

                $next_date_local->setTimezone(Lotto_Settings::getInstance()->get("timezone") ?? 'UTC');
                $group['next_draw_timestamp'] = $next_date_local->getTimestamp();

                $draw_in_human_time = sprintf(
                    _("draw in %s"),
                    human_time_diff($group['next_draw_timestamp'])
                );
                $group['draw_in_human_time_escaped'] = Security::htmlentities($draw_in_human_time);

                $group['warnings'] = Lotto_Helper::get_warnings_for_lottery($glottery);

                $final_group_lotteries[] = $group;
            }
        }

        return $final_group_lotteries;
    }

    /**
     * Check if user should have access to this controller based on URL
     *
     * @param string $routing_source Controller routing source
     *
     * @return bool The result
     */
    public static function allow_access(string $routing_source): bool
    {
        $url_source = Lotto_Settings::getInstance()->get("routing_source");
        if ($routing_source !== $url_source) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param int $whitelabel_id
     *
     * @return array
     */
    public static function get_selectable_whitelabel_groups_list(int $whitelabel_id): array
    {
        $groups = [];

        if (isset($whitelabel_id)) {
            $groups_array = Model_Whitelabel_User_Group::get_all_selectable_by_whitelabel($whitelabel_id);
            $default_whitelabel_user_group = Model_Whitelabel_User_Group::get_default_not_selectable_for_whitelabel($whitelabel_id);

            if (count($default_whitelabel_user_group) > 0) {
                array_push($groups_array, $default_whitelabel_user_group);
            }

            foreach ($groups_array as $item) {
                $groups[$item['id']] = $item;
            }
        }

        return $groups;
    }

    /**
     *
     * @param int $whitelabel_id
     *
     * @return array
     */
    public static function get_all_whitelabel_groups_list(int $whitelabel_id): array
    {
        $groups = [];

        if (isset($whitelabel_id)) {
            $groups_array = Model_Whitelabel_User_Group::get_all_groups_for_whitelabel($whitelabel_id);

            foreach ($groups_array as $item) {
                $groups[$item['id']] = $item;
            }
        }

        return $groups;
    }

    /**
     *
     * @param mixed  $lottery
     * @param string $draw_date
     *
     * @return \DateTime|null
     */
    public static function get_lottery_next_scheduled_draw($lottery, string $draw_date_str)
    {
        if (empty($lottery['draw_dates'])) {
            return null;
        }

        $new_date = null;
        $new_time_str = null;
        $new_weekday = null;

        $dhs = (array)json_decode($lottery['draw_dates']);
        // $dhs = ksort($dhs);

        $draw_date = new DateTime(
            $draw_date_str,
            new DateTimeZone($lottery['timezone'])
        );

        $draw_weekday = $draw_date->format('N');
        $draw_date_str = $draw_date->format(Helpers_Time::DATE_FORMAT);
        $draw_time_str = $draw_date->format(Helpers_Time::TIME_FORMAT);

        if (!in_array($draw_weekday, array_keys($dhs))) {
            return null;
        }

        $updated = false;

        // $dhs FORMAT [ "1" => ["14:00:00", "21:50:00"]]

        // Update if there's a lottery the same day
        foreach ($dhs[$draw_weekday] as $draw_hour) {
            if ($draw_time_str < $draw_hour) {
                $new_time_str = $draw_hour;
                $new_weekday = $draw_weekday;
                $updated = true;
                break;
            }
        }

        // If the value is not updated that means the draw is not on the same day
        if (!$updated) {
            foreach ($dhs as $dhs_idx => $draw_hours) {
                if ($dhs_idx <= $draw_weekday) {
                    continue;
                }

                $new_time_str = $draw_hours[0];
                $new_weekday = $dhs_idx;
                $updated = true;
                break;
            }
        }

        // If the value is not updated that means the draw is in the next week
        if (!$updated) {
            $new_weekday = min(array_keys($dhs)); // first draw in the week
            $draw_time_str = $dhs[$new_weekday][0];
        }

        $day_diff = 0;
        if ($new_weekday < $draw_weekday) {
            $day_diff = 7 - $draw_weekday + $new_weekday;
        } elseif ($new_weekday > $draw_weekday) {
            $day_diff = $new_weekday - $draw_weekday;
        }

        $new_date = new DateTime(
            $draw_date_str . " " . $new_time_str,
            new DateTimeZone($lottery['timezone'])
        );

        if ($day_diff > 0) {
            $new_date->modify("{$day_diff} days");
        }

        return $new_date;
    }

    /**
     * @param string $month
     *
     * @return string|null
     */
    public static function get_hungarian_month_number(string $month): ?string
    {
        $months = [
            "január" => '01',
            "február" => '02',
            "március" => '03',
            "április" => '04',
            "május" => '05',
            "június" => '06',
            "július" => '07',
            "augusztus" => '08',
            "szeptember" => '09',
            "október" => '10',
            "november" => '11',
            "december" => '12'
        ];
        $month = mb_strtolower($month);

        return isset($months[$month]) ? $months[$month] : null;
    }
}
