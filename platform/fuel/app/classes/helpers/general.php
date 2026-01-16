<?php

use Helpers\Wordpress\LanguageHelper;
use Services\Logs\FileLoggerService;

/**@deprecated */
class Helpers_General
{
    const SOURCE_ADMIN = 1;
    const SOURCE_WHITELABEL = 2;
    const SOURCE_AFF = 3;
    const SOURCE_WORDPRESS = 4;
    const SOURCE_CRON = 5;

    /**
     * ORDER AREA
     */

    const ORDER_TICKET_SINGLE = 1;
    const ORDER_TICKET_MULTIDRAW = 2;

    /**
     * TRANSACTIONS
     */
    const TYPE_TRANSACTION_PURCHASE = 0;
    const TYPE_TRANSACTION_DEPOSIT = 1;

    const STATUS_TRANSACTION_PENDING = 0;
    const STATUS_TRANSACTION_APPROVED = 1;
    const STATUS_TRANSACTION_ERROR = 2;
    /**
     * END
     */

    /**
     * PAYMENT METHOD
     */
    /**
     * These values are important to cover the same values in DB
     */
    const PAYMENT_TYPE_BALANCE = 1;
    const PAYMENT_TYPE_CC = 2;
    const PAYMENT_TYPE_OTHER = 3;
    const PAYMENT_TYPE_BONUS_BALANCE = 4;
    const PAYMENT_TYPE_WELCOME_BONUS_BALANCE = 5;

    /**
     * END
     */

    /**
     * WHITELABEL
     */
    const WHITELABEL_ID_SPECIAL = 1;    // This is special id of lottopark.com

    const WHITELABEL_TYPE_V1 = 1;       // Our payments
    const WHITELABEL_TYPE_V2 = 2;       // Whitelabel payments

    const ACTIVATION_TYPE_NONE = 0;
    const ACTIVATION_TYPE_OPTIONAL = 1;
    const ACTIVATION_TYPE_REQUIRED = 2;

    const REGISTER_FIELD_NONE = 0;
    const REGISTER_FIELD_OPTIONAL = 1;
    const REGISTER_FIELD_REQUIRED = 2;
    public const DISPLAY_REGISTER_FIELD_VALUES = [self::REGISTER_FIELD_OPTIONAL, self::REGISTER_FIELD_REQUIRED];
    /**
     * END
     */

    /**
     * USER TICKETS
     */
    const TICKET_UNPAID = 0;
    const TICKET_PAID = 1;

    const TICKET_STATUS_PENDING = 0;
    const TICKET_STATUS_WIN = 1;
    const TICKET_STATUS_NO_WINNINGS = 2;
    const TICKET_STATUS_QUICK_PICK = 3;
    const TICKET_STATUS_CANCELED = 4;

    const TICKET_PAYOUT_PENDING = 0;
    const TICKET_PAYOUT_PAIDOUT = 1;
    /**
     * END
     */

    /**
     * USER SALE STATUS
     */
    const SALE_STATUS_NONE = 0;
    const SALE_STATUS_STARTED_DEPOSIT = 1;
    const SALE_STATUS_DEPOSITED = 2;
    const SALE_STATUS_STARTED_PURCHASE = 3;
    const SALE_STATUS_PURCHASED = 4;

    /**
     * END
     */

    /**
     * CC METHOD - this is moved to Helpers_Payment_Method
     */
    //const CC_METHOD_EMERCHANT = 1;
    /**
     * END
     */

    /**
     * LOGS
     */
    const TYPE_INFO = 0;
    const TYPE_SUCCESS = 1;
    const TYPE_WARNING = 2;
    const TYPE_ERROR = 3;
    /**
     * END
     */

    /**
     * PROVIDERS
     */
    const PROVIDER_IMVALAP = 0;
    const PROVIDER_LOTTORISQ = 1;
    const PROVIDER_NONE = 2;
    const PROVIDER_LOTTERY_CENTRAL_SERVER = 3;
    const PROVIDER_FEED = 4;
    /**
     * END PROVIDERS
     */

    /**
     * LOTTERY_TYPE_DATA
     */
    const LOTTERY_TYPE_DATA_PRIZE = 0;
    const LOTTERY_TYPE_DATA_ESTIMATED = 1;
    const LOTTERY_TYPE_DATA_QUICK_PICK = 2;
    /**
     * END LOTTERY_TYPE_DATA
     *
     * /**
     * WHITELABEL_LOTTERY
     */
    const LOTTERY_MODEL_PURCHASE = 0;
    const LOTTERY_MODEL_MIXED = 1;
    const LOTTERY_MODEL_PURCHASE_SCAN = 2;
    const LOTTERY_MODEL_NONE = 3;

    const LOTTERY_INCOME_TYPE_CURRENCY = 0;
    const LOTTERY_INCOME_TYPE_PERCENT = 1;
    /**
     * WHITELABEL_LOTTERY
     */

    /**
     * AFFS
     */
    const TYPE_AFF_SALE = 0;
    const TYPE_AFF_FTP = 1;

    const TYPE_TIER_FIRST = 1;
    const TYPE_TIER_SECOND = 2;

    const COOKIE_AFF_NAME = 'ref';
    const COOKIE_AFF_TAG_MARKETING_TRANSACTION_ID = 'TagMarketingTransactionId';
    const COOKIE_AFF_DIGITAL_HUB_DATA = 'DigitalHubData';
    const COOKIE_AFF_TIBOLARIO = 'AffTibolario';
    const COOKIE_AFF_LOUDING_ADS = 'AffLoudingAds';
    const COOKIE_AFF_TAGD = 'TAGD';

    const REF_DIGITAL_HUB = '4a5147fabf';
    const REF_TAG_MARKETING = 'baa9e65df0';
    const REF_TIBOLARIO = 'ca113b4790';
    const REF_LOUDING_ADS = 'c5f6e7ae28';
    const REF_TAGD = '8d13794b55';

    /**
     * These constants are needed to discover
     * which list of payment methods have to be processed
     */
    const IS_DESKTOP = 1;
    const IS_MOBILE = 2;
    /**
     * END
     */

    /**
     * PROMO CODES
    */
    const PROMO_CODE_TYPE_PURCHASE = 0;
    const PROMO_CODE_TYPE_DEPOSIT = 1;
    const PROMO_CODE_TYPE_REGISTER = 2;
    const PROMO_CODE_TYPE_PURCHASE_DEPOSIT = 3;
    const PROMO_CODE_TYPE_PURCHASE_REGISTER = 4;
    const PROMO_CODE_TYPE_DEPOSIT_REGISTER = 5;
    const PROMO_CODE_TYPE_PURCHASE_DEPOSIT_REGISTER = 6;

    const PROMO_CODE_BONUS_TYPE_FREE_LINE = 0;
    const PROMO_CODE_BONUS_TYPE_DISCOUNT = 1;
    const PROMO_CODE_BONUS_TYPE_BONUS_MONEY = 2;

    const PROMO_CODE_DISCOUNT_TYPE_PERCENT = 0;
    const PROMO_CODE_DISCOUNT_TYPE_AMOUNT = 1;
    
    const PROMO_CODE_BONUS_BALANCE_TYPE_PERCENT = 0;
    const PROMO_CODE_BONUS_BALANCE_TYPE_AMOUNT = 1;
    /**
     * END
     */

    /**
     * CONNECTION
     */
    public const GUZZLE_TIMEOUT_IN_SECONDS = 10.0;
    /**
     * END
     */

    /**
     *
     * @return bool
     */
    public static function is_manager(): bool
    {
        $http_host_text = str_replace('www.', '', $_SERVER['HTTP_HOST']);
        $domain = explode('.', $http_host_text);

        $result = false;
        if ((string)$domain[0] === "manager") {
            $result = true;
        }

        return $result;
    }

    /**
     *
     * @return bool
     */
    public static function is_empire(): bool
    {
        $http_host_text = str_replace('www.', '', $_SERVER['HTTP_HOST']);
        $domain = explode('.', $http_host_text);

        $result = false;
        if ((string)$domain[0] === "empire") {
            $result = true;
        }

        return $result;
    }

    /**
     *
     * @return string
     */
    public static function get_domain(): string
    {
        $is_cli = (bool) defined('STDIN');
        $domain_input = "";
        if ($is_cli) {
            if (\Fuel::$env == \Fuel::PRODUCTION) {
                $domain_input = "whitelotto.com";
            } elseif (\Fuel::$env == \Fuel::STAGING) {
                $domain_input = "whitelotto.work";
            } else {
                $domain_input = "whitelotto.loc";
            }
        } else {
            $domain_input = $_SERVER['HTTP_HOST'];
        }
        
        $domain_table = explode('.', $domain_input);
        if ($domain_table[0] == "www") {
            array_shift($domain_table);
        }
        
        $domain = implode('.', $domain_table);
        
        return $domain;
    }

    /**
     *
     * @return bool
     */
    public static function is_development_env(): bool
    {
        // Put here all Environment consts that should not be treated as DEVELOPMENT
        $list_of_env = [
            \Fuel::TEST,
            \Fuel::STAGING,
            \Fuel::PRODUCTION
        ];

        if (!in_array(\Fuel::$env, $list_of_env)) {
            return true;
        }

        return false;
    }

    /**
     *
     * @return bool
     */
    public static function is_test_env(): bool
    {
        if (\Fuel::$env === \Fuel::TEST) {
            return true;
        }
        
        return false;
    }

    public static function get_ticket_statuses(): array
    {
        $ticket_statuses = [
            Helpers_General::TICKET_STATUS_PENDING => _("Pending"),
            Helpers_General::TICKET_STATUS_WIN => _("Win"),
            Helpers_General::TICKET_STATUS_NO_WINNINGS => _("No winnings"),
            Helpers_General::TICKET_STATUS_QUICK_PICK => _("Quick Pick"),
            Helpers_General::TICKET_STATUS_CANCELED => _("Cancelled")
        ];

        return $ticket_statuses;
    }

    /**
     *
     * @return array
     */
    public static function get_ticket_payouts(): array
    {
        $ticket_payouts = [
            Helpers_General::TICKET_PAYOUT_PENDING => _("No"),
            Helpers_General::TICKET_PAYOUT_PAIDOUT => _("Yes")
        ];

        return $ticket_payouts;
    }

    /**
     *
     * @return array
     */
    public static function get_activation_types(): array
    {
        $activation_types = [
            Helpers_General::ACTIVATION_TYPE_NONE => _("No activation"),
            Helpers_General::ACTIVATION_TYPE_OPTIONAL => _("Activation optional"),
            Helpers_General::ACTIVATION_TYPE_REQUIRED => _("Activation required")
        ];

        return $activation_types;
    }

    /**
     *
     * @return array
     */
    public static function get_all_wrong_timezones()
    {
        $wrong_timezones = [
            "America/Fort_Nelson",
            "America/Nuuk",
            "America/Punta_Arenas",
            "Asia/Atyrau",
            "Asia/Barnaul",
            "Asia/Famagusta",
            "Asia/Tomsk",
            "Asia/Yangon",
            "Asia/Qostanay",
            "Europe/Astrakhan",
            "Europe/Kirov",
            "Europe/Saratov",
            "Europe/Ulyanovsk"
        ];

        return $wrong_timezones;
    }

    /**
     *
     * @param string $birthday_data
     * @param string $format
     * @return array
     */
    public static function validate_birthday(
        string $birthday_data,
        string $format = ""
    ): array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $is_date_ok = true;
        $date_time = null;
        $lower_limit = 18;
        $higher_limit = 110;

        if ($birthday_data !== "") {
            $is_date_ok = false;

            try {
                if (!empty($format)) {
                    $date_time = DateTime::createFromFormat(
                        $format,
                        $birthday_data,
                        new DateTimeZone("UTC")
                    );
                } else {
                    $date_time = new DateTime(
                        $birthday_data,
                        new DateTimeZone("UTC")
                    );
                }
                $now = new DateTime("now", new DateTimeZone("UTC"));
                $diff = Lotto_View::date_diff($date_time, $now, "%y");
                if ($diff >= $lower_limit && $diff <= $higher_limit) {
                    $is_date_ok = true;
                }
            } catch (Exception $e) {
                $fileLoggerService->error(
                    $e->getMessage()
                );
            }
        }

        return [
            $is_date_ok,
            $date_time
        ];
    }

    /**
     *
     * @param string $birthday_data
     * @param string $format
     * @return array
     */
    public static function validate_date(
        string $date,
        string $format = ""
    ): array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $is_date_ok = true;
        $date_time = null;

        if ($date !== "") {
            $is_date_ok = false;

            try {
                if (!empty($format)) {
                    $date_time = DateTime::createFromFormat(
                        $format,
                        $date,
                        new DateTimeZone("UTC")
                    );
                } else {
                    $date_time = new DateTime(
                        $date,
                        new DateTimeZone("UTC")
                    );
                }
                $is_date_ok = true;
            } catch (Exception $e) {
                $fileLoggerService->error(
                    $e->getMessage()
                );
            }
        }

        return [
            $is_date_ok,
            $date_time
        ];
    }

    /**
     *
     * @param string $phone
     * @param string $prefix
     * @param array $pcountries
     * @return array
     * @throws \Exception
     */
    public static function validate_phonenumber(
        string $phone,
        string $prefix,
        array $pcountries
    ): array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $phone_country = "";
        $error = [];

        // This should not be empty
        $isWrongPrefix = empty($prefix) && !empty($phone);
        if ($isWrongPrefix) {
            $error = ['prefix' => _("You have to specify the user's country prefix")];
            return [
                $phone,
                $phone_country,
                $error
            ];
        }

        // This should not be empty
        $pCountriesIsWrong = empty($pcountries) && !empty($phone);
        if ($pCountriesIsWrong) {
            $error = ['prefix' => _("You have to specify proper user's country prefix")];
            return [
                $phone,
                $phone_country,
                $error
            ];
        }

        // Is there any possibility to make that empty ?
        // If yes that will be problem when it will be saved to db
        // to table user!!!
        if (!empty($phone)) {
            $pcountry = explode("_", $prefix);

            if (!empty($pcountry) &&
                count($pcountry) == 2 &&
                isset($pcountries[$pcountry[0]])
            ) {
                $phone_util = \libphonenumber\PhoneNumberUtil::getInstance();

                try {
                    $number_parsed = $phone_util->parse("+" . $pcountry[1] . $phone, $pcountry[0]);
                    $is_number_valid = $phone_util->isValidNumber($number_parsed);
                    if ($is_number_valid) {
                        $phone = $phone_util->format($number_parsed, \libphonenumber\PhoneNumberFormat::E164);
                        $phone_country = $pcountry[0];
                    } else {
                        $error = ['phone' => _("Wrong phone number! Please try to specify it in other format!")];
                    }
                } catch (\libphonenumber\NumberParseException $e) {
                    $fileLoggerService->error(
                        $e->getMessage()
                    );
                    $error = ['phone' => _("Wrong phone number! Please try to specify it in other format!")];
                }
            } else {
                $error = ['prefix' => _("You have to specify the user's country before adding a phone number!")];
            }
        }

        return [
            $phone,
            $phone_country,
            $error
        ];
    }

    /**
     *
     * @return int
     */
    public static function get_default_language_id()
    {
        $default_language_code = LanguageHelper::DEFAULT_LANGUAGE_CODE;
        $default_language = Model_Language::find_one_by_code($default_language_code);

        return intval($default_language['id']);
    }

    /**
     * Returns array of social share data
     * Single row consists:
     * - end of the name of ID for customizer,
     * - name shown in customizer,
     * - name of the awesome icon in 4.7.0 version
     * - name of the awesome icon in 5.0 version
     * - start of the url to redirect on proper site as share url
     * @return array
     */
    public static function get_socials_share_data(): array
    {
        $social_share_rows = [
            [
                'facebook',
                'Facebook',
                'fa fa-brands fa-facebook',
                'fa fa-brands fa-facebook-f',
                'https://www.facebook.com/sharer/sharer.php?u='
            ],
            [
                'twitter',
                'Twitter',
                'fa fa-brands fa-twitter',
                'fa fa-brands fa-twitter',
                'https://twitter.com/intent/tweet?text='
            ],
            [
                'googleplus',
                'Google+',
                'fa fa-brands fa-google-plus',
                'fa fa-brands fa-google-plus-g',
                'https://plus.google.com/share?url='
            ],
//            [
//                'pinterest',
//                'Pinterest',
//                'fa fa-pinterest',
//                'fab fa-pinterest',
//                'https://www.pinterest.com/pin/create/button/?url='
//            ],
//            [
//                'linkedin',
//                'LinkedIn',
//                'fa fa-linkedin',
//                'fab fa-linkedin-in'
//            ]
        ];

        return $social_share_rows;
    }

    /**
     *
     * @return array
     */
    public static function get_prepared_social_share_links(): array
    {
        $social_share_rows = Helpers_General::get_socials_share_data();
        $obj_id = get_queried_object_id();
        $current_url = get_permalink($obj_id);
        $counter_socials = 0;

        foreach ($social_share_rows as $social) {
            $show_social = get_theme_mod('base_social_share_' . $social[0]);
            if ($show_social) {
                $counter_socials++;
            }
        }

        return [
            $social_share_rows,
            $counter_socials,
            $current_url
        ];
    }

    /**
     *
     * @return string
     */
    public static function get_os(): string
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        $os_platform = "Unknown OS Platform";

        $os_array = [
            '/windows nt 10/i'      =>  'Windows 10',
            '/windows nt 6.3/i'     =>  'Windows 8.1',
            '/windows nt 6.2/i'     =>  'Windows 8',
            '/windows nt 6.1/i'     =>  'Windows 7',
            '/windows nt 6.0/i'     =>  'Windows Vista',
            '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'     =>  'Windows XP',
            '/windows xp/i'         =>  'Windows XP',
            '/windows nt 5.0/i'     =>  'Windows 2000',
            '/windows me/i'         =>  'Windows ME',
            '/win98/i'              =>  'Windows 98',
            '/win95/i'              =>  'Windows 95',
            '/win16/i'              =>  'Windows 3.11',
            '/macintosh|mac os x/i' =>  'Mac OS X',
            '/mac_powerpc/i'        =>  'Mac OS 9',
            '/linux/i'              =>  'Linux',
            '/ubuntu/i'             =>  'Ubuntu',
            '/iphone/i'             =>  'iPhone',
            '/ipod/i'               =>  'iPod',
            '/ipad/i'               =>  'iPad',
            '/android/i'            =>  'Android',
            '/blackberry/i'         =>  'BlackBerry',
            '/webos/i'              =>  'Mobile'
        ];

        foreach ($os_array as $regex => $value) {
            if (preg_match($regex, $user_agent)) {
                $os_platform = $value;
            }
        }

        return $os_platform;
    }

    /**
     *
     * @return string
     */
    public static function get_browser(): string
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        $browser = "Unknown Browser";

        $browser_array = [
            '/msie/i'      => 'Internet Explorer',
            '/firefox/i'   => 'Firefox',
            '/safari/i'    => 'Safari',
            '/chrome/i'    => 'Chrome',
            '/edge/i'      => 'Edge',
            '/opera/i'     => 'Opera',
            '/netscape/i'  => 'Netscape',
            '/maxthon/i'   => 'Maxthon',
            '/konqueror/i' => 'Konqueror',
            '/mobile/i'    => 'Handheld Browser'
        ];

        foreach ($browser_array as $regex => $value) {
            if (preg_match($regex, $user_agent)) {
                $browser = $value;
            }
        }

        return $browser;
    }

    /**
     * Check if tickets scans are available for specific whitelabel
     * @param $whitelabel
     * @param $lottery
     * @return bool
     */
    public static function ticket_scan_availability($whitelabel, $lottery, $wl_check = false)
    {
        // Check if model is PURCHASE + SCAN
        if ($lottery['model'] != Helpers_General::LOTTERY_MODEL_PURCHASE_SCAN && !$wl_check) {
            return false;
        }

        // Check if lottery have enabled ticket scans
        if ((int)$lottery['scans_enabled'] === 0) {
            return false;
        }
        
        return true;
    }

    /**
     * @return string|null
     */
    public static function user_aff_token()
    {
        $user_aff_token = null;
        $is_user = Lotto_Settings::getInstance()->get("is_user");
        if (!$is_user) {
            return null;
        }
        $user = Lotto_Settings::getInstance()->get("user");
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        if ((int)$whitelabel['user_registration_through_ref_only'] === 1) {
            $aff = Model_Whitelabel_Aff::find_by_pk($user['connected_aff_id']);
            if (isset($aff)) {
                $user_aff_token = $aff->token;
            }
        }
        return $user_aff_token;
    }
}
