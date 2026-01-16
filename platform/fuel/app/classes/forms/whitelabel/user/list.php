<?php

use Models\Whitelabel;
use Services\Logs\FileLoggerService;

class Forms_Whitelabel_User_List
{
    /**
     * Get Trait for date range preparation
     */
    use Traits_Gets_Date;
    
    /**
     *
     * @var array
     */
    private $whitelabel = [];
    private FileLoggerService $fileLoggerService;

    /**
     *
     * @var int
     */
    private $source;
    
    /**
     *
     * @var int
     */
    private $items_per_page = 25;
    
    /**
     * @param int $source
     * @param array $whitelabel
     */
    public function __construct(int $source, array $whitelabel = [])
    {
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        
        if (!empty($source) &&
            $source === Helpers_General::SOURCE_ADMIN
        ) {
            if (Input::get("filter.whitelabel") != null &&
                Input::get("filter.whitelabel") != "a"
            ) {
                $whitelabel_id = intval(Input::get("filter.whitelabel"));
                $whitelabel = Model_Whitelabel::get_single_by_id($whitelabel_id);
            }
        }
        
        $this->source = $source;
        $this->whitelabel = $whitelabel;
    }
    
    /**
     *
     * @return array
     */
    public function get_whitelabel(): array
    {
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
     */
    public function get_prepared_form()
    {
    }
    
    /**
     *
     * @param View $inside
     * @param string $route_param
     * @param int $deleted
     * @param string $link
     * @return void
     */
    public function process_form(
        &$inside,
        string $route_param,
        int $deleted,
        string $link
    ): void {
        // This value depends on source given
        $whitelabel = $this->get_whitelabel();
        $source = $this->get_source();
        
        if (!empty($source) &&
            $source === Helpers_General::SOURCE_ADMIN
        ) {
            // Get all whitelabels for Filters in admin area
            $whitelabels = Model_Whitelabel::find([
                "order_by" => ["id" => "ASC"]
            ]);
            if (!empty($whitelabels)) {
                $inside->set("whitelabels", $whitelabels);
            }
        }

        $countries = Lotto_Helper::get_localized_country_list();
        $languages = Model_Language::get_all_languages();

        $inside->set("languages", $languages);

        list(
            $filter_add,
            $params
        ) = $this->prepare_filters();

        $add = $this->prepare_additional_filters($route_param);

        $sort = $this->prepare_sorting($deleted, $link);

        $users_counted = Model_Whitelabel_User::get_filtered_data_count(
            $whitelabel,
            $params,
            $deleted,
            $add,
            $filter_add
        );
        
        if (is_null($users_counted)) {
            $this->fileLoggerService->error(
                "There is something wrong with users table. Received empty count."
            );
            exit("There is a problem on server");
        }
        
        $count = $users_counted[0]['count'];

        $config = [
            'pagination_url' => $link . '?' . http_build_query(Input::get()),
            'total_items' => $count,
            'per_page' => $this->items_per_page,
            'uri_segment' => 'page'
        ];
        $pagination = Pagination::forge('userspagination', $config);

        $users = null;
        if (!empty($source) && $source == Helpers_General::SOURCE_ADMIN) {
            $users = Model_Whitelabel_User::get_data_joined_with_aff_for_admin(
                $whitelabel,
                $pagination,
                $sort,
                $params,
                $deleted,
                $add,
                $filter_add
            );
        } else {
            $users = Model_Whitelabel_User::get_data_joined_with_aff(
                $whitelabel,
                $pagination,
                $sort,
                $params,
                $deleted,
                $add,
                $filter_add
            );
        }
        
        if (is_null($users)) { // If that situation happend it will be a problem
            $this->fileLoggerService->error(
                "There is something wrong with users table."
            );
            exit("There is a problem on server");
        }

        $currencies = Lotto_Settings::getInstance()->get("currencies");

        $fallback_currency_tab = Helpers_Currency::get_mtab_currency();
        $inside->set("fallback_currency_tab", $fallback_currency_tab);
        
        $inside->set("pages", $pagination);
        $inside->set("sort", $sort);
        $inside->set("users", $users);
        $inside->set("countries", $countries);
        $inside->set("currencies", $currencies);
    }

    /**
     *
     * @param string $route_param
     * @param int $deleted
     * @param string $link
     */
    public function prepare_for_export($route_param, $deleted, $link)
    {
        $languages = Model_Language::get_all_languages();
        
        list(
            $filter_add,
            $params
        ) = $this->prepare_filters();
        
        $add = $this->prepare_additional_filters($route_param);
        
        $sort = $this->prepare_sorting($deleted, $link);
        
        $this->export_data(
            $params,
            $sort,
            $deleted,
            $add,
            $filter_add,
            $route_param,
            $languages
        );
    }
    
    /**
     *
     * @return array
     */
    private function prepare_filters(): array
    {
        $filter_add = [];
        $params = [];
        $whitelabel = $this->get_whitelabel();
        
        if (Input::get("filter.whitelabel") != null && Input::get('filter.whitelabel') != "a") {
            $filter_add[] = " AND whitelabel_user.whitelabel_id = :whitelabel_id";
            $params[] = [":whitelabel_id", intval(Input::get('filter.whitelabel'))];
        }

        if (!empty($whitelabel) && !empty($whitelabel['prefix'])) {
            if (Input::get("filter.id") != null) {
                $filter_add[] = " AND whitelabel_user.token = :token";
                $token_temp = str_ireplace($whitelabel['prefix'] . 'U', "", Input::get("filter.id"));
                $params[] = [":token", intval($token_temp)];
            }
        } else {
            if (Input::get("filter.id") != null) {
                preg_match_all('!\d+!', Input::get("filter.id"), $matches);

                $filter_token = -1;
                if (!empty($matches) && !empty($matches[0])) {
                    $filter_token = $matches[0][0];
                    $filter_add[] = " AND whitelabel_user.token LIKE :token";
                    $params[] = [":token", '%' . $filter_token . '%'];
                }
            }
        }

        if (Input::get("filter.email") != null) {
            $filter_add[] = " AND whitelabel_user.email LIKE :email";
            $params[] = [":email", '%' . Input::get("filter.email") . '%'];
        }
        if (Input::get("filter.language") != null && Input::get("filter.language") != "a") {
            $filter_add[] = " AND whitelabel_user.language_id = :language";
            $params[] = [":language", intval(Input::get("filter.language"))];
        }
        if (Input::get("filter.country") != null && Input::get("filter.country") != "a") {
            $filter_add[] = " AND whitelabel_user.country = :country";
            $params[] = [":country", Input::get("filter.country")];
        }
        if (Input::get("filter.register_ip_country") != null &&
            Input::get("filter.register_ip_country") != "a"
        ) {
            $filter_add[] = " AND whitelabel_user.register_country = :country";
            $params[] = [":country", Input::get("filter.register_ip_country")];
        }
        if (Input::get("filter.last_ip_country") != null &&
            Input::get("filter.last_ip_country") != "a"
        ) {
            $filter_add[] = " AND whitelabel_user.last_country = :country";
            $params[] = [":country", Input::get("filter.last_ip_country")];
        }
        if (Input::get("filter.name") != null) {
            $filter_add[] = " AND whitelabel_user.name LIKE :name";
            $params[] = [":name", '%' . Input::get("filter.name") . '%'];
        }
        if (Input::get("filter.surname") != null) {
            $filter_add[] = " AND whitelabel_user.surname LIKE :surname";
            $params[] = [":surname", '%' . Input::get("filter.surname") . '%'];
        }
        
        if (Input::get("filter.range_start") != '') {
            // get date ranges
            $dates = $this->prepare_dates();
            
            $filter_add[] = " AND date_register >= :date_start";
            $params[] = [":date_start", $dates['date_start']];
            
            $filter_add[] = " AND date_register <= :date_end";
            $params[] = [":date_end", $dates['date_end']];
        }

        $filter_add_whole = implode("", $filter_add);

        return [
            $filter_add_whole,
            $params
        ];
    }
    
    /**
     *
     * @param string $route_param
     * @return string
     */
    private function prepare_additional_filters(string $route_param = "users"): string
    {
        $whitelabel = $this->get_whitelabel();
        $source = $this->get_source();
        
        $additional_filters_string = "";
        if ($source === Helpers_General::SOURCE_ADMIN) {
            switch ($route_param) {
                case "users":
                    $additional_filters_string = " AND whitelabel_user.is_active = 1";
                    break;
                case "inactive":
                    $additional_filters_string = " AND whitelabel_user.is_active = 0";
                    break;
                case "deleted":
                    $additional_filters_string = "";
                    break;
            }
        } else {
            switch ($route_param) {
                case "users":
                    $additional_filters_string = " AND (whitelabel_user.is_active = 1";
                    if (!empty($whitelabel) &&
                        (int)$whitelabel['user_activation_type'] === Helpers_General::ACTIVATION_TYPE_REQUIRED
                    ) {
                        $additional_filters_string .= " AND whitelabel_user.is_confirmed = 1";
                    }
                    $additional_filters_string .= ")";
                    break;
                case "inactive":
                    $additional_filters_string = " AND (whitelabel_user.is_active = 0";
                    if (!empty($whitelabel) &&
                        (int)$whitelabel['user_activation_type'] === Helpers_General::ACTIVATION_TYPE_REQUIRED
                    ) {
                        $additional_filters_string .= " OR whitelabel_user.is_confirmed = 0";
                    }
                    $additional_filters_string .= ")";
                    break;
                case "deleted":
                    $additional_filters_string = "";
                    break;
            }
        }
        
        return $additional_filters_string;
    }
    
    /**
     *
     * @param int $deleted
     * @param string $link
     * @return array
     */
    private function prepare_sorting(int $deleted, string $link): array
    {
        $sort_arr = [
            'name' => 'asc',
            'balance' => 'desc',
            'id' => 'asc',
            'last_active' => 'desc'
        ];

        $defsort = ['id', 'desc'];

        if ((int)$deleted !== 0) {
            unset($sort_arr['date_register']);
            unset($sort_arr['last_active']);
            $sort_arr['date_delete'] = 'desc';
            $defsort = ["date_delete", 'asc'];
        }

        $sort = Lotto_Helper::get_sort($sort_arr, $defsort, $link);

        $xsort = explode(' ', $sort['db']);
        if ($xsort[0] == "name") {
            $sort['db'] = "name " . $xsort[1] . ", surname " . $xsort[1];
        }

        return $sort;
    }
    
    /**
     *
     * @param array $params
     * @param array $sort
     * @param int $deleted
     * @param string $add
     * @param string $filter_add
     * @param string $rparam
     * @param array $languages
     */
    private function export_data(
        &$params,
        &$sort,
        $deleted,
        $add,
        $filter_add,
        $rparam,
        $languages
    ) {
        $currencies = Helpers_Currency::getCurrencies();
        $countries = Lotto_Helper::get_localized_country_list();

        $whitelabel = $this->get_whitelabel();
        $source = $this->get_source();

        if (!empty($source) && $source === Helpers_General::SOURCE_ADMIN) {
            $users_statement = Model_Whitelabel_User::get_filtered_data_full_for_admin_pdo(
                $whitelabel,
                $params,
                $sort,
                $deleted,
                $add,
                $filter_add
            );
        } else {
            $users_statement = Model_Whitelabel_User::get_data_joined_with_aff_pdo(
                $whitelabel,
                null,
                $sort,
                $params,
                $deleted,
                $add,
                $filter_add
            );
        }

        if (is_null($users_statement)) {          // If it will happen this should be cought by exception and logged
            $this->fileLoggerService->error(
                "There is something wrong with users table."
            );
            exit("There is a problem on server");
        }

        // close session before output, so cookie won't clash with our output buffering
        \Fuel\Core\Session::close(true);

        $dt = new DateTime("now", new DateTimeZone("UTC"));
        $filename = $rparam . "_" . $dt->format("Y_m_d-H_i_s") . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');

        $headers = [
            _("User ID"),
        ];

        if (!empty($source) && $source === Helpers_General::SOURCE_ADMIN) {
            $headers[] = _("Whitelabel");
        }

        /** @var Whitelabel $whitelabelModel */
        $whitelabelModel = Container::get('whitelabel');
        $loginIsUsed = $whitelabelModel->loginForUserIsUsedDuringRegistration();
        if (!empty($whitelabel) && $loginIsUsed) {
            $headers[] = _("Login");
        }

        $headers_rest = [
            _("First Name"),
            _("Last Name"),
            _("E-mail"),
            _("Affiliate name"),
            _("Affiliate email"),
            _("Balance"),
            _("Bonus balance"),
            _("Currency"),
            _("Phone Country"),
            _("Phone"),
            _("Country"),
            _("Address #1"),
            _("Address #2"),
            _("City"),
            _("Region"),
            _("Postal/ZIP Code"),
            _("Birthdate"),
            _("Last Active"),
            _("Language"),
            _("Register Date"),
            _("Register IP"),
            _("Register Country"),
            _("Time Zone")
        ];

        $headers = array_merge($headers, $headers_rest);

        fputcsv($output, $headers);

        if (!empty($source) &&
            $source === Helpers_General::SOURCE_WHITELABEL &&
            !empty($whitelabel) &&
            !empty($whitelabel['prefix'])
        ) {
            while ($user = $users_statement->fetch()) {
                $affiliate = $this->prepare_affiliate_csv($user);

                $user_id = $whitelabel['prefix'] . 'U' . $user['token'];

                $currency_code = "";
                if (!empty($user['currency_id']) &&
                    !empty($currencies[$user['currency_id']]) &&
                    !empty($currencies[$user['currency_id']]['code'])
                ) {
                    $currency_code = $currencies[$user['currency_id']]['code'];
                }

                $user_birthdate = '';
                if (!empty($user['birthdate'])) {
                    $user_birthdate = $user['birthdate'];
                }

                $lang_code = "";
                if (!empty($user['language_id']) &&
                    !empty($languages[$user['language_id']]) &&
                    !empty($languages[$user['language_id']]['code'])
                ) {
                    $lang_c = $languages[$user['language_id']]['code'];
                    $lang_code = str_replace('_', '-', $lang_c);
                }

                $user_date_reg = Lotto_View::format_date(
                    $user['date_register'],
                    IntlDateFormatter::MEDIUM,
                    IntlDateFormatter::SHORT
                );

                $register_ip = Security::htmlentities($user['register_ip']);

                $register_country = '';
                if (!empty($user['register_country']) &&
                    !empty($countries[$user['register_country']])
                ) {
                    $register_country = $countries[$user['register_country']];
                }

                $data_to_insert = [$user_id];

                /** @var Whitelabel $whitelabelModel */
                $whitelabelModel = Container::get('whitelabel');
                $loginIsUsed = $whitelabelModel->loginForUserIsUsedDuringRegistration();
                if (!empty($whitelabel) && $loginIsUsed) {
                    $data_to_insert[] = $user['login'];
                }

                array_push(
                    $data_to_insert,
                    $user['name'],
                    $user['surname'],
                    $user['email'],
                    $affiliate['name'],
                    $affiliate['email'],
                    $user['balance'],
                    $user['bonus_balance'],
                    $currency_code,
                    $user['phone_country'],
                    $user['phone'],
                    $user['country'],
                    $user['address_1'],
                    $user['address_2'],
                    $user['city'],
                    $user['state'],
                    $user['zip'],
                    $user_birthdate,
                    $user['last_active'],
                    $lang_code,
                    $user_date_reg,
                    $register_ip,
                    $register_country,
                    $user['timezone']
                );

                fputcsv($output, $data_to_insert);
                ob_flush();
                flush();
            }
        } elseif (!empty($source) && $source === Helpers_General::SOURCE_ADMIN) {
            while ($user = $users_statement->fetch()) {
                $affiliate = $this->prepare_affiliate_csv($user);

                $user_id = $user['pref'] . 'U' . $user['token'];

                $currency_code = "";
                if (!empty($user['currency_id']) &&
                    !empty($currencies[$user['currency_id']]) &&
                    !empty($currencies[$user['currency_id']]['code'])
                ) {
                    $currency_code = $currencies[$user['currency_id']]['code'];
                }

                $user_birthdate = '';
                if (!empty($user['birthdate'])) {
                    $user_birthdate = $user['birthdate'];
                }

                $lang_code = "";
                if (!empty($user['language_id']) &&
                    !empty($languages[$user['language_id']]) &&
                    !empty($languages[$user['language_id']]['code'])
                ) {
                    $lang_c = $languages[$user['language_id']]['code'];
                    $lang_code = str_replace('_', '-', $lang_c);
                }

                $user_date_reg = Lotto_View::format_date(
                    $user['date_register'],
                    IntlDateFormatter::MEDIUM,
                    IntlDateFormatter::SHORT
                );

                $register_ip = Security::htmlentities($user['register_ip']);

                $register_country = '';
                if (!empty($user['register_country']) &&
                    !empty($countries[$user['register_country']])
                ) {
                    $register_country = $countries[$user['register_country']];
                }

                $data_to_insert = [
                    $user_id,
                    $user['w_name'] . ' - ' . $user['w_domain'],
                    $user['name'],
                    $user['surname'],
                    $user['email'],
                    $affiliate['name'],
                    $affiliate['email'],
                    $user['balance'],
                    $user['bonus_balance'],
                    $currency_code,
                    $user['phone_country'],
                    $user['phone'],
                    $user['country'],
                    $user['address_1'],
                    $user['address_2'],
                    $user['city'],
                    $user['state'],
                    $user['zip'],
                    $user_birthdate,
                    $user['last_active'],
                    $lang_code,
                    $user_date_reg,
                    $register_ip,
                    $register_country,
                    $user['timezone']
                ];

                fputcsv($output, $data_to_insert);
                ob_flush();
                flush();
            }
        }

        fclose($output);
        exit();
    }

    /**
     *
     * @param object $user
     */
    private function prepare_affiliate_csv($user)
    {
        $email = "";
        if (!empty($user['whitelabel_aff_id'])) {
            // Name, surname
            if (!empty($user['aff_name']) || !empty($user['aff_surname'])) {
                $full_name = $user['aff_name'];
                $full_name .= ' ';
                $full_name .= $user['aff_surname'];
                $name = Security::htmlentities($full_name);
            } else {
                $name = _("anonymous");
            }

            // Email
            $email = Security::htmlentities($user['aff_email']);
            if ($user['aff_is_confirmed'] == 1) {
                $email .= ' (confirmed)';
            } else {
                $email .= ' (not confirmed)';
            }
        } else {
            $name = _("None");
        }

        return [
            'name' => $name,
            'email' => (!empty($email)) ? $email : ''
        ];
    }
}
