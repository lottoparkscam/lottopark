<?php

use Carbon\Carbon;
use Helpers\UrlHelper;
use Helpers\NumberHelper;
use Carbon\CarbonInterval;
use Services\MemoizeService;
use Services\Logs\FileLoggerService;
use Helpers\Wordpress\LanguageHelper;

class Lotto_View
{
    /**
     *
     * @return string
     */
    public static function query_vars()
    {
        $vars = Input::get();
        $query = '';
        if ($vars !== null && count($vars) > 0) {
            $query .= '?' . http_build_query($vars);
        }

        return $query;
    }

    /**
     *
     * @param bool $bool
     *
     * @return string
     */
    public static function show_boolean($bool)
    {
        if ($bool) {
            return '<span class="glyphicon glyphicon-ok"></span>';
        } else {
            return '<span class="glyphicon glyphicon-remove"></span>';
        }
    }

    /**
     *
     * @param bool $bool
     *
     * @return string
     */
    public static function show_boolean_class($bool)
    {
        if ($bool) {
            return 'text-success';
        } else {
            return 'text-danger';
        }
    }

    /**
     *
     * @param string $country
     *
     * @return string
     */
    public static function map_flags($country)
    {
        $map = [
            "Australia" => "au",
            "Austria" => "at",
            "Belarus" => "by",
            "Belgium" => "be",
            "Brazil" => "br",
            "Canada" => "ca",
            "Croatia" => "hr",
            "Czechia" => "cz",
            "Denmark" => "dk",
            "Estonia" => "ee",
            "Europe" => "eu",
            "Finland" => "fi",
            "France" => "fr",
            "Germany" => "de",
            "Greece" => "gr",
            "Hungary" => "hu",
            "Ireland" => "ie",
            "Italy" => "it",
            "Latvia" => "lv",
            "Lithuania" => "lt",
            "Norway" => "no",
            "Peru" => "pe",
            "Poland" => "pl",
            "Romania" => "ro",
            "Singapore" => "sg",
            "Slovakia" => "sk",
            "Spain" => "es",
            "Sweden" => "se",
            "UK" => "gb",
            "Ukraine" => "ua",
            "USA" => "us",
            "World" => "w",
            "Zambia" => "zm",
        ];

        return isset($map[$country]) ? $map[$country] : 'none';
    }

    /**
     *
     * @param string  $code
     *
     * @return string
     * @global Object $sitepress
     * 
     * @deprecated use LanguageHelper::getLanguageNameByLocale() instead
     */
    public static function get_language_name(string $code = '')
    {
        global $sitepress;
        $details = $sitepress->get_language_details($code);
        $language_name = $details['english_name'] ?? 'English';
        return $language_name;
    }

    /**
     *
     * @param string $language_code
     *
     * @return string
     */
    public static function format_language(string $language_code): string
    {
        return Locale::getDisplayLanguage(
            $language_code,
            Lotto_Settings::getInstance()->get("locale_default")
        );
    }

    /**
     *
     * @param string $currency_code
     * @param string $lang
     *
     * @return string
     */
    public static function format_currency_code(
        string $currency_code,
        string $lang = null
    ): string {
        $locale = Lotto_Settings::getInstance()->get("locale_default");

        if ($lang != null) {
            $locale = $lang;
        }

        $formatter = new NumberFormatter(
            $locale . "@currency=" . $currency_code,
            NumberFormatter::CURRENCY
        );
        $text = $formatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL);

        return self::manual_currency_adjust($text, $locale, $currency_code);
    }

    /**
     *
     * @param number $num
     *
     * @return string
     */
    public static function format_number($num)
    {
        $formatter = new NumberFormatter(
            Lotto_Settings::getInstance()->get("locale_default"),
            NumberFormatter::DECIMAL
        );

        return $formatter->format($num);
    }

    /**
     *
     * @param string      $phone
     * @param string|null $country
     *
     * @return int|null
     */
    public static function get_phone_country_code(
        string $phone = "",
        string $country = null
    ): ?int
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        if (empty($phone) || empty($country)) {
            return false;
        }

        $phone_util = \libphonenumber\PhoneNumberUtil::getInstance();

        try {
            $phone_util_number = $phone_util->parse($phone, $country);

            return $phone_util_number->getCountryCode();
        } catch (\libphonenumber\NumberParseException $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );

            return null;
        }
    }

    /**
     *
     */
//    public static function getPhoneType($phone, $country)
//    {
//        if (empty($phone) || empty($country)) {
//            return false;
//        }
//        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
//        try {
//            $pNumber = $phoneUtil->parse($phone, $country);
//            return $phoneUtil->getNumberType($pNumber);
//        } catch (\libphonenumber\NumberParseException $e) {
//            return false;
//        }
//    }

    public static function generate_region_list(string $selected, ?string $country = null, bool $viewall = false)
    {
        $subdivisions = json_decode(file_get_contents(APPPATH . 'vendor/iso/subdivisions.json'), true);
        self::show_region($subdivisions, "", $country, $selected, $viewall);
    }

    /**
     *
     * @param string $country
     *
     * @return bool
     */
    public static function regions_exist($country)
    {
        $subdivisions = json_decode(file_get_contents(APPPATH . 'vendor/iso/subdivisions.json'), true);
        foreach ($subdivisions as $item) {
            if ($item[0] == $country) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * @param string $region
     * @param bool   $showtype
     * @param bool   $dash_as_html_entity
     *
     * @return string
     */
    public static function get_region_name($region, $showtype = true, $dash_as_html_entity = true)
    {
        $dash_text = ' &ndash; ';
        $subdivisions = json_decode(file_get_contents(APPPATH . 'vendor/iso/subdivisions.json'), true);
        if (!isset($subdivisions[$region])) {
            return 'N/A';
        }
        if (!$dash_as_html_entity) {
            $dash_text = ' - ';
        }

        return $subdivisions[$region][2] . ($showtype ? $dash_text . $subdivisions[$region][1] : '');
    }

    /**
     *
     * @param array       $data
     * @param string      $parent
     * @param string      $country
     * @param string|null $selected
     * @param bool        $viewall
     */
    private static function show_region($data, $parent, $country, $selected, $viewall = false)
    {
        foreach ($data as $key => $item) {
            if ($parent == $item[3] && ($item[0] == $country || $viewall)) {
                if ($item[4] == 0) { // not a parent
                    $add = '';
                    if ($selected !== null && $selected == $key) {
                        $add = ' selected';
                    }
                    echo '<option value="' . $key . '" data-country="' . $item[0] . '"' . $add . '>';
                    echo $item[2] . ' &ndash; ' . $item[1];
                    echo '</option>';
                } else {
                    echo '<optgroup data-country="' . $item[0] . '" label="' . $item[2] . ' &ndash; ' . $item[1] . '">';
                    echo self::show_region($data, $key, $country, $selected, $viewall);
                    echo '</optgroup>';
                }
            }
        }
    }

    /**
     *
     * @param string      $phone
     * @param string|null $country
     * @param bool        $strip_code
     * @param int         $format
     *
     * @return string
     */
    public static function format_phone(
        $phone,
        $country,
        $strip_code = false,
        $format = \libphonenumber\PhoneNumberFormat::INTERNATIONAL
    ) {
        if (empty($phone) || empty($country)) {
            return $phone;
        }
        $fileLoggerService = Container::get(FileLoggerService::class);

        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        try {
            $pNumber = $phoneUtil->parse($phone, $country);
            $number = $phoneUtil->format($pNumber, $format);
            if ($strip_code) {
                $number = preg_replace('/^\+' . $pNumber->getCountryCode() . '\s?/', '', $number);
            }

            return $number;
        } catch (\libphonenumber\NumberParseException $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );

            return $phone;
        }
    }

    public static function get_jackpot_for_order(array $lottery): string
    {
        return self::get_jackpot_formatted_to_text(
            $lottery['current_jackpot'],
            $lottery['currency'],
            Helpers_General::SOURCE_WORDPRESS,
            $lottery['force_currency']
        )[0];
    }

    /**
     *
     * @param float       $current_jackpot
     * @param string      $currency_code
     *                                       if true the section will be return in 2 separated blocks (as array of
     *                                       those strings)
     * @param int         $source
     * @param string|null $override_currency Overrides currency for specific lotteries (used originally for gg-world)
     *
     * @return array
     * @throws Exception
     */
    public static function get_jackpot_formatted_to_text(
        $current_jackpot,
        string $currency_code,
        int $source = Helpers_General::SOURCE_WORDPRESS,
        ?string $override_currency = null
    ): array {
        /** @var MemoizeService $memoizeService */
        $memoizeService = Container::get(MemoizeService::class);
        $memoizeService->prepareArgs($current_jackpot, $currency_code, $source, $override_currency);
        $cachedResult = $memoizeService->findCachedResult();

        if (!empty($cachedResult)) {
            return $cachedResult;
        }

        $to_win = '';

        $translations = [
            'pending' => _('Pending'),
        ];

        if ($source !== Helpers_General::SOURCE_WORDPRESS) {
            $translations = Helpers_Lottery::get_jackpot_translations();
        }

        list(
            $local_value,
            $local_currency_code
            ) = self::get_currency_localized(
                $current_jackpot,
                $currency_code,
                false,
                $override_currency,
                8
            );

        list(
            $thousands,
            $local_value,
            $formatted_value,
            $formatted_currency_code,
            $show_in_front
            ) = self::get_jackpot_format(
                $local_value,
                $local_currency_code
            );

        if ($local_value == 0) {
            $to_win .= Security::htmlentities($translations['pending']);
        } else {
            if ((string)$local_currency_code !== (string)$currency_code) {
                $current_jackpot = $current_jackpot * 1000000;
                $current_jackpot_value_text = Lotto_View::format_currency(
                    $current_jackpot,
                    $currency_code
                );

                $to_win .= '<span class="tooltip tooltip-bottom local-amount" data-tooltip="' .
                    $current_jackpot_value_text . '">';
            }

            $local_value = $local_value * 1000000;

            $formatted_local_number_value = Lotto_View::format_currency($local_value, $local_currency_code);

            $to_win .= $formatted_local_number_value;

            if ((string)$local_currency_code !== (string)$currency_code) {
                $to_win .= '</span>';
            }
        }

        $result = [
            $to_win,
            $thousands,
        ];
        $memoizeService->addResultToCache($result);

        return $result;
    }

    /**
     *
     * @param float  $number_value
     * @param string $currency_code
     * @param bool   $withfraction      This is unused in that function
     * @param string $override_currency Overrides currency for specific lotteries (originally used for gg-world)
     * @param int    $decimals          Number of decimals after coma
     *
     * @return array
     */
    public static function get_currency_localized(
        $number_value,
        string $currency_code,
        bool $withfraction = false,
        ?string $override_currency = null,
        int $decimals = 2
    ): array {
        // All currencies mapped where currency_code is a key
        $currency_codes = Helpers_Currency::get_currency_map_by_code();

        // single row as array
        $currency_array = $currency_codes[$currency_code];

        $multiplier_usd = round(1 / $currency_array['rate'], Helpers_Currency::RATE_SCALE);
        $number_value_usd = round($number_value * $multiplier_usd, Helpers_Currency::RATE_SCALE);

        $final_currency_code = Helpers_Currency::get_final_currency_code();

        if (!empty($override_currency)) {
            $final_currency_code = $override_currency;
        }

        if ((string)$final_currency_code !== (string)$currency_code) {
            // single row as array
            $currency_array = $currency_codes[$final_currency_code];

            $number_usd_multi = round($number_value_usd * $currency_array['rate'], Helpers_Currency::RATE_SCALE);

            $final_number = $number_usd_multi;
        } else {
            $final_number = $number_value;
        }

        return [
            $final_number,
            $final_currency_code
        ];
    }

    /**
     *
     * @param int $lottery_id
     *
     * @return string
     */
    public static function get_lottery_image($lottery_id, $whitelabel = null, $prefix = 'lottery')
    {
        if (empty($whitelabel)) {
            $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        };

        $url = Lotto_Helper::get_wordpress_file_url_path($whitelabel, 'images/lotteries/' . $prefix . '_' . intval($lottery_id) . '.png');

        // Check if whitelabel doesn't have custom lottery logo
        if (file_exists($url['path'])) {
            return $url['url'];
        }

        // Get default lottery logo
        return UrlHelper::getHomeUrlWithoutLanguage() .
            '/wp-content/plugins/lotto-platform/public/images/lotteries/' .
            $prefix . '_' .
            intval($lottery_id) . '.png';
    }

    /**
     *
     * @param int $lottery_id
     *
     * @return string
     */
    public static function get_lottery_image_path($lottery_id, $whitelabel = null, $prefix = 'lottery')
    {
        if (empty($whitelabel)) {
            $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        }

        $url = Lotto_Helper::get_wordpress_file_url_path($whitelabel, 'images/lotteries/' . $prefix . '_' . intval($lottery_id) . '.png');

        // Check if whitelabel doesn't have custom lottery logo
        if (file_exists($url['path'])) {
            return $url['path'];
        }
        // Get default lottery logo
        return realpath(APPPATH . '../../../wordpress/wp-content/plugins/lotto-platform/public') .
            '/images/lotteries/' . $prefix . '_' .
            intval($lottery_id) . '.png';
    }

    /**
     *
     * @param string $payment_id
     * @param string $custom_logotype_url
     *
     * @return string
     */
    public static function get_payment_image(
        string $payment_id = "",
        string $custom_logotype_url = null
    ): string {
        $payment_image = "";

        if (!empty($custom_logotype_url)) {
            $payment_image = $custom_logotype_url;
        } elseif (!empty($payment_id)) {
            $payment_image = LOTTO_PLUGIN_URL .
                'public/images/payments/payment_' .
                $payment_id . '.png';
        }

        return $payment_image;
    }

    /**
     *
     * @param string $payment_id
     * @param string $custom_logotype_url
     *
     * @return bool
     */
    public static function check_payments_image(
        string $payment_id = "",
        string $custom_logotype_url = null
    ): bool {
        $result = false;

        $payment_path = "";
        if (!empty($custom_logotype_url)) {
            $payment_path = $custom_logotype_url;
        } elseif (!empty($payment_id)) {
            $payment_path = LOTTO_PLUGIN_DIR .
                'public/images/payments/payment_' .
                $payment_id . '.png';
        }

        if (!empty($custom_logotype_url) &&
            !empty($payment_path) &&
            file_get_contents($payment_path) !== false
        ) {
            $result = true;
        } elseif (file_exists($payment_path)) {
            $result = true;
        }

        return $result;
    }

    /**
     *
     * @param string|int  $number_value
     * @param string|null $currency_code
     *
     * @return string
     */
    public static function format_currency(
        $number_value,
        string $currency_code = null,
        bool $withfraction = false,
        string $lang = null,
        int $decimal_precision = 2,
        bool $trim_decimals = false,
        bool $remove_currency_symbol = false
    ): string
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        if (empty($currency_code)) {
            $trace_stack_string = "";

            try {
                throw new Exception();
            } catch (Exception $e) {
                $trace_stack_string = $e->getTraceAsString();
            }

            $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
            $additional_text = "";
            if (!empty($whitelabel)) {
                $additional_text = "WhitelabelID: " . $whitelabel['id'];
            }
            $error_text = "Empty currency code happened. " . $additional_text . ". ";
            $error_text .= "Stack trance: " . $trace_stack_string;

            $fileLoggerService->error(
                $error_text
            );

            return round($number_value, 2);
        }

        $locale = Lotto_Settings::getInstance()->get("locale_default") ?: 'en_GB.utf8';

        if ($lang != null) {
            $locale = $lang;
        }

        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        $result = $formatter->setTextAttribute(NumberFormatter::CURRENCY_CODE, $currency_code);

        if (!$result) {
            $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

            $additional_text = "Value: " . $number_value . " ";
            $additional_text .= "Currency code: " . $currency_code . " ";
            if (!empty($whitelabel)) {
                $additional_text .= "WhitelabelID: " . $whitelabel['id'];
            }

            $error_text = "Something happened during the process of formatting value. " . $additional_text;

            $fileLoggerService->error(
                $error_text
            );

            $number_value = round($number_value, 2);

            return $number_value;
        }

        if ($trim_decimals && NumberHelper::is_not_decimal($number_value)) {
            $decimal_precision = 0;
        }
        if (!$withfraction) {
            $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);
        } else {
            $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $decimal_precision);
        }

        // Not sure why it showed after merge, let's keep it for a while
        //
        //if ($num < 1) {
        //    $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 1);
        //}
        //

        if (is_string($number_value)) {
            $number_value = (float)$number_value;
        }

        if ($remove_currency_symbol) {
            $formatter->setSymbol(NumberFormatter::CURRENCY_SYMBOL, '');
        }

        $text = $formatter->formatCurrency($number_value, $currency_code);

        return self::manual_currency_adjust($text, $locale, $currency_code);
    }

    /**
     *
     * @param string $text
     * @param string $locale
     * @param string $currency_code
     *
     * @return string
     */
    public static function manual_currency_adjust(
        string $text,
        string $locale = null,
        string $currency_code = ""
    ): string {
        if (empty($locale)) {
            return $text;
        }

        if ($locale == "hr_HR.utf8" && $currency_code == "HRK") {
            $text = str_replace("HRK", "kn", $text);
        } elseif ($locale == "ka_GE.utf8" && $currency_code == "GEL") {
            $text = str_replace("GEL", "₾", $text);
        } elseif ($locale == "sr_Latn_RS.utf8" && $currency_code == "RSD") {
            $text = str_replace("RSD", "din.", $text);
        } elseif ($locale == "hu_HU.utf8" && $currency_code == "HUF") {
            $text = str_replace("HUF", "Ft", $text);
        } elseif ($locale == "th_TH.utf8" && $currency_code == "THB") {
            $text = str_replace("THB", "฿", $text);
        } elseif ($locale == "az_AZ.utf8" && $currency_code == "AZN") {
            $text = str_replace("AZN", "₼", $text);
        }
        // RO do not want Lei, they want EUR
//        elseif ($locale == "ro_RO.utf8" && $cur == "RON") {
//            $text = str_replace("RON", "Lei", $text);
//        }

        return $text;
    }

    /**
     *
     * @param string $local_number_value
     * @param string $local_currency_code
     *
     * @return array
     */
    public static function get_jackpot_format(
        $local_number_value,
        string $local_currency_code
    ): array {
        $thousands = false;

        if ($local_number_value >= 1000) {
            $thousands = true;
        }

        $formatted_value = self::format_currency($local_number_value, $local_currency_code);
        $formatted_currency_code = self::format_currency_code($local_currency_code);

        $show_in_front = false;
        if (strpos($formatted_value, $formatted_currency_code) == 0) {
            $show_in_front = true;
        }

//        $format = self::manual_currency_adjust($format, Lotto_Settings::getInstance()->get("locale_default"), $local_currency);
//        $format_cur = self::manual_currency_adjust($format_cur, Lotto_Settings::getInstance()->get("locale_default"), $local_currency);

        return [
            $thousands,
            $local_number_value,
            $formatted_value,
            $formatted_currency_code,
            $show_in_front
        ];
    }

    /**
     *
     * @param string $numbers_line
     * @param string $bonus_numbers_line
     * @param string $draw_numers_line
     * @param string $draw_bonus_numbers_line
     * @param int    $bextra
     * @param array  $additional_data
     * @param array  $additional_numbers
     *
     * @return string
     */
    public static function format_line(
        string $numbers_line = null,
        string $bonus_numbers_line = null,
        string $draw_numers_line = null,
        string $draw_bonus_numbers_line = null,
        int $bextra = null,
        array $additional_data = null,
        array $additional_numbers = null
    ): string {
        $numbers = null;
        $bonus_numbers = null;

        if (!empty($numbers_line)) {
            $numbers = explode(',', $numbers_line);
            asort($numbers);
        }

        if (!empty($bonus_numbers_line)) {
            $bonus_numbers = explode(',', $bonus_numbers_line);
            asort($bonus_numbers);
        }

        $draw_numers = null;
        if (isset($draw_numers_line)) {
            $draw_numers = explode(',', $draw_numers_line);
        }

        $draw_bonus_numbers = null;
        if (isset($draw_bonus_numbers_line)) {
            $draw_bonus_numbers = explode(',', $draw_bonus_numbers_line);
        }

        $str = '<div class="ticket-line">';

        if (!empty($numbers)) {
            foreach ($numbers as $single_number) {
                $add_class = '';
                $class = 'ticket-line-number';
                if (isset($draw_numers)) {
                    if (in_array($single_number, $draw_numers)) {
                        $add_class = ' ticket-line-number-win';
                    } elseif (((int)$bextra > 0 && in_array($single_number, $draw_bonus_numbers))
                    ) {
                        $add_class = ' ticket-line-number-win';
                        $class = 'ticket-line-bnumber';
                    } else {
                        $add_class = ' ticket-line-number-nowin';
                    }
                }
                $str .= <<<HTML

    <div class="$class$add_class">$single_number</div>
HTML;
            }
        }

        if (!empty($bonus_numbers)) {
            foreach ($bonus_numbers as $single_number) {
                $add_class = '';
                if (isset($draw_bonus_numbers)) {
                    if (in_array($single_number, $draw_bonus_numbers)) {
                        $add_class = ' ticket-line-number-win';
                    } else {
                        $add_class = ' ticket-line-number-nowin';
                    }
                }
                $str .= <<<HTML

    <div class="ticket-line-bnumber$add_class">$single_number</div>
HTML;
            }
        }

        if (!empty($additional_data)) {
            $is_manager_domain = Helpers_General::is_manager();
            $is_empire_domain = Helpers_General::is_empire();

            $key_name = '';
            $ball_name = '';
            $class_name = '';

            if (isset($additional_data['refund'])) { //TODO: refactor
                $key_name = 'refund';
                $ball_name = 'Reintegro';
                $ball_name_short = 'R';
                $class_name = 'reintegro';
            } elseif (isset($additional_data['super'])) {
                $key_name = 'super';
                $ball_name = 'Super';
                $ball_name_short = 'S';
                $class_name = 'super';
            }

            foreach ($additional_data as $single_number) {
                if (array_key_exists($key_name, $additional_data)) {
                    $add_class = $class_name;
                } else {
                    $add_class = '';
                }

                if (!empty($additional_numbers)) {
                    if ($single_number == $additional_numbers[$key_name]) {
                        $add_class .= ' ticket-line-number-win';
                    } else {
                        $add_class .= ' ticket-line-number-nowin';
                    }
                }

                if ($is_manager_domain || $is_empire_domain) {
                    $full_text = _($ball_name);      // So, I don't really know if it will work
                    $sign_text = _($ball_name_short);              // Here as well
                } else {
                    $full_text = Security::htmlentities(_($ball_name));
                    $sign_text = _($ball_name_short);
                }

                $str .= <<<HTML

<div data-tooltip="$full_text" class="a ticket-line-bnumber $add_class tooltip">
    $single_number
    <span>
        $sign_text
    </span>
</div>
HTML;
            }
        }

        $str .= '</div>';

        return $str;
    }

    /**
     *
     * @return int
     */
    public static function get_first_day_of_week()
    {
        $timestamp = strtotime('next Sunday');
        $utc = new DateTimeZone("UTC");
        $dt = new DateTime(null, $utc);
        $dt->setTimestamp($timestamp);
        $fmt = new IntlDateFormatter(
            Lotto_Settings::getInstance()->get("locale_default"),
            IntlDateFormatter::SHORT,
            IntlDateFormatter::SHORT,
            "UTC",
            IntlDateFormatter::GREGORIAN,
            "e"
        );
        $ret = $fmt->format($dt);

        return $ret == 7 ? 1 : 0;
    }

    /**
     *
     * @param $lottery
     *
     * @return string
     */
    public static function get_disabled_days_of_week($lottery)
    {
        return join(",", array_diff(
            array_keys(array_fill(1, 7, true)),
            explode(
                ",",
                self::get_highlighted_days_of_week($lottery)
            )
        ));
    }

    /**
     *
     * @param $lottery
     *
     * @return string
     */
    public static function get_highlighted_days_of_week($lottery)
    {
        $weekdays = [];
        $draw_dates = json_decode($lottery['draw_dates']);
        foreach ($draw_dates as $draw_date) {
            $draw_date = Carbon::parse($draw_date, $lottery['timezone']);
            $weekdays[$draw_date->weekday()] = true;
        }

        return join(",", array_keys($weekdays));
    }

    /**
     *
     * @param DateTime $date
     * @param DateTime $date2
     * @param string   $format
     *
     * @return string|bool
     */
    public static function date_diff($date, $date2, $format = '%d')
    {
        $interval = $date->diff($date2);

        return $interval->format($format);
    }

    /**
     *
     * @return array
     */
    public static function days_of_week()
    {
        $saved = Lotto_Settings::getInstance()->get("daysofweek");
        if ($saved != null) {
            return $saved;
        }
        $timestamp = strtotime('next Monday');
        $days = [];
        $utc = new DateTimeZone("UTC");
        for ($i = 1; $i <= 7; $i++) {
            $dt = new DateTime(null, $utc);
            $dt->setTimestamp($timestamp);
            $fmt = new IntlDateFormatter(
                Lotto_Settings::getInstance()->get("locale_default"),
                IntlDateFormatter::SHORT,
                IntlDateFormatter::SHORT,
                $utc,
                IntlDateFormatter::GREGORIAN,
                "E"
            );
            $days[$i] = $fmt->format($dt);
            $timestamp = strtotime('+1 day', $timestamp);
        }
        Lotto_Settings::getInstance()->set("daysofweek", $days);

        return $days;
    }

    /**
     *
     * @param array $draw_dates
     *
     * @return string
     */
    public static function get_days_of_week_by_draw_dates(array $draw_dates): array
    {
        $days_of_week = [];
        foreach ($draw_dates as $draw_date) {
            if ($draw_date instanceof Carbon === false) {
                $draw_date = Carbon::parse($draw_date);
            }
            $days_of_week[$draw_date->weekday()] = _($draw_date->dayName);
        }

        return array_values($days_of_week);
    }

    /**
     *
     * @return string
     */
    public static function get_user_timezone()
    {
        $is_user = Lotto_Settings::getInstance()->get("is_user");
        if ($is_user) {
            $user = Lotto_Settings::getInstance()->get("user");
            if (!empty($user['timezone'])) {
                return $user['timezone'];
            }
        }

        return 'UTC';
    }

    /**
     *
     * @param string $date_as_string
     * @param string $hour_as_string
     * @param string $timezone_as_string
     *
     * @return string
     */
    public static function format_date_with_hour(
        string $date_as_string,
        string $hour_as_string,
        string $timezone_as_string
    ): string {
        $timezone = new DateTimeZone($timezone_as_string);
        $date_time_user_timezone = new DateTimeZone(self::get_user_timezone());

        $date_time = new DateTime(
            $date_as_string . ' ' . $hour_as_string,
            $timezone
        );

        $date_time_formatted = new IntlDateFormatter(
            Lotto_Settings::getInstance()->get("locale_default"),
            IntlDateFormatter::LONG,
            IntlDateFormatter::LONG,
            $timezone,
            IntlDateFormatter::GREGORIAN
        );

        $date_time_formatted_user = new IntlDateFormatter(
            Lotto_Settings::getInstance()->get("locale_default"),
            IntlDateFormatter::LONG,
            IntlDateFormatter::LONG,
            $date_time_user_timezone,
            IntlDateFormatter::GREGORIAN
        );

        $date_time_user = clone $date_time;
        $date_time_user->setTimezone($date_time_user_timezone);

        $result = $date_time_formatted_user->format($date_time_user) .
            ' <span class="fa fa-clock-o tooltip" aria-hidden="true" data-tooltip="' .
            Security::htmlentities(_("<strong>Lottery Local Time:</strong>")) .
            ' ' .
            $date_time_formatted->format($date_time) .
            '"></span><span class="mobile-only-time">' .
            $date_time_formatted->format($date_time) .
            '</span>';

        return $result;
    }

    /**
     *
     * @param DateTime|Carbon $date_time
     * @param DateTimeZone    $timezone
     * @param DateTimeZone    $user_timezone
     *
     * @return string
     */
    public static function format_single_day_with_hour(DateTime $date_time, DateTimeZone $timezone, DateTimeZone $user_timezone)
    {
        $formatted_time = new IntlDateFormatter(
            Lotto_Settings::getInstance()->get("locale_default"),
            IntlDateFormatter::NONE,
            IntlDateFormatter::SHORT
        );

        $formatted_date_with_timezone = new IntlDateFormatter(
            Lotto_Settings::getInstance()->get("locale_default"),
            IntlDateFormatter::NONE,
            IntlDateFormatter::SHORT,
            $timezone,
            IntlDateFormatter::GREGORIAN,
            "EEEE " . $formatted_time->getPattern() . " zzz"
        );

        $formatted_date_with_timezone_user = new IntlDateFormatter(
            Lotto_Settings::getInstance()->get("locale_default"),
            IntlDateFormatter::NONE,
            IntlDateFormatter::SHORT,
            $user_timezone,
            IntlDateFormatter::GREGORIAN,
            "EEEE " . $formatted_time->getPattern() . " zzz"
        );

        $date_time_user = clone $date_time;
        $date_time_user->setTimezone($user_timezone);

        return $formatted_date_with_timezone_user->format($date_time_user) .
            ' <span class="fa fa-clock-o tooltip" aria-hidden="true" data-tooltip="' .
            Security::htmlentities(_("<strong>Lottery Local Time:</strong>")) .
            ' ' .
            $formatted_date_with_timezone->format($date_time) . '"></span><span class="mobile-only-time">' .
            _("<strong>Lottery Local Time:</strong>") .
            ' ' .
            $formatted_date_with_timezone->format($date_time) . '</span>';
    }

    /**
     *
     * @param int $integer
     *
     * @return string
     */
    public static function romanic_number(int $integer): string
    {
        $table = [
            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'CD' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1
        ];

        $return = '';
        while ($integer > 0) {
            foreach ($table as $rom => $arb) {
                if ($integer >= $arb) {
                    $integer -= $arb;
                    $return .= $rom;
                    break;
                }
            }
        }

        return $return;
    }

    /**
     *
     * @param float $percentage
     *
     * @return string
     */
    public static function format_percentage($percentage)
    {
        $formatter = new NumberFormatter(
            Lotto_Settings::getInstance()->get("locale_default"),
            NumberFormatter::PERCENT
        );
        $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 2);

        return $formatter->format($percentage);
    }

    /**
     *
     * @param string|null $last_date
     * @param string|null $timezone
     *
     * @return int This represents timestamp
     */
    public static function get_last_UTC_timestamp(
        string $last_date = null,
        string $timezone = null
    ): int {
        $date = null;
        if (empty($last_date) || empty($timezone)) {
            $date = Carbon::now();
        } else {
            $date = Carbon::parse($last_date, new DateTimeZone($timezone));
        }

        $date->setTimezone(new DateTimeZone("UTC"));

        return $date->timestamp;
    }

    /**
     *
     * @param int $type
     *
     * @return string
     */
    public static function type_to_class($type)
    {
        switch ($type) {
            case Helpers_General::TYPE_ERROR:
                return 'danger';
            case Helpers_General::TYPE_WARNING:
                return 'warning';
            case Helpers_General::TYPE_SUCCESS:
                return 'success';
            default:
                return 'info';
        }
    }

    /**
     *
     * @param int $type
     *
     * @return string
     */
    public static function type_to_name($type)
    {
        switch ($type) {
            case Helpers_General::TYPE_ERROR:
                return 'ERROR';
            case Helpers_General::TYPE_WARNING:
                return 'WARNING';
            case Helpers_General::TYPE_SUCCESS:
                return 'SUCCESS';
            default:
                return 'INFO';
        }
    }

    /**
     *
     * @param array $lottery
     *
     * @return int This represents timestamp
     */
    public static function next_real_draw_timestamp($lottery)
    {
        $date = Lotto_Helper::get_lottery_real_next_draw($lottery);
        $date->setTimezone(new DateTimeZone("UTC"));

        return $date->getTimestamp();
    }

    /**
     *
     * @param array $lottery
     *
     * @return int This represents timestamp
     */
    public static function next_draw_timestamp($lottery)
    {
        $date = Lotto_Helper::get_lottery_real_next_draw($lottery);
        $date->setTimezone('UTC');

        return $date->timestamp;
    }

    public static function next_draw_countdown($lottery): DateInterval
    {
        $date = Lotto_Helper::get_lottery_real_next_draw($lottery);
        $date->setTimezone('UTC');
        $now = $date->nowWithSameTz();

        return $date->diffAsCarbonInterval($now, true, ['years', 'months']);
    }

    /**
     *
     * @param array $lottery
     *
     * @return CarbonInterval
     * @throws Exception
     */
    public static function next_real_draw_countdown($lottery): CarbonInterval
    {
        $date = Lotto_Helper::get_lottery_real_next_draw($lottery);
        $date->setTimezone('UTC');
        $now = $date->nowWithSameTz();

        return $date->diffAsCarbonInterval($now);
    }

    /**
     *
     * @param string $date
     * @param int    $date_format
     * @param int    $time_format
     * @param string $format_pattern
     * @param string $timezone_in_for_formatter
     * @param string $timezone_in_for_date_time_zone
     *
     * @return string
     */
    public static function format_date_without_timezone(
        ?string $date,
        int $date_format = IntlDateFormatter::LONG,
        int $time_format = IntlDateFormatter::LONG,
        string $format_pattern = null,
        string $timezone_in_for_formatter = "UTC",
        string $timezone_in_for_date_time_zone = "UTC"
    ): string {
        $result = "";

        if (!$date) {
            return "";
        }

        $formatted_date = new IntlDateFormatter(
            Lotto_Settings::getInstance()->get("locale_default"),
            $date_format,
            $time_format,
            $timezone_in_for_formatter,
            IntlDateFormatter::GREGORIAN,
            $format_pattern
        );

        $date_time_formatted = new DateTime(
            $date,
            new DateTimeZone($timezone_in_for_date_time_zone)
        );

        if ($date_time_formatted === false) {
            return $result;
        }

        $formatted = $formatted_date->format($date_time_formatted);

        if ($formatted !== false) {
            $result = $formatted;
        }

        return $result;
    }

    /**
     *
     * @param int $date_format
     * @param int $time_format
     *
     * @return mixed
     */
    public static function get_date_pattern(
        int $date_format = IntlDateFormatter::LONG,
        int $time_format = IntlDateFormatter::LONG
    ) {
        $search = ["EEEE", "EEE", "EE", "E", "MMMM", "MMM", "MM", "M", "$", "yyyy", "yy", "y", "$", "&"];
        $replace = ["DD", "D", "D", "D", '$$', '$', "mm", "m", "M", "$$", "&", "yy", "y", "y"];

        $formatted_date = new IntlDateFormatter(
            Lotto_Settings::getInstance()->get("locale_default"),
            $date_format,
            $time_format,
            "UTC",
            IntlDateFormatter::GREGORIAN
        );

        return str_replace($search, $replace, $formatted_date->getPattern());
    }

    /**
     *
     * @param string $date
     * @param int    $date_format
     * @param int    $time_format
     * @param string $timezonein
     * @param bool   $use_user_timezone
     *
     * @return string
     */
    public static function format_date(
        string $date = null,
        int $date_format = IntlDateFormatter::LONG,
        int $time_format = IntlDateFormatter::LONG,
        string $timezonein = "UTC",
        bool $use_user_timezone = true,
        bool $useEnglishDateTranslating = false
    ): string {
        $result = "";

        if (empty($date)) {
            return $result;
        }

        $timezone = $timezonein;

        if ($use_user_timezone) {
            $timezone = Lotto_Settings::getInstance()->get("timezone");
        }

        $locale = $useEnglishDateTranslating ? 'en' : Lotto_Settings::getInstance()->get('locale_default');
        $formatted_date = new IntlDateFormatter(
            $locale,
            $date_format,
            $time_format,
            $timezone,
            IntlDateFormatter::GREGORIAN
        );

        $date_time_formatted = Carbon::parse($date, new DateTimeZone($timezonein));

        if ($date_time_formatted === false) {
            return $result;
        }
        $date_time_formatted->setTimezone($timezone);
        $formatted = $formatted_date->format($date_time_formatted);

        if ($formatted !== false) {
            $result = $formatted;
        }

        return $result;
    }

    /**
     *
     * @param string $time
     * @param int    $time_format
     *
     * @return string
     */
    public static function format_time(
        string $time,
        int $time_format = IntlDateFormatter::SHORT
    ): string {
        $result = "";
        $timezone = Lotto_Settings::getInstance()->get("timezone");

        $formetted_date = new IntlDateFormatter(
            Lotto_Settings::getInstance()->get("locale_default"),
            IntlDateFormatter::NONE,
            $time_format,
            $timezone,
            IntlDateFormatter::GREGORIAN
        );

        $date_time_formatted = DateTime::createFromFormat(
            "H:i:s",
            $time,
            new DateTimeZone("UTC")
        );

        if ($date_time_formatted === false) {
            return $result;
        }

        $formatted = $formetted_date->format($date_time_formatted);

        if ($formatted !== false) {
            $result = $formatted;
        }

        return $result;
    }

    /**
     *
     * @param string $timezone
     * @param bool   $short
     * @param bool   $exclude_wrong_timezones Unfortunately for some of the timezones IntlDateFormatter generate error
     * @param string $locale
     *
     * @return bool|string
     */
    public static function format_time_zone(
        $timezone,
        $short = false,
        $exclude_wrong_timezones = true,
        $locale = null
    )
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        // Whole feature to check if current timezone belongs to timezones
        // which could not be processes by IntlDateFormatter!
        if ($exclude_wrong_timezones) {
            $wrong_timezones = Helpers_General::get_all_wrong_timezones();
            if (in_array($timezone, $wrong_timezones)) {
                if ($short) {
                    return '';
                }

                return false;
            }
        }

        try {
            $dt = new DateTime("now", new DateTimeZone("UTC"));
            $dtzone = new DateTimeZone($timezone);
            $dt->setTimezone($dtzone);
            if (isset($locale)) {
                $locale_default = $locale;
            } else {
                $locale_default = Lotto_Settings::getInstance()->get("locale_default");
            }
            $fmt = new IntlDateFormatter(
                $locale_default,
                IntlDateFormatter::NONE,
                IntlDateFormatter::NONE,
                $dtzone,
                IntlDateFormatter::GREGORIAN,
                "zzz"
            );
            $fmtgmt = new IntlDateFormatter(
                $locale_default,
                IntlDateFormatter::NONE,
                IntlDateFormatter::NONE,
                $dtzone,
                IntlDateFormatter::GREGORIAN,
                $short ? "O" : "ZZZZ"
            );
            $fmtzn = new IntlDateFormatter(
                $locale_default,
                IntlDateFormatter::NONE,
                IntlDateFormatter::NONE,
                $dtzone,
                IntlDateFormatter::GREGORIAN,
                $short ? "VVV" : "VVVV"
            );
            $fmtzncity = new IntlDateFormatter(
                $locale_default,
                IntlDateFormatter::NONE,
                IntlDateFormatter::NONE,
                $dtzone,
                IntlDateFormatter::GREGORIAN,
                "VVV"
            );

            $localized = $fmtzn->format($dt);
            $format = $fmt->format($dt);
            if (false !== strpos($format, 'GMT')) {
                $format = '';
            } else {
                $format = ', ' . $format;
            }
            $format_city = $fmtzncity->format($dt);
            if (false !== strpos($localized, $format_city) ||
                false !== strpos($localized, 'GMT')
            ) {
                $format_city = '';
            } else {
                $format_city = ' - ' . $format_city;
            }

            $fullname = ($localized == 'GMT' ? '' : '[' . $fmtgmt->format($dt) . $format . '] ') . $localized . $format_city;

            return $fullname;
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );

            return false;
        }
    }

    /**
     *
     * @param string $numbers
     *
     * @return string
     */
    public static function format_numbers($numbers)
    {
        $numbers = explode(',', $numbers);
        $return = '';
        foreach ($numbers as $number) {
            $return .= '<span class="badge">' . $number . '</span> ';
        }

        return $return;
    }

    /**
     *
     * @param int $id
     *
     * @return string
     */
    public static function get_gateway_name($id)
    {
        $gateways = Lotto_Helper::get_cc_gateways();

        return isset($gateways[$id]) ? $gateways[$id] : 'N/A';
    }

    /**
     *
     * @param string $email
     *
     * @return string
     */
    public static function hide_email($email)
    {
        $email = explode("@", $email);
        for ($i = 1; $i < strlen($email[0]) - 1; $i++) {
            $email[0][$i] = '*';
        }

        return implode("@", $email);
    }

    /**
     * At this moment this function is for German language only
     *
     * @param string $time_text
     *
     * @return string
     */
    public static function shorten_time_translations($time_text)
    {
        $wlanguage = LanguageHelper::getCurrentWhitelabelLanguage();

        if (!isset($wlanguage['code'])) {
            return $time_text;
        }

        if ($wlanguage["code"] === "de_DE") {
            $time_text = str_replace(
                ["Tagen", "Stunden", "Minuten", "Sekunden"],
                ["Tage", "Std", "Min", "Sek"],
                $time_text
            );
        }

        if ($wlanguage["code"] === "cs_CZ") {
            $time_text = str_replace(
                ["hodiny", "minuty", "sekundy"],
                ["h", "m", "s"],
                $time_text
            );
        }

        if ($wlanguage["code"] === "sr_RS") {
            $time_text = str_replace(
                ["minuta", "sekundi"],
                ["min", "sek"],
                $time_text
            );
        }

        return $time_text;
    }

    /**
     * Get prepared draw date for widget
     *
     * @param $lottery
     *
     * @return string
     * @throws Exception
     */
    public static function get_formatted_date_for_widget($lottery)
    {
        $next_draw = new DateTime($lottery['next_date_utc'], new DateTimeZone("UTC"));
        $now = new DateTime("now", new DateTimeZone("UTC"));

        if ($next_draw <= $now) {
            return Security::htmlentities(_("Pending"));
        }

        $countdown = Lotto_View::next_draw_countdown($lottery);

        $next_draw_time = Lotto_View::next_draw_timestamp($lottery);
        $adjusted_next_draw = Lotto_Helper::adjust_time_to_display($next_draw_time);
        $adjusted_next_draw_h = Lotto_Helper::adjust_time_to_display_hours($next_draw_time);

        $adjusted_next_human = human_time_diff($adjusted_next_draw);
        $adjusted_next_human_h = human_time_diff($next_draw_time, $adjusted_next_draw_h);

        // Return +days if left more than 24 hours
        if ($countdown->invert && $countdown->d >= 1) {
            return self::shorten_time_translations($adjusted_next_human) . ' ' . self::shorten_time_translations($adjusted_next_human_h);
        }

        // Return just hours if left less than 24h
        return self::shorten_time_translations($adjusted_next_human_h);
    }

    /**
     * Add numbers from additional data and display additional numbers
     *
     * @param null|string $bnumbers
     * @param null|string $additional_data
     *
     * @return string
     */
    public static function display_additional_numbers(?string $bnumbers, ?string $additional_data): string
    {
        $additional_number = "";

        if (isset($additional_data)) {
            $additional_data = unserialize($additional_data);
            if (isset($additional_data['super'])) {
                $additional_number = $additional_data['super'];
            } elseif (isset($additional_data['refund'])) {
                $additional_number = $additional_data['refund'];
            }
        }

        return Lotto_View::format_numbers($bnumbers . "," . $additional_number);
    }

    /**
     *
     * @param string $draw_dates_json
     * @param string $timezone_as_string
     *
     * @return string
     * @throws Exception
     */
    public static function format_draw_dates(
        string $draw_dates_json,
        string $timezone_as_string
    ): string {
        $draw_dates = json_decode($draw_dates_json);
        $final = [];
        $timezone = new DateTimeZone($timezone_as_string);
        $user_timezone = new DateTimeZone(self::get_user_timezone()); // change to user timezone
        foreach ($draw_dates as $draw_date) {
            $date_time = Carbon::parse($draw_date, $timezone);

            $formatted_time = new IntlDateFormatter(
                Lotto_Settings::getInstance()->get("locale_default"),
                IntlDateFormatter::NONE,
                IntlDateFormatter::SHORT
            );

            $formatted_date_with_timezone = new IntlDateFormatter(
                Lotto_Settings::getInstance()->get("locale_default"),
                IntlDateFormatter::NONE,
                IntlDateFormatter::SHORT,
                $timezone,
                IntlDateFormatter::GREGORIAN,
                "EEEE " . $formatted_time->getPattern() . " zzz"
            );

            $formatted_date_with_timezone_user = new IntlDateFormatter(
                Lotto_Settings::getInstance()->get("locale_default"),
                IntlDateFormatter::NONE,
                IntlDateFormatter::SHORT,
                $user_timezone,
                IntlDateFormatter::GREGORIAN,
                "EEEE " . $formatted_time->getPattern() . " zzz"
            );

            $date_time_user = clone $date_time;
            $date_time_user->setTimezone($user_timezone);

            $final[] = $formatted_date_with_timezone_user->format($date_time_user) .
                ' <span class="fa fa-clock-o tooltip" aria-hidden="true" data-tooltip="' .
                Security::htmlentities(_("<strong>Lottery Local Time:</strong>")) .
                ' ' .
                $formatted_date_with_timezone->format($date_time) . '"></span><span class="mobile-only-time">' .
                _("<strong>Lottery Local Time:</strong>") .
                ' ' .
                $formatted_date_with_timezone->format($date_time) . '</span>';
        }

        return implode('<br>', $final);
    }

    public static function getKenoMaxPrizeConvertedToText(
        $kenoMaxPrize,
        string $currencyCode,
        ?string $overrideCurrency = null
    ): string {
        list(
                $localValue,
                $localCurrencyCode
                ) = Lotto_View::get_currency_localized(
                    $kenoMaxPrize,
                    $currencyCode,
                    false,
                    $overrideCurrency,
                    8
                );
        list(
                $thousands,
                $localValue,
                $formattedValue,
                $formattedCurrencyCode,
                $showInFront
                ) = Lotto_View::get_jackpot_format(
                    $localValue,
                    $localCurrencyCode
                );
        return $formattedValue;
    }
}
