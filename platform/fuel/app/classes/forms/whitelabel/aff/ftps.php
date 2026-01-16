<?php

/**
 * @deprecated
 * Description of Forms_Whitelabel_Aff_Ftps
 */
class Forms_Whitelabel_Aff_Ftps
{
    /**
     *
     * Get Trait for CSV Export
     */

    use Traits_Gets_Csv;
    
    /**
     * Get Trait for date range preparation
     */
    use Traits_Gets_Date;
    
    /**
     *
     * @var array
     */
    private $whitelabel = [];
    
    /**
     *
     * @var int
     */
    private $items_per_page = 25;
    
    /**
     *
     * @var View
     */
    private $inside = null;

    /**
     *
     * @var array
     */
    private $countries = [];
    
    /**
     *
     * @param array $whitelabel
     * @param array $countries
     */
    public function __construct(array $whitelabel, array $countries)
    {
        $this->whitelabel = $whitelabel;
        $this->countries = $countries;
    }

    /**
     *
     * @return array
     */
    public function get_whitelabel()
    {
        return $this->whitelabel;
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
     * At this moment is unused, but the code responsible for
     * set filters on Front End could be moved here
     * or to Presenter class
     *
     * @return array
     */
    private function prepare_filters_data(): array
    {
        $filters_data = [];

        return $filters_data;
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

        if (Input::get("filter.id") != null) {
            $filter_add[] = " AND whitelabel_user.token = :token";
            $token_ticket_temp = $whitelabel['prefix'] . 'U';
            $token_ticket = str_ireplace($token_ticket_temp, "", Input::get("filter.id"));
            $params[] = [":token", intval($token_ticket)];
        }
        
        if (Input::get("filter.email") != null) {
            $filter_add[] = " AND whitelabel_user.email LIKE :email";
            $params[] = [":email", '%' . Input::get("filter.email") . '%'];
        }
        
        if (Input::get("filter.country") != null &&
            Input::get("filter.country") != "a"
        ) {
            $filter_add[] = " AND whitelabel_user.country = :country";
            $params[] = [":country", Input::get("filter.country")];
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

        return [$filter_add_whole, $params];
    }
    
    /**
     *
     * Prepare FTPS data for CSV
     *
     * @param array $results
     * @return array
     */
    private function prepare_csv($results): array
    {
        $data = [];

        /*Prepare Headers*/
        $headers = [
            _("Affiliate Name"),
            _("Affiliate Login"),
            _("Affiliate Email"),
            _("User Name"),
            _("User ID"),
            _("User Email"),
            _("Country"),
            _("Register country"),
            _("Last country"),
            _("Registered"),
            _("First purchase")
        ];

        /*Prepare Data*/

        foreach ($results as $key => $item) {
            // AFFILIATE
            if (!empty($item['aff_name']) || !empty($item['aff_surname'])) {
                $aff_full_name = $item['aff_name'] . ' ' . $item['aff_surname'];
                $affiliate = Security::htmlentities($aff_full_name);
            } else {
                $affiliate = _("anonymous");
            }

            $affiliate_login = Security::htmlentities($item['aff_login']);
            $affiliate_email = Security::htmlentities($item['aff_email']);

            //USER
            $user = _("anonymous");
            if (!empty($item['name']) || !empty($item['surname'])) {
                $user = Security::htmlentities($item['name'] . ' ' . $item['surname']);
            }

            $user_id = $this->whitelabel['prefix'] . 'U' . $item['token'];
            $user_email = Security::htmlentities($item['email']);

            //COUNTRY
            $country = '';
            if (!empty($item['country'])) {
                $country = Security::htmlentities($this->countries[$item['country']]);
            }

            //REGISTER COUNTRY
            $register_country = '';
            if (!empty($item['register_country'])) {
                $register_country = Security::htmlentities($this->countries[$item['register_country']]);
            }

            //LAST COUNTRY
            $last_country = '';
            if (!empty($item['last_country'])) {
                $last_country = Security::htmlentities($this->countries[$item['last_country']]);
            }

            //REGISTERED
            $registered = Lotto_View::format_date(
                $item['date_register'],
                IntlDateFormatter::MEDIUM,
                IntlDateFormatter::SHORT
            );

            //FIRST PURCHASE
            $first_purchase = '';
            if (!empty($item['first_purchase'])) {
                $first_purchase = Lotto_View::format_date(
                    $item['first_purchase'],
                    IntlDateFormatter::MEDIUM,
                    IntlDateFormatter::SHORT
                );
            }

            /*Add data to array*/

            $data[] = [
                $affiliate,
                $affiliate_login,
                $affiliate_email,
                $user,
                $user_id,
                $user_email,
                $country,
                $register_country,
                $last_country,
                $registered,
                $first_purchase
            ];
        }

        // Return headers and data for CSV
        return [
            'headers' => $headers,
            'data' => $data
        ];
    }
    
    /**
     *
     * @return void
     */
    public function process_form(): void
    {
        $whitelabel = $this->get_whitelabel();
        
        list($filter_add, $params) = $this->prepare_filters();
        
        $inside = View::forge("whitelabel/affs/reports/ftps");
        $inside->set("countries", $this->countries);
        $inside->set("currencies", Lotto_Settings::getInstance()->get("currencies"));
        
        $count = Model_Whitelabel_Aff::count_for_ftps($whitelabel, $filter_add, $params);
        
        $config = [
            'pagination_url' => '/affs/ftps?' . http_build_query(Input::get()),
            'total_items' => $count,
            'per_page' => $this->items_per_page,
            'uri_segment' => 'page'
        ];
        $pagination = Pagination::forge('affspagination', $config);

        $add_limits = " LIMIT :offset, :limit";
        
        $ftpcount = Model_Whitelabel_Aff::get_data_for_ftps(
            $whitelabel,
            $filter_add,
            $params,
            $add_limits,
            $pagination->offset,
            $pagination->per_page
        );
        
        $inside->set("ftpcount", $ftpcount);
        $inside->set("pages", $pagination);

        $this->inside = $inside;
    }
    
    /**
     *
     * @return void
     */
    public function process_form_export(): void
    {
        $whitelabel = $this->get_whitelabel();
        
        list($filter_add, $params) = $this->prepare_filters();
        
        $add_limits = "";
        $ftpcount = Model_Whitelabel_Aff::get_data_for_ftps(
            $whitelabel,
            $filter_add,
            $params,
            $add_limits
        );
        
        /*CSV EXPORT ACTION*/
        // Prepare headers and data for CSV
        $csv_results = $this->prepare_csv($ftpcount);

        // Export CSV
        $this->get_csv_export(
            'ftps',
            $csv_results['headers'],
            $csv_results['data']
        );
    }
}
