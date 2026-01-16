<?php

use Services\Logs\FileLoggerService;

/**
 * Description of Forms_Whitelabel_User_View
 */
class Forms_Whitelabel_User_View extends Forms_Main
{
    /**
     * This is in fact token
     *
     * @var int
     */
    private $param_id;
    
    /**
     *
     * @var array
     */
    private $whitelabel = [];
    
    /**
     *
     * @var string
     */
    private $domain = "";
    private FileLoggerService $fileLoggerService;

    /**
     *
     * @var int
     */
    private $source;
    
    /**
     *
     * @var View
     */
    private $inside = null;
    
    /**
     *
     * @param int $source
     * @param int $param_id
     * @param array $whitelabel
     */
    public function __construct(int $source, $param_id = null, $whitelabel = [])
    {
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        
        $this->source = $source;
        $this->param_id = $param_id;
        $this->whitelabel = $whitelabel;
        
        if ($this->source === Helpers_General::SOURCE_ADMIN) {
            $user_obj = Model_Whitelabel_User::find_by_pk($this->param_id);
            // Get whitelabel for selected user
            $whitelabel = Model_Whitelabel::get_single_by_id($user_obj->whitelabel_id);
            $this->whitelabel = $whitelabel;
        } else {
            if (!empty($param_id) && empty($whitelabel)) {      // This is only in the case of admin page
                $domain = Model_Whitelabel::get_domain_for_user($this->param_id);
                if (is_null($domain)) {     // This could be a problem if it will be null!!!
                    $this->fileLoggerService->error(
                        "Lack of settings for domain in DB. Name of domain: " . $domain
                    );
                    exit();
                }
                $this->domain = $domain;

                $whitelabel = Model_Whitelabel::get_by_domain($this->domain);
                if (is_null($whitelabel)) {     // This could be a problem if it will be null!!!
                    $this->fileLoggerService->error(
                        "Lack of settings for whitelabel in DB. Whitelabel: " . json_endcode($domain)
                    );
                    exit();
                }
                $this->whitelabel = $whitelabel;
            }
        }
    }
    
    /**
     *
     * @return null|array
     */
    public function get_whitelabel(): array
    {
        $result = null;
        
        if (empty($this->whitelabel)) {
            if ($this->source === Helpers_General::SOURCE_ADMIN) {
                $user_obj = Model_Whitelabel_User::find_by_pk($this->param_id);
                // Get whitelabel for selected user
                $whitelabel = Model_Whitelabel::get_single_by_id($user_obj->whitelabel_id);
                $this->whitelabel = $whitelabel;
            } else {
                $domain = Model_Whitelabel::get_domain_for_user($this->get_param_id());
                if (is_null($domain)) {     // This could be a problem if it will be null!!!
                    return $result;
                }
                $this->domain = $domain;

                $whitelabel = Model_Whitelabel::get_by_domain($domain);
                if (is_null($whitelabel)) {     // This could be a problem if it will be null!!!
                    return $result;
                }
                $this->whitelabel = $whitelabel;
            }
        }
        
        return $this->whitelabel;
    }

    /**
     *
     * @return int
     */
    public function get_source(): int
    {
        return $this->source;
    }
    
    /**
     *
     * @param array $whitelabel
     */
    public function set_whitelabel($whitelabel)
    {
        $this->whitelabel = $whitelabel;
    }
    
    /**
     *
     * @return int
     */
    public function get_param_id(): int
    {
        return $this->param_id;
    }

    /**
     *
     * @param int $param_id
     */
    public function set_param_id($param_id)
    {
        $this->param_id = $param_id;
    }
    
    /**
     *
     * @return View
     */
    public function get_inside()
    {
        return $this->inside;
    }
    
    /**
     *
     * @param array $user
     * @return array
     */
    private function prepare_user_view_data($user): array
    {
        $countries = Lotto_Helper::get_localized_country_list();
        $timezones = Lotto_Helper::get_timezone_list();
        $languages = Model_Language::get_all_languages();

        $user_data = [];
        $user_data['email'] = Security::htmlentities($user['email']);

        $user_id_temp = "-";
        if (!empty($user['token'])) {
            $user_id_temp = $this->whitelabel['prefix'] . 'U' . $user['token'];
        }
        $user_data['id'] = Security::htmlentities($user_id_temp);

        $user_name_temp = _("Anonymous");
        if (!empty($user['name'])) {
            $user_name_temp = $user['name'];
        }
        $user_data['name'] = Security::htmlentities($user_name_temp);

        $user_surname_temp = _("Anonymous");
        if (!empty($user['surname'])) {
            $user_surname_temp = $user['surname'];
        }
        $user_data['surname'] = Security::htmlentities($user_surname_temp);

        $login_temp = "-";
        if (!empty($user['login'])) {
            $login_temp = $user['login'];
        }
        $user_data['login'] = Security::htmlentities($login_temp);

        $user_data['is_confrimed_class'] = Lotto_View::show_boolean_class($user['is_confirmed']);
        $user_data['is_confrimed_value'] = Lotto_View::show_boolean($user['is_confirmed']);

        $user_country_temp = "-";
        if (!empty($user['country']) && isset($countries[$user['country']])) {
            $user_country_temp = $countries[$user['country']];
        }
        $user_data['country'] = Security::htmlentities($user_country_temp);

        $user_city_temp = "-";
        if (!empty($user['city'])) {
            $user_city_temp = $user['city'];
        }
        $user_data['city'] = Security::htmlentities($user_city_temp);

        $user_state_temp = "-";
        if (!empty($user['state'])) {
            $user_state_temp = Lotto_View::get_region_name($user['state']);
        }
        $user_data['state'] = Security::htmlentities($user_state_temp);

        $user_address1_temp = "-";
        if (!empty($user['address_1'])) {
            $user_address1_temp = $user['address_1'];
        }
        $user_data['address_1'] = Security::htmlentities($user_address1_temp);

        $user_address2_temp = "-";
        if (!empty($user['address_2'])) {
            $user_address2_temp = $user['address_2'];
        }
        $user_data['address_2'] = Security::htmlentities($user_address2_temp);

        $user_zip_temp = "-";
        if (!empty($user['zip'])) {
            $user_zip_temp = $user['zip'];
        }
        $user_data['zip'] = Security::htmlentities($user_zip_temp);

        $user_birthdate_temp = "-";
        if (!empty($user['birthdate'])) {
            $user_birthdate_temp = Lotto_View::format_date(
                $user['birthdate'],
                IntlDateFormatter::MEDIUM,
                IntlDateFormatter::NONE
            );
        }
        $user_data['birthdate'] = $user_birthdate_temp;

        $user_phone_temp = "-";
        if (!empty($user['phone']) && !empty($user['phone_country'])) {
            $user_phone_temp = Lotto_View::format_phone(
                $user['phone'],
                $user['phone_country']
            );
            if (isset($countries[$user['phone_country']])) {
                $user_phone_temp .= ' (' . $countries[$user['phone_country']] . ')';
            }
        }
        $user_data['phone'] = Security::htmlentities($user_phone_temp);

        $user_timezone_temp = "-";
        if (!empty($user['timezone']) && isset($timezones[$user['timezone']])) {
            $user_timezone_temp = $timezones[$user['timezone']];
        }
        $user_data['timezone'] = Security::htmlentities($user_timezone_temp);

        // TODO: {Vordis 2019-05-27 14:26:59} this should be in presenter (low priority)
        $user_data['gender'] = "-";
        $genders = Helpers_Fuel_Resources::get_genders();
        if (!empty($user['gender']) && isset($genders[$user['gender']])) {
            $user_data['gender'] = Security::htmlentities($genders[$user['gender']]);
        }

        $user_data['national_id'] = "-";
        if (!empty($user['national_id'])) {
            $user_data['national_id'] = Security::htmlentities($user['national_id']);
        }

        $user_language_temp = "-";
        if (!empty($user['language_id']) && isset($languages[$user['language_id']]['code'])) {
            $user_language_temp = Lotto_View::format_language(
                $languages[$user['language_id']]['code']
            );
        }
        $user_data['language'] = Security::htmlentities($user_language_temp);

        $user_balance_text = Lotto_View::format_currency(
            $user['balance'],
            $user['user_currency_code'],
            true
        );
        $user_data['balance'] = $user_balance_text;
        
        $balance_currency_tab = [
            'id' => $user['user_currency_id'],
            'code' => $user['user_currency_code'],
            'rate' => $user['user_currency_rate'],
        ];
        $balance_in_manager_curr = Helpers_Currency::get_recalculated_to_given_currency(
            $user['balance'],
            $balance_currency_tab,
            $user['manager_currency_code']
        );
        $balance_in_manager_curr_full = Lotto_View::format_currency(
            $balance_in_manager_curr,
            $user['manager_currency_code'],
            true
        );
        $user_data['balance_in_manager'] = $balance_in_manager_curr_full;

        $user_bonus_balance_text = Lotto_View::format_currency(
            $user['bonus_balance'],
            $user['user_currency_code'],
            true
        );
        $user_data['bonus_balance'] = $user_bonus_balance_text;
        
        $bonus_balance_in_manager_curr = Helpers_Currency::get_recalculated_to_given_currency(
            $user['bonus_balance'],
            $balance_currency_tab,
            $user['manager_currency_code']
        );
        $bonus_balance_in_manager_curr_full = Lotto_View::format_currency(
            $bonus_balance_in_manager_curr,
            $user['manager_currency_code'],
            true
        );
        $user_data['bonus_balance_in_manager'] = $bonus_balance_in_manager_curr_full;
        
        $user_data['show_user_balance'] = false;
        if ($user['user_currency_code'] !== $user['manager_currency_code']) {
            $user_data['show_user_balance'] = true;
        }

        $user_data['date_register'] = Lotto_View::format_date(
            $user['date_register'],
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::SHORT
        );

        $user_register_ip_temp = $user['register_ip'];
        if (!empty($user['register_country']) &&
            isset($countries[$user['register_country']])
        ) {
            $user_register_ip_temp .= " (" . $countries[$user['register_country']] . ")";
        }
        $user_data['register_ip'] = Security::htmlentities($user_register_ip_temp);

        $user_last_ip_temp = $user['last_ip'];
        if (!empty($user['last_country']) &&
            isset($countries[$user['last_country']])
        ) {
            $user_last_ip_temp .= " (" . $countries[$user['last_country']] . ")";
        }
        $user_data['last_ip'] = Security::htmlentities($user_last_ip_temp);

        $user_data['last_active'] = Lotto_View::format_date(
            $user['last_active'],
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::SHORT
        );

        $user_data['last_update'] = Lotto_View::format_date(
            $user['last_update'],
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::SHORT
        );

        $user_first_purchase_temp = "-";
        if (!empty($user['first_purchase'])) {
            $user_first_purchase_temp = Lotto_View::format_date(
                $user['first_purchase'],
                IntlDateFormatter::MEDIUM,
                IntlDateFormatter::SHORT
            );
        }
        $user_data['first_purchase'] = $user_first_purchase_temp;

        return $user_data;
    }
    
    /**
     *
     * @param string $view_template
     * @return int
     */
    public function process_form(string $view_template): int
    {
        $source = $this->source;
        $param_id = $this->get_param_id();
        
        $ending_url = $param_id . Lotto_View::query_vars();
        
        if ($source === Helpers_General::SOURCE_ADMIN) {
            $user = Model_Whitelabel_User::get_user_with_currencies_by_id($param_id);
        } else {
            $user = Model_Whitelabel_User::get_user_with_currencies_by_token(
                $param_id,
                $this->get_whitelabel()
            );
        }
        
        if ($user !== null) {
            $users_urls = [
                'main' => "/users" . Lotto_View::query_vars(),
                'email_edit' => '/users/email/' . $ending_url,
                'password' => '/users/password/' . $ending_url,
                'edit' => '/users/edit/' . $ending_url
            ];
            
            $this->inside = View::forge($view_template);
            $this->inside->set("users_urls", $users_urls);
            
            $user_data = $this->prepare_user_view_data($user);
            $this->inside->set("user_data", $user_data);
        } else {
            return self::RESULT_INCORRECT_USER;
        }
        
        return self::RESULT_OK;
    }
}
