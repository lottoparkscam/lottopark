<?php

use Helpers\ArrayHelper;
use Helpers\CurrencyHelper;
use Helpers\UserHelper;
use Services\Logs\FileLoggerService;

class Helpers_Currency
{
    /**
     * Number of digits after decimal point for currency rates - setting within DB
     */
    const RATE_SCALE = 8;
    
    /**
     * Numbers of digits after decimal point for bcmath operations
     */
    const BC_SCALE = 8;
    const BCCOMP_SCALE = 2;

    /**
     * Function to proprely pull value of type of Payment and id of the Payment
     *
     * @param array $whitelabel
     * @param bool $deposit
     * @param bool $balancepayment
     * @param bool $minreached
     * @return array
     */
    public static function get_first_payment(
        array $whitelabel,
        bool $deposit = false,
        bool $balancepayment = false,
        bool $bonus_balance_payment = false,
        bool $minreached = false
    ): array {
        $payment_type = 0;
        $whitelabel_payment_method_id = -1;
        
        $found = false;
        if (!$deposit) {
            if ($bonus_balance_payment) {
                $payment_type = Helpers_General::PAYMENT_TYPE_BONUS_BALANCE;
            } elseif ($balancepayment) {
                $payment_type = Helpers_General::PAYMENT_TYPE_BALANCE;
            }
            $found = true;
        }
        
        if (!$found && ($deposit || $minreached)) {
            $cc_methods = Model_Whitelabel_CC_Method::get_cc_methods_for_whitelabel($whitelabel);
            if (!empty($cc_methods)) {
                $payment_type = Helpers_General::PAYMENT_TYPE_CC;
                $whitelabel_payment_method_id = Model_Whitelabel_CC_Method::get_emerchant_method_id();
                $found = true;
            }
        }
        
        if (!$found) {
            $whitelabel_payment_methods_without_currency = Model_Whitelabel_Payment_Method::get_payment_methods_for_whitelabel($whitelabel);
            $whitelabel_payment_methods = Lotto_Helper::get_whitelabel_payment_methods_for_language(
                $whitelabel,
                $whitelabel_payment_methods_without_currency
            );
            $payment_type = Helpers_General::PAYMENT_TYPE_OTHER;
            if (!is_null($whitelabel_payment_methods)) {
                foreach ($whitelabel_payment_methods as $whitelabel_payment_method) {
                    if ((int)$whitelabel_payment_method['show'] === 1) {
                        $whitelabel_payment_method_id = (int)$whitelabel_payment_method['id'];
                        $found = true;
                        break;
                    }
                }
            }
        }
        
        if (!$found && $whitelabel_payment_method_id === -1) {
            $payment_type = Helpers_General::PAYMENT_TYPE_BALANCE;
            $found = true;
        }
        
        return [
            $payment_type,
            $whitelabel_payment_method_id
        ];
    }
    
    /**
     * Process and return currencies for gateway and manager plus payment type
     * and payment method ID as array
     *
     * @param array $whitelabel
     * @param int $type
     * @param int $whitelabel_payment_method_id
     * @return array
     */
    public static function get_currencies_tabs(
        array $whitelabel,
        int $type = null,
        int $whitelabel_payment_method_id = null
    ): array {
        $payment_type = 1;
        if (!empty($type)) {
            $payment_type = (int) $type;
        } else {
            $payment_type = 2;
        }

        if (empty($whitelabel_payment_method_id)) {
            $whitelabel_payment_method_id = 0;  // Normally this should not be happend
        }

        $user_currency_tab = CurrencyHelper::getCurrentCurrency()->to_array();
        
        $gateway_currency_tab = Helpers_Currency::get_default_gateway_currency(
            $whitelabel,
            $payment_type,
            $whitelabel_payment_method_id,
            $user_currency_tab
        );

        return $gateway_currency_tab;
    }

    /** This function also sets currencies for singleton */
    public static function getCurrencies(): array
    {
        $currencies = Lotto_Settings::getInstance()->get("currencies");

        if (empty($currencies)) {
            $currencies = Model_Currency::get_all_currencies();
            Lotto_Settings::getInstance()->set("currencies", $currencies);
        }

        return $currencies;
    }

    /**
     * @param string $code
     * @return array|null
     * @throws Exception
     */
    public static function findCurrencyByCode(string $code): ?array
    {
        $currencies = self::getCurrencies();
        $itemKey = ArrayHelper::getKeyOfItemFromArrayByItemValue($currencies, 'code', $code);

        if ($itemKey < 0) {
            return null;
        }

        return $currencies[$itemKey];
    }

    /**
     * @param int $id
     * @return array|null
     * @throws Exception
     */
    public static function findCurrencyById(int $id): ?array
    {
        $currencies = self::getCurrencies();
        return $currencies[$id] ?? null;
    }

    /**
     * Function pull the data from DB for given code of the currency (or id of currency)
     * set as default currency and returns it as array with id, code and rate,
     * converted rate (to USD) could be given if $add_converted set to true
     *
     * @param boolean $add_converted If true the result will consist converted rate as well
     * @param string $other_currency_code If empty it will fallback to EUR
     *                                      or if set to null it will try to get
     *                                      currency tab based on $other_currency_id
     *                                      if given
     * @param null|int $other_currency_id It could be Id of currency given
     * @return array
     * @throws Exception
     */
    public static function get_mtab_currency(
        $add_converted = false,
        $other_currency_code = "",
        $other_currency_id = null
    ): array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $currency_tab = null;
        
        if (empty($other_currency_id)) {
            if (!empty($other_currency_code)) {
                $default_country_code = $other_currency_code;
            } else {
                $default_country_code = "EUR";                  // ONLY HERE SHOULD BE DEFAULT SYSTEM CURRENCY IN EUR
            }

            $result_curr = self::findCurrencyByCode($default_country_code);

            if (!empty($result_curr)) {
                $currency_tab = $result_curr;
            }
        } else {
            $result_curr = self::findCurrencyById($other_currency_id);
            
            if (!is_null($result_curr)) {
                $currency_tab = $result_curr;
            }
        }
        
        if ($currency_tab !== null) {
            $result = [
                'id' => $currency_tab['id'],
                'code' => $currency_tab['code'],
                'rate' => $currency_tab['rate']
            ];
            
            if ($add_converted) {
                $result['multiplier_in_usd'] = round(1 / $currency_tab['rate'], Helpers_Currency::RATE_SCALE);
            }
        } else {
            $msg = "Lack of currency in the system! ";
            if (!empty($other_currency_code)) {
                $msg .= "Given code: " . $other_currency_code . ". ";
            }
            if (!is_null($other_currency_id)) {
                $msg .= "Given currency_id: " . $other_currency_id . ".";
            }
            $fileLoggerService->error($msg);
            exit(_("Security error! Please contact us!"));
        }
        
        return $result;
    }
    
    /**
     * Get list all available currencies where currency code is a key
     *
     * @return array
     */
    public static function get_currency_map_by_code()
    {
        $currencies = self::getCurrencies();
        
        $default_system_currency_tab_usd = Helpers_Currency::get_mtab_currency(false, "USD");
        
        $currency_map = [];
        if (isset($currencies)) {
            foreach ($currencies as $item) {
                $currency_map[$item['code']] = $item;
            }
        } elseif (!empty($default_system_currency_tab_usd['code'])) {
            $currency_map[$default_system_currency_tab_usd['code']] = $default_system_currency_tab_usd;
        }
        
        return $currency_map;
    }

    /**
     * Get currency code based on IP of the user depends on state
     * of the logged in or not of the user
     *
     * @return array
     */
    public static function get_final_currency_code()
    {
        // default system currency needed to fallback to "EUR"
        $default_system_currency = Helpers_Currency::get_mtab_currency();
        $final_currency_code = $default_system_currency['code']; // Only for initialization purpose
        
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        
        $is_user = Lotto_Settings::getInstance()->get("is_user");
        $user = Lotto_Settings::getInstance()->get("user");
        
        $usersCurrencyById = !empty($user['currency_id']) ? self::findCurrencyById($user['currency_id']) : null;
        
        // if our user is registered and has country set up in his profile
        // take the currency from his country
        // THIS WILL CHANGE SOON TO MANAGE USER PROFILE BY currency_id FIELD, NOT BY country!!!
        if ($is_user && !empty($user['currency_id']) &&
            isset($usersCurrencyById) &&
            !empty($usersCurrencyById['code'])
        ) {
            $final_currency_code = $usersCurrencyById['code'];
        } else {
            // FOR ALL LANGUAGES!
            $country_currency = null;
            
            // if not set, take the currency from IP
            $user_ip = Lotto_Security::get_IP();
            $geoip = Lotto_Helper::get_geo_IP_record($user_ip);
            $country_code = null;
            if ($geoip !== false) {
                $country_code = $geoip->country->isoCode;
            }

            // Not found in the iso records
            if ($country_code === null) {
                // Get default currency row for Whitelabel - not recognized IP
                $default_currency = Model_Whitelabel_Default_Currency::get_default_for_whitelabel($whitelabel);
                // There is no currency set for whitelabel for not recognized IP-s
                // fallback to default 'EUR'
                if ($default_currency === null) {
                    ;
                } else {
                    $final_currency_code = $default_currency['currency_code'];
                }
            } else {
                // Check if Whitelabel set proper Currency for Country (by IP)
                $country_currency = Model_Whitelabel_Country_Currency::get_for_whitelabel_and_country($whitelabel, $country_code);

                // There is no currency set for whitelabel for such country code
                if ($country_currency === null) {
                    // Get default currency row for Whitelabel - not recognized IP
                    $default_currency = Model_Whitelabel_Default_Currency::get_default_for_whitelabel($whitelabel);
                    // There is no currency set for whitelabel for not recognized IP-s
                    // fallback to default 'EUR'
                    if ($default_currency === null) {
                        ;
                    } else {
                        $final_currency_code = $default_currency['currency_code'];
                    }
                } else {
                    $final_currency_code = $country_currency['currency_code'];
                }
            }

            // There is no currency set for whitelabel (even for not recognized IP-s)
            // fallback to default 'EUR'
            if ($country_currency === null) {
                ;
            } else {
                $final_currency_code = $country_currency['currency_code'];
            }
        }
        
        return $final_currency_code;
    }
    
    /**
     * Function check if user currency is defined for given payment method
     * and if yes it returns that currency otherwise
     * it returns default currency for that payment method
     *
     * @param array $whitelabel
     * @param int $payment_type
     * @param int $whitelabel_payment_method_id
     * @param array $user_currency_tab
     * @return array|null
     */
    public static function get_currency_for_payment_method(
        array $whitelabel,
        int $payment_type,
        int $whitelabel_payment_method_id = null,
        array $user_currency_tab = []
    ):? array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $currency = [];
                
        if (empty($user_currency_tab)) {     // Try to fallback to system currency
            $fileLoggerService->error(
                "Lack of user currency setting. WhitelabelID: " .
                $whitelabel['id'] . "!"
            );
            $default_system_currency = Helpers_Currency::get_mtab_currency();
            if (!empty($default_system_currency)) {
                $currency = $default_system_currency;
            }
            
            if (empty($default_system_currency)) {
                exit("Security error! Please contact us!");
            }
        } else {
            switch ($payment_type) {
                case Helpers_General::PAYMENT_TYPE_BALANCE:
                case Helpers_General::PAYMENT_TYPE_BONUS_BALANCE:
                    $currency = $user_currency_tab;
                    break;
                case Helpers_General::PAYMENT_TYPE_CC:
                    // TODO: prepare for different currency use
                    // At this moment it is off, so the value of user currency
                    // is returned
                    $currency = $user_currency_tab;
                    break;
                case Helpers_General::PAYMENT_TYPE_OTHER:
                    $user_currency_id = $user_currency_tab['id'];

                    $whitelabel_payment_method_currency_temp = Model_Whitelabel_Payment_Method_Currency::get_single_row_for_whitelabel_payment_id(
                        $whitelabel['id'],
                        $whitelabel_payment_method_id,
                        $user_currency_id
                    );
                    
                    if (!empty($whitelabel_payment_method_currency_temp[0])) {
                        $currency = $whitelabel_payment_method_currency_temp[0];
                    } else {
                        $user = Lotto_Settings::getInstance()->get("user");
                        $user_id = -1;
                        if (!empty($user)) {
                            $user_id = (int)$user['id'];
                        }

                        $fileLoggerService->error(
                            "Lack of whitelabel_payment_method_currency setting. " .
                            "WhitelabelID: " . $whitelabel['id'] . ", " .
                            "Whitelabel Payment Method ID: " . $whitelabel_payment_method_id . ", " .
                            "User ID: " . $user_id . ". " .
                            "User currency ID: " . $user_currency_id . "!"
                        );
                        $default_system_currency = Helpers_Currency::get_mtab_currency();
                        if (!empty($default_system_currency)) {
                            $currency = $default_system_currency;
                        }
                        if (empty($default_system_currency)) {
                            exit("Security error! Please contact us!");
                        }
                    }
                    break;
            }
        }
        
        return $currency;
    }
    
    /**
     * Function prepared to pull the gateway default currency
     * At this moment is EUR for most of them, but I suppose that it should be
     * variable for each type and method of payment
     *
     * @param array $whitelabel
     * @param int $payment_type
     * @param int $whitelabel_payment_method_id
     * @param array $user_currency_tab
     * @param bool $check_payment_method_currency
     * @return array
     */
    public static function get_default_gateway_currency(
        array $whitelabel,
        int $payment_type,
        int $whitelabel_payment_method_id = null,
        array $user_currency_tab = [],
        bool $check_payment_method_currency = true
    ): array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $gateway_currency_tab = "";
        
        if ($check_payment_method_currency) {
            $gateway_currency_tab = Helpers_Currency::get_currency_for_payment_method(
                $whitelabel,
                $payment_type,
                $whitelabel_payment_method_id,
                $user_currency_tab
            );
            if (empty($gateway_currency_tab)) {
                $default_system_currency = Helpers_Currency::get_mtab_currency();
                if (!empty($default_system_currency)) {
                    $gateway_currency_tab = $default_system_currency;
                }
            }
            return $gateway_currency_tab;
        }
        
        switch ($payment_type) {
            case Helpers_General::PAYMENT_TYPE_BALANCE:
            case Helpers_General::PAYMENT_TYPE_BONUS_BALANCE:
                $user_currency = CurrencyHelper::getCurrentCurrency()->to_array();
                if (empty($user_currency)) {     // Try to fallback to system currency
                    $fileLoggerService->error(
                        "Lack of user currency setting. WhitelabelID: " .
                        $whitelabel['id'] . "!"
                    );
                    $default_system_currency = Helpers_Currency::get_mtab_currency();
                    if (!empty($default_system_currency)) {
                        $gateway_currency_tab = $default_system_currency;
                    }
                    if (empty($default_system_currency)) {
                        exit("Security error! Please contact us!");
                    }
                } else {
                    $gateway_currency_tab = $user_currency;
                }
                break;
            case Helpers_General::PAYMENT_TYPE_CC:
                $payment_currency = Model_Whitelabel_CC_Method::get_payment_currency_for_whitelabel(
                    $whitelabel,
                    $whitelabel_payment_method_id
                );

                if (empty($payment_currency)) {     // Try to fallback to system currency
                    $fileLoggerService->error(
                        "Lack of payment currency setting for Credit Card for payment method: " .
                        $whitelabel_payment_method_id . ". WhitelabelID: " .
                        $whitelabel['id'] . "!"
                    );
                    $default_system_currency = Helpers_Currency::get_mtab_currency();
                    if (!empty($default_system_currency)) {
                        $gateway_currency_tab = $default_system_currency;
                    }
                    if (empty($default_system_currency)) {
                        exit("Security error! Please contact us!");
                    }
                } else {
                    $gateway_currency_tab = $payment_currency;
                }
                break;
            case Helpers_General::PAYMENT_TYPE_OTHER:
                $payment_currency = Model_Whitelabel_Payment_Method::get_payment_currency_for_whitelabel(
                    $whitelabel,
                    $whitelabel_payment_method_id
                );
                
                if (empty($payment_currency)) {     // Try to fallback to system currency
                    $fileLoggerService->error(
                        "Lack of payment currency setting for Other methods for payment method: " .
                        $whitelabel_payment_method_id . ". WhitelabelID: " .
                        $whitelabel['id'] . "!"
                    );
                    $default_system_currency = Helpers_Currency::get_mtab_currency();
                    if (!empty($default_system_currency)) {
                        $gateway_currency_tab = $default_system_currency;
                    }
                    if (empty($default_system_currency)) {
                        exit("Security error! Please contact us!");
                    }
                } else {
                    $gateway_currency_tab = $payment_currency;
                }

                break;
            default:
                $default_system_currency = Helpers_Currency::get_mtab_currency();
                if (!empty($default_system_currency)) {
                    $gateway_currency_tab = $default_system_currency;
                }

                break;
        }
        
        return $gateway_currency_tab;
    }
    
    /**
     *
     * @return array
     */
    public static function get_default_values_for_deposits(): array
    {
        // If you would like to change proposal amounts
        // you can do that by changing those variables
        // They are converted to user currency
        // Those values are in Euro at this moment,
        // but you can treat those values as independent to currency
        // TODO: In short future should be set as gataway wants
        $first_box_deposit_value = '20.00';
        $second_box_deposit_value = '50.00';
        $third_box_deposit_value = '100.00';
        
        return [
            $first_box_deposit_value,
            $second_box_deposit_value,
            $third_box_deposit_value
        ];
    }
    
    /**
     * Function recalculate value from one currency given as table - id, code, rate -
     * ($from_currency_tab) and returns it in currency given as code ($to_currency)
     *
     * @param string $value_in_currency
     * @param array $from_currency_tab
     * @param string $to_currency_code Default EUR - it will return record for EUR currency
     * @param int $decimals Default 2
     * @return string
     */
    public static function get_recalculated_to_given_currency(
        string $value_in_currency,
        array $from_currency_tab,
        string $to_currency_code = "EUR",
        int $decimals = 2
    ): string {
        $to_currency_mtab = Helpers_Currency::get_mtab_currency(true, $to_currency_code);

        $value_in_gateway_currency = $value_in_currency;

        // Nothing changed in the case that currency is equal to system default currency
        if ((string)$to_currency_mtab['code'] !== (string)$from_currency_tab['code']) {
            $multiplier_in_usd = $to_currency_mtab['multiplier_in_usd'];
            $converted_multiplier = round(
                $multiplier_in_usd * $from_currency_tab['rate'],
                Helpers_Currency::RATE_SCALE
            );

            $value_in_gateway_currency = round($value_in_currency / $converted_multiplier, $decimals);
        }

        return $value_in_gateway_currency;
    }
    
    /**
     * Function convert value in EUR currency to other currency.
     * Function round multiplier to int value and based on that is
     * given converted value. At this moment it is perfect function for calculate
     * values for deposit boxes
     *
     * @param float $multiplier_in_usd
     * @param array $to_currency_tab
     * @param string $to_convert_value
     * @param bool $should_round If yes the value of $converted_formatted
     * will be returned as nice value (ex. 20, 3000, 500 etc)
     * @return array
     */
    public static function get_single_converted_from_system_currency(
        float $multiplier_in_usd,
        array $to_currency_tab,
        string $to_convert_value,
        bool $should_round = false
    ): array {
        $converted_multiplier = round(
            $multiplier_in_usd * $to_currency_tab["rate"],
            Helpers_Currency::RATE_SCALE
        );

        $rounded_in_gateway_currency = round($converted_multiplier, 0);
        if ($converted_multiplier < 1) {
            $rounded_in_gateway_currency = round($converted_multiplier, 1);
        }
        
        $converted = round(
            $to_convert_value * $rounded_in_gateway_currency,
            Helpers_Currency::RATE_SCALE
        );
        $value_in_gateway_currency = round(
            $converted / $converted_multiplier,
            Helpers_Currency::RATE_SCALE
        );
        
        $converted_formatted = round($converted, 2);
        $value_in_gateway_currency_formatted = round($value_in_gateway_currency, 2);
        
        if ($should_round && $converted_formatted != 0) {
            // Discover how many digits are before point sign
            $converted_int_value = intval($converted_formatted);
            // and decreased by one
            $length_of_value_decreased = strlen((string)$converted_int_value) - 1;
            
            // Calculate 10^$length_of_value_decreased
            $division_value = pow(10, $length_of_value_decreased);
            // To get nice value - ceil is nice function to that feature
            $converted_noformatted = ceil($converted_formatted / $division_value) * $division_value;
            
            $converted_formatted = round($converted_noformatted, 2);
            $value_in_gateway_currency_formatted = Helpers_Currency::get_recalculated_to_given_currency(
                $converted_formatted,
                $to_currency_tab
            );
        }
        
        return [
            $converted_formatted,
            $value_in_gateway_currency_formatted
        ];
    }

    /**
     * Function convert value given in $from_currency_tab to $to_currency_tab
     *
     * @param array $from_currency_tab
     * @param string $value
     * @param array $to_currency_tab If null it will be fallback to default system tab
     * @return string
     */
    public static function get_single_converted_from_currency(
        array $from_currency_tab,
        string $value,
        array $to_currency_tab = null
    ): string {
        $to_currency_tab_full = null;
        if (!isset($to_currency_tab)) {
            // Returns record for EUR currency
            $to_currency_tab_full = Helpers_Currency::get_mtab_currency(true);
        } else {
            $to_currency_tab_full = $to_currency_tab;
        }
        
        $converted = $value;
        
        // Nothing changed in the case that currency is equal to system default currency
        if ((string)$to_currency_tab_full['code'] !== (string)$from_currency_tab['code']) {
            $converted_multiplier = round(
                $to_currency_tab_full['multiplier_in_usd'] * $from_currency_tab['rate'],
                Helpers_Currency::RATE_SCALE
            );
            $converted = round($value / $converted_multiplier, 2);
        }
        
        return $converted;
    }
    
    /**
     * This is helper function for manager site to get converted prepared
     * default values of deposits
     *
     * @param array $to_currency_tab
     * @return array
     */
    public static function get_default_deposits_in_currency(array $to_currency_tab): array
    {
        list(
            $first_box_deposit_value,
            $second_box_deposit_value,
            $third_box_deposit_value
        ) = Helpers_Currency::get_default_values_for_deposits();
        
        $result = [
            'first_in_gateway_currency' => $first_box_deposit_value,
            'first_converted' => $first_box_deposit_value,
            'first_default_multi_in_gateway_currency' => $first_box_deposit_value,
            'second_in_gateway_currency' => $second_box_deposit_value,
            'second_converted' => $second_box_deposit_value,
            'second_default_multi_in_gateway_currency' => $second_box_deposit_value,
            'third_in_gateway_currency' => $third_box_deposit_value,
            'third_converted' => $third_box_deposit_value,
            'third_default_multi_in_gateway_currency' => $third_box_deposit_value
        ];
        
        // Returns record for EUR currency
        $default_system_currency = Helpers_Currency::get_mtab_currency(true);
        
        // Nothing changed in the case that currency is equal to system default currency
        if ((string)$default_system_currency['code'] !== (string)$to_currency_tab['code']) {
            list(
                $result['first_converted'],
                $result['first_in_gateway_currency']
            ) = Helpers_Currency::get_single_converted_from_system_currency(
                $default_system_currency['multiplier_in_usd'],
                $to_currency_tab,
                $first_box_deposit_value,
                true
            );
            
            list(
                $result['second_converted'],
                $result['second_in_gateway_currency']
            ) = Helpers_Currency::get_single_converted_from_system_currency(
                $default_system_currency['multiplier_in_usd'],
                $to_currency_tab,
                $second_box_deposit_value,
                true
            );

            list(
                $result['third_converted'],
                $result['third_in_gateway_currency']
            ) = Helpers_Currency::get_single_converted_from_system_currency(
                $default_system_currency['multiplier_in_usd'],
                $to_currency_tab,
                $third_box_deposit_value,
                true
            );
        }
        
        return $result;
    }
    
    /**
     * This function convert deposits values (for 3 boxes) from current value to EUR value
     * and return those data as array (with default value as well)
     * @param array $currency_from_tab
     * @param array $data
     * @return array
     */
    public static function get_default_deposits_from_currency(
        array $currency_from_tab,
        array $data
    ): array {
        $first_box_deposit_value = Helpers_Currency::get_single_converted_from_currency(
            $currency_from_tab,
            $data['default_deposit_first_box']
        );
        $second_box_deposit_value = Helpers_Currency::get_single_converted_from_currency(
            $currency_from_tab,
            $data['default_deposit_second_box']
        );
        $third_box_deposit_value = Helpers_Currency::get_single_converted_from_currency(
            $currency_from_tab,
            $data['default_deposit_third_box']
        );

        $results = [
            'first_in_gateway_currency' => $first_box_deposit_value,
            'first_converted' => $data['default_deposit_first_box'],
            'first_default_multi_in_gateway_currency' => $first_box_deposit_value,
            'second_in_gateway_currency' => $second_box_deposit_value,
            'second_converted' => $data['default_deposit_second_box'],
            'second_default_multi_in_gateway_currency' => $second_box_deposit_value,
            'third_in_gateway_currency' => $third_box_deposit_value,
            'third_converted' => $data['default_deposit_third_box'],
            'third_default_multi_in_gateway_currency' => $third_box_deposit_value
        ];
        
        return $results;
    }

    /**
     * Get prepared converted deposit values in prepared string
     * for manager site
     *
     * @param string $deposit_value
     * @param string $from_currency_code
     * @param string $additional_text
     * @return string
     * @throws Exception
     */
    public static function get_prepared_deposit_value(
        $deposit_value,
        $from_currency_code,
        $additional_text
    ) {
        $result = $additional_text . " ";
        
        $default_currency_code = "";
        
        // At this moment default system currency is EURO
        $default_system_currency = Helpers_Currency::get_mtab_currency();
        if (!empty($default_system_currency['code'])) {
            $default_currency_code = $default_system_currency['code'];
        }
        
        // Nothing changed in the case that currency is equal to system default currency
        if ((string)$default_currency_code !== (string)$from_currency_code) {
            $currency_rate = 0.00;
            $res = self::findCurrencyByCode($from_currency_code);
            if (!empty($res)) {
                $currency_rate = $res['rate'];
            }
            
            $gateway_currency_rate = $default_system_currency['rate'];
            $multiplier_in_usd = round(1 / $gateway_currency_rate, Helpers_Currency::RATE_SCALE);
            
            $converted_multiplier = round($multiplier_in_usd * $currency_rate, Helpers_Currency::RATE_SCALE);
            
            $result_temp = round($deposit_value / $converted_multiplier, Helpers_Currency::RATE_SCALE);
            
            $result_in_gateway_currency = Lotto_View::format_currency(
                $result_temp,
                $default_currency_code,
                true
            );
            
            $result_in_given_currency = Lotto_View::format_currency(
                $deposit_value,
                $from_currency_code,
                true
            );
            
            $result .= $result_in_given_currency . " (" . $result_in_gateway_currency . ")";
        } else {
            $last_deposit_value = Lotto_View::format_currency(
                $deposit_value,
                $default_currency_code,
                true
            );
            
            $result .= $last_deposit_value;
        }
        
        return $result;
    }
    
    /**
     *
     * @param array $whitelabel
     * @param array $user_currency
     * @param string $to_currency Default EUR - return deposits records based on EUR currency
     * @return array
     */
    public static function get_deposit_values_for_country(
        $whitelabel,
        $user_currency,
        $to_currency = "EUR"
    ) {
        $result = [];
        
        $user_deposits = Model_Whitelabel_Default_Currency::get_for_user(
            $whitelabel,
            $user_currency['id']
        );
        
        // There are values for user for proper country_code in admin section
        if (!empty($user_deposits)) {
            $result['first_in_currency'] = $user_deposits['default_deposit_first_box'];
            $result['first_in_gateway_currency'] = Helpers_Currency::get_recalculated_to_given_currency(
                $user_deposits['default_deposit_first_box'],
                $user_currency,
                $to_currency
            );
            $result['second_in_currency'] = $user_deposits['default_deposit_second_box'];
            $result['second_in_gateway_currency'] = Helpers_Currency::get_recalculated_to_given_currency(
                $user_deposits['default_deposit_second_box'],
                $user_currency,
                $to_currency
            );
            $result['third_in_currency'] = $user_deposits['default_deposit_third_box'];
            $result['third_in_gateway_currency'] = Helpers_Currency::get_recalculated_to_given_currency(
                $user_deposits['default_deposit_third_box'],
                $user_currency,
                $to_currency
            );
        } else { // Maybe there are values for user currency for default in admin section
            $user_deposits_in_default_currency = Model_Whitelabel_Default_Currency::get_for_user(
                $whitelabel,
                null,
                true
            );
            
            if (!empty($user_deposits_in_default_currency)) {
                $result['first_in_currency'] = $user_deposits_in_default_currency['default_deposit_first_box'];
                $result['first_in_gateway_currency'] = Helpers_Currency::get_recalculated_to_given_currency(
                    $user_deposits_in_default_currency['default_deposit_first_box'],
                    $user_currency,
                    $to_currency
                );
                $result['second_in_currency'] = $user_deposits_in_default_currency['default_deposit_second_box'];
                $result['second_in_gateway_currency'] = Helpers_Currency::get_recalculated_to_given_currency(
                    $user_deposits_in_default_currency['default_deposit_second_box'],
                    $user_currency,
                    $to_currency
                );
                $result['third_in_currency'] = $user_deposits_in_default_currency['default_deposit_third_box'];
                $result['third_in_gateway_currency'] = Helpers_Currency::get_recalculated_to_given_currency(
                    $user_deposits_in_default_currency['default_deposit_third_box'],
                    $user_currency,
                    $to_currency
                );
            }
        }
        
        // No settings in admin section
        if (empty($result)) {
            // In that case the system will be based on default values in EUR
            // There is no settings for currencies in admin section
            
            $default_values = Helpers_Currency::get_default_deposits_in_currency($user_currency);

            $result['first_in_currency'] = $default_values['first_converted'];
            $result['first_in_gateway_currency'] = $default_values['first_in_gateway_currency'];
            $result['second_in_currency'] = $default_values['second_converted'];
            $result['second_in_gateway_currency'] = $default_values['second_in_gateway_currency'];
            $result['third_in_currency'] = $default_values['third_converted'];
            $result['third_in_gateway_currency'] = $default_values['third_in_gateway_currency'];
        }
        
        return $result;
    }
    
    /**
     *
     * @param array $from_currency_mtab
     * @param array $to_currency_tab
     * @return string
     */
    public static function get_converted_mulitiplier($from_currency_mtab, $to_currency_tab)
    {
        $converted_multiplier = "1.00";
        if (!empty($from_currency_mtab)) {
            $converted_multiplier = round($from_currency_mtab['multiplier_in_usd'] * $to_currency_tab['rate'], Helpers_Currency::RATE_SCALE);
        }
        
        return $converted_multiplier;
    }
    
    /**
     *
     * @param string $amount Amount as formatted string
     * @param string $currency_from Currency as code ex. USD
     * @param string $currency_to Currency as code ex. EUR
     * @return string
     */
    public static function convert_to_any(
        string $amount,
        string $currency_from,
        string $currency_to,
        bool $withoutRounding = false,
        int $roundingPrecision = 2
    ): string {
        
        $areBothCurrenciesTheSame = $currency_from === $currency_to;
        
        if($areBothCurrenciesTheSame) {
            return $amount;
        }

        $currencies = self::getCurrencies();
        $currency_map = [];
        foreach ($currencies as $item) {
            $currency_map[$item['code']] = $item;
        }
        
        $amount_from = self::convert_to_USD($amount, $currency_from);
        
        if ($withoutRounding) {
            return $amount_from * $currency_map[$currency_to]['rate'];
        }

        return round($amount_from * $currency_map[$currency_to]['rate'], $roundingPrecision);
    }

    /**
     *
     * @param string $amount
     * @param string $currency Currency as code ex. USD
     * @param int $decimals_in_calculations Default 8, Decimals in calculations
     * @param int $decimals_in_result Default:2, Decimals in result - return.
     * @return string
     */
    public static function convert_to_USD(
        string $amount,
        string $currency,
        int $decimals_in_calculations = 8,
        int $decimals_in_result = 2
    ): string {
        $currencies = self::getCurrencies();
        $currency_map = [];
        foreach ($currencies as $item) {
            $currency_map[$item['code']] = $item;
        }
        
        $multiplier = round(1 / $currency_map[$currency]['rate'], $decimals_in_calculations);

        return round($amount * $multiplier, $decimals_in_result);
    }

    /**
     *
     * @param string $amount
     * @param string $currency Currency as code ex. PLN
     * @param int $dec_point Default 8
     * @return string
     */
    public static function convert_to_EUR(
        string $amount,
        string $currency,
        int $dec_point = 8
    ): string {
        $currencies = self::getCurrencies();
        $currency_map = [];
        foreach ($currencies as $item) {
            $currency_map[$item['code']] = $item;
        }
        
        $amount_USD = self::convert_to_USD($amount, $currency, $dec_point);

        return round($amount_USD * $currency_map['EUR']['rate'], 2);
    }
    
    /**
     * This function was moved from Lotto_Helper to hold everything what is connected
     * with currencies within one file
     * But at this moment I can't see that it is used anywhere beside of dev.php controller
     * @param string $country
     * @return string
     */
    public static function get_currency_for_country(string $country): string
    {
        $default_currency = "EUR";

        if (empty($country)) {
            return $country;
        }

        $supplemental_file = APPPATH . 'vendor/cldr/supplemental/supplementalData.xml';
        $time = filemtime($supplemental_file);

        $map_currency_file = APPPATH . 'vendor/cldr/currencies-map-' . $time . '.json';
        if (file_exists($map_currency_file)) {
            $map = json_decode(file_get_contents($map_currency_file), true);
            if (isset($map) && !empty($map[$country])) {
                return $map[$country];
            } else {
                return $default_currency;
            }
        }

        $doc = new DOMDocument();
        $doc->load($supplemental_file);

        $regions = $doc->getElementsByTagName('region');
        $countries = array_keys(self::get_localized_country_list());

        $map = [];
        foreach ($regions as $region) {
            if (!empty($region->getAttribute('iso3166')) &&
                in_array($region->getAttribute('iso3166'), $countries)
            ) {
                $currencies = $region->getElementsByTagName('currency');
                $currency = $default_currency;
                if (!empty($currencies) && count($currencies) > 0 && !$currencies[0]->hasAttribute('tender')) {
                    $currency = $currencies[0]->getAttribute('iso4217');
                }

                // RO prefers Euro currency
                if ($region->getAttribute('iso3166') == 'RO') {
                    $currency = $default_currency;
                }

                $map[$region->getAttribute('iso3166')] = $currency;
            }
        }

        file_put_contents($map_currency_file, json_encode($map, JSON_UNESCAPED_UNICODE));

        if (isset($map) && !empty($map[$country])) {
            return $map[$country];
        } else {
            return $default_currency;
        }
    }
    
    /**
     * This function returns default currency code
     * At this moment it is EUR currency and probably it will not be
     * changed anytime! This currency in many situation is treated as fallback
     * currency.
     * @return string
     */
    public static function get_default_currency_code(): string
    {
        $default_currency_tab = Helpers_Currency::get_mtab_currency();
        $default_currency_code = $default_currency_tab['code'];
        
        return $default_currency_code;
    }
    
    /**
     * This function returns system default currency code.
     * Here is USD and probably it will not be changed anytime
     * @return string
     */
    public static function get_system_currency_code(): string
    {
        $system_currency_tab = Helpers_Currency::get_mtab_currency(false, "USD");
        $system_currency_code = $system_currency_tab['code'];
        
        return $system_currency_code;
    }
    
    /**
     * @param boolean $with_currency
     * @return string
     */
    public static function sum_order(bool $with_currency = true, bool $with_multidraw = true): string
    {
        $whitelabel = Model_Whitelabel::get_by_domain(Lotto_Helper::getWhitelabelDomainFromUrl());
        $total_sum = 0.0;

        if (!empty(Session::get("order"))) {
            $order = Session::get("order");
            $lotteries = Helpers_Lottery::getLotteries();

            foreach ($order as $key => $item) {
                if (isset($lotteries['__by_id'][$item['lottery']])) {
                    $lottery = $lotteries['__by_id'][$item['lottery']];
                    $pricing = Helpers_Lottery::getPricing($lottery, $item['ticket_multiplier'] ?? 1);

                    $ticket_price = !empty($item['lines']) ? count($item['lines']) : 0;

                    if (!$with_multidraw && isset($item['multidraw'])) {
                        continue;
                    }

                    if (isset($item['multidraw'])) {
                        $multi_draw_helper = new Helpers_Multidraw($whitelabel);
                        $multi_draw = $multi_draw_helper->check_multidraw($item['multidraw']);
                    }

                    $item_price = round($pricing * $ticket_price, 2);

                    if (isset($item['multidraw']) && !empty($multi_draw['tickets'])) {
                        $ticket_price = $multi_draw_helper->calculate($multi_draw, $item_price);
                        $item_price = $ticket_price;
                    }

                    $total_sum = round($total_sum + $item_price, 2);
                }
            }
        }

        if ($with_currency) {
            $user_currency = self::getUserCurrencyTable();
            $total_sum = Lotto_View::format_currency($total_sum, $user_currency, true);
        }
        
        return $total_sum;
    }
    
    /**
     *
     * @param array $currency_tab
     * @param boolean $with_currency Default false
     * @return string
     */
    public static function get_sum_order_in_gateway(
        array $currency_tab,
        bool $with_currency = true
    ): string {
        $total_sum = "0.00";
        
        if (!empty(Session::get("order"))) {
            $order = Session::get("order");
            $lotteries = lotto_platform_get_lotteries();
            foreach ($order as $key => $item) {
                if (isset($lotteries['__by_id'][$item['lottery']])) {
                    $lottery = $lotteries['__by_id'][$item['lottery']];
                    
                    $converted_price = Lotto_Helper::get_user_converted_price($lottery, $currency_tab['id']);
                    
                    $total_sum += $converted_price * count($item['lines']);
                }
            }
        }

        if ($with_currency) {
            $total_sum = Lotto_View::format_currency($total_sum, $currency_tab['code'], true);
        }
        
        return $total_sum;
    }
    
    /**
     *
     * @param array $whitelabel
     * @param array $whitelabel_payment_methods
     * @param array $user_currency
     * @return array|null
     */
    public static function get_whitelabel_payment_methods_with_currency(
        array $whitelabel,
        array $whitelabel_payment_methods,
        array $user_currency = null
    ):? array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $whitelabel_payment_methods_with_currency = [];
        
        $user_currency_id = null;
        if (!empty($user_currency)) {
            $user_currency_id = (int)$user_currency['id'];
        }
        
        if (empty($whitelabel_payment_methods)) {
            return $whitelabel_payment_methods_with_currency;
        }
        
        foreach ($whitelabel_payment_methods as $key => $whitelabel_payment_method) {
            $whitelabel_id = -1;
            if (!empty($whitelabel)) {
                $whitelabel_id = (int) $whitelabel['id'];
            } else {
                $whitelabel_id = (int) $whitelabel_payment_method['whitelabel_id'];
            }

            $whitelabel_payment_method_id = (int) $whitelabel_payment_method['id'];

            $whitelabel_payment_method_currency = Model_Whitelabel_Payment_Method_Currency::get_single_row_for_whitelabel_payment_id(
                $whitelabel_id,
                $whitelabel_payment_method_id,
                $user_currency_id
            );

            if (!isset($whitelabel_payment_method_currency[0])) {
                $fileLoggerService->error(
                    "There is something wrong with currency for payment method: " . $whitelabel_payment_method_id
                );
                exit("Wrong payment method");
            }
            $most_fit_currency = $whitelabel_payment_method_currency[0];

            $currency_data = [
                "cid" => $most_fit_currency['id'],
                "currency_code" => $most_fit_currency['code'],
                "currency_rate" => $most_fit_currency['rate']
            ];
            $current_data = $whitelabel_payment_methods[$key];

            $new_data = array_merge($current_data, $currency_data);

            $whitelabel_payment_methods_with_currency[$key] = $new_data;
        }
        
        return $whitelabel_payment_methods_with_currency;
    }
    
    /**
     * This function returns min_purchase value from DB for given
     * whitelabel_payment_method_id, but first it tries to return value in
     * user currency, if it dosn't find it tries to get in default currency
     * for whitelabel payment method
     *
     * @param int $whitelabel_payment_method_id Id of whitelabel payment method
     * @param array $user_currency_tab Table consists user currency data
     * @param array $payment_currency_tab Table consists payment currency data
     * @return array|null
     */
    public static function get_min_purchase_for_payment_method(
        int $whitelabel_payment_method_id,
        array $user_currency_tab,
        array $payment_currency_tab
    ):? array {
        $min_purchase_val = null;
                
        $payment_method_currencies = Model_Whitelabel_Payment_Method_Currency::find_by([
            "whitelabel_payment_method_id" => $whitelabel_payment_method_id,
            "currency_id" => $user_currency_tab['id'],
            "is_enabled" => 1
        ]);
        $currency_code = $user_currency_tab['code'];

        if (empty($payment_method_currencies[0])) {
            // Try to find default currency for payment method
            $payment_method_currencies = Model_Whitelabel_Payment_Method_Currency::find_by([
                "whitelabel_payment_method_id" => $whitelabel_payment_method_id,
                "currency_id" => $payment_currency_tab['id'],
                "is_enabled" => 1,
            ]);

            if (empty($payment_method_currencies[0])) {
                exit("There is something wrong with this payment. Please contact us!");
            }
                
            $min_purchase_val = Helpers_Currency::get_recalculated_to_given_currency(
                $payment_method_currencies[0]->min_purchase,
                $payment_currency_tab,
                $user_currency_tab['code'],
                2
            );
            $currency_code = $payment_currency_tab['code'];
        } else {
            $min_purchase_val = $payment_method_currencies[0]->min_purchase;
        }
        
        return [
            $min_purchase_val,
            $currency_code
        ];
    }
    
    /**
     *
     * @param array $whitelabel
     * @param string $amount_in_user_currency
     * @param string $amount_payment
     * @param array $user_currency_tab
     * @param array $gateway_currency_tab
     * @return array
     */
    public static function is_max_deposit_reached(
        array $whitelabel,
        string $amount_in_user_currency,
        string $amount_payment,
        array $user_currency_tab,
        array $gateway_currency_tab
    ): array {
        $max_deposit_reached = false;
        $amount_to_compare = $amount_in_user_currency;
        
        // Get full currency data with settings for user currency
        $user_currency_data = Model_Whitelabel_Default_Currency::get_for_user(
            $whitelabel,
            $user_currency_tab['id']
        );
        $max_deposit_amount_for_user = $user_currency_data['max_deposit_amount'];
        
        $max_deposit = $max_deposit_amount_for_user;
        
        if ((string)$user_currency_tab['code'] !== (string)$gateway_currency_tab['code']) {
            $max_deposit = Helpers_Currency::get_recalculated_to_given_currency(
                $max_deposit_amount_for_user,
                $user_currency_tab,
                $gateway_currency_tab['code']
            );
            $amount_to_compare = $amount_payment;
        }
        
        $max_deposit_in_user_currency = Lotto_View::format_currency(
            $max_deposit_amount_for_user,
            $user_currency_tab['code'],
            true
        );
        
        if ($amount_to_compare > $max_deposit) {
            $max_deposit_reached = true;
        }
        
        return [
            $max_deposit_reached,
            $max_deposit_in_user_currency
        ];
    }
    
    /**
     *
     * @param string $value In most cases this should be value in user currency
     * @param array $from_currency_tab
     * @param array $system_currency_tab
     * @return string
     */
    public static function get_value_in_USD(
        string $value,
        array $from_currency_tab,
        array $system_currency_tab
    ): string {
        $value_usd = $value;
        
        // Only recalculate value if currencies are different
        if ((string)$from_currency_tab['code'] !== (string)$system_currency_tab['code']) {
            $value_usd = Helpers_Currency::get_recalculated_to_given_currency(
                $value,
                $from_currency_tab,
                $system_currency_tab['code']
            );
        }
        
        return $value_usd;
    }
    
    /**
     * Try to get one value among those which are already calculated on different
     * currencies
     *
     * @param array $gateway_currency_tab
     * @param array $user_currency_tab
     * @param string $value_in_user_currency
     * @param array $system_currency_tab
     * @param string $value_in_usd
     * @return string
     */
    public static function get_value_for_payment(
        array $gateway_currency_tab,
        array $user_currency_tab,
        string $value_in_user_currency,
        array $system_currency_tab,
        string $value_in_usd
    ): string {
        $value_payment = "0";
        
        if ((string)$user_currency_tab['code'] === (string)$gateway_currency_tab['code']) {
            $value_payment = $value_in_user_currency;
        } elseif ((string)$system_currency_tab['code'] === (string)$gateway_currency_tab['code']) {
            $value_payment = $value_in_usd;
        } else {
            $value_payment = Helpers_Currency::get_recalculated_to_given_currency(
                $value_in_usd,
                $system_currency_tab,
                $gateway_currency_tab['code']
            );
        }
        
        return $value_payment;
    }
    
    /**
     * Try to get one value among those which are already calculated on different
     * currencies
     *
     * @param array $manager_currency_tab
     * @param array $user_currency_tab
     * @param string $value_in_user_currency
     * @param array $system_currency_tab
     * @param string $value_in_usd
     * @param array $gateway_currency_tab
     * @param string $value_in_gateway_currency
     * @return string
     */
    public static function get_value_for_manager(
        array $manager_currency_tab,
        array $user_currency_tab,
        string $value_in_user_currency,
        array $system_currency_tab,
        string $value_in_usd,
        array $gateway_currency_tab = null,
        string $value_in_gateway_currency = null
    ): string {
        $value_manager = "0";
        
        if ((string)$user_currency_tab['code'] === (string)$manager_currency_tab['code']) {
            $value_manager = $value_in_user_currency;
        } elseif ((string)$system_currency_tab['code'] === (string)$manager_currency_tab['code']) {
            $value_manager = $value_in_usd;
        } elseif (!empty($gateway_currency_tab) &&
            !is_null($value_in_gateway_currency) &&
            (string)$gateway_currency_tab['code'] === (string)$manager_currency_tab['code']
        ) {
            $value_manager = $value_in_gateway_currency;
        } else {
            $value_manager = Helpers_Currency::get_recalculated_to_given_currency(
                $value_in_usd,
                $system_currency_tab,
                $manager_currency_tab['code']
            );
        }
        
        return $value_manager;
    }

    /**
     * ID of USD in currencies (database).
     */
    const USD_ID = 1;

    /**
     * Mass calculate provided values in specified currencies.
     *
     * @param array $values values to be calculated
     * @param integer $value_currency_id original values currency.
     * @param array $target_currency_ids currencies to which values will be calculated
     * @param array $currencies currencies tabs (in language of this class),
     * just pass result of all query on currency database table.
     * @return array result of calculation in form: key = value, value = [currency_id => calculated_value ...] ...
     */
    public static function mass_calculate(array $values, int $value_currency_id, array $target_currency_ids, array $currencies): array
    {
        $calculated_values = [];
        foreach ($values as $key => $value) {
            $calculated_values[$key] = [];
            foreach ($target_currency_ids as $currency_id) {
                $calculated_values[$key][$currency_id] =
                    self::get_recalculated_to_given_currency(
                        $value,
                        $currencies[$value_currency_id],
                        $currencies[$currency_id]['code']
                    );
            }
        }
        return $calculated_values;
    }

    public static function check_is_bonus_balance_in_use(): bool
    {
        $bonus_balance_in_use = true;

        $order = Session::get('order');
        $lotteries = lotto_platform_get_lotteries();
        if (!empty($order)) {
            foreach ($order as $key => $item) {
                $lottery_id = null;
                if (isset($item['lottery'])) {
                    $lottery_id = $item['lottery'];
                } else {
                    $lottery_id = $item[0];
                }
                if (isset($lotteries['__by_id'][$lottery_id])) {
                    $lottery = $lotteries['__by_id'][$lottery_id];
                    if ($lottery['is_bonus_balance_in_use'] === "0") {
                        $bonus_balance_in_use = false;
                    }
                }
            }
        }

        return $bonus_balance_in_use;
    }

    public static function getUserCurrencyTable(): array
    {
        // Set temporary cache for request time
        global $currentUserCurrencyTable;
        if (!empty($currentUserCurrencyTable)) {
            return $currentUserCurrencyTable;
        }

        $user = UserHelper::getUser();
        if (!empty($user)) {
            $currencies = self::getCurrencies();
            $currentUserCurrencyTable = $currencies[$user['currency_id']];;
            return $currencies[$user['currency_id']];
        } else {
            $finalCurrencyCode = (string)Helpers_Currency::get_final_currency_code();
            $currencyCodes = Helpers_Currency::get_currency_map_by_code();
            $currentUserCurrencyTable = $currencyCodes[$finalCurrencyCode];
            return $currencyCodes[$finalCurrencyCode];
        }
    }
}
