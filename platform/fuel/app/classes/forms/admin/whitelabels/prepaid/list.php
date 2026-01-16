<?php

/**
 * Description of Forms_Admin_Whitelabels_Prepaid_List
 */
class Forms_Admin_Whitelabels_Prepaid_List extends Forms_Main
{
    /**
     *
     * @var int
     */
    private $source = 0;
    
    /**
     *
     * @var array
     */
    private $whitelabel = [];
    
    /**
     *
     * @var null|Presenter_Admin_Whitelabels_Prepaid_List
     */
    private $inside = null;

    /**
     *
     * @var int
     */
    private $results_per_page = 100;
    
    /**
     *
     * @var string
     */
    private $manager_currency_code = "";
    
    /**
     *
     * @param int $source
     * @param array $whitelabel
     * @param int $request_page If null it will be set to 1
     */
    public function __construct(
        int $source,
        array $whitelabel,
        int $request_page = null
    ) {
        $this->source = $source;
        $this->whitelabel = $whitelabel;
        
        if ($request_page !== null) {
            $this->request_page = $request_page;
        } else {
            $this->request_page = 1;
        }
        
        $manager_currency_tab = Helpers_Currency::get_mtab_currency(
            true,
            "",
            (int)$this->whitelabel['manager_site_currency_id']
        );
        
        $this->manager_currency_code = $manager_currency_tab['code'];
    }

    /**
     *
     * @return int
     */
    public function get_request_page()
    {
        return $this->request_page;
    }
    
    /**
     *
     * @param string $path_to_view
     * @return \Forms_Admin_Whitelabels_Prepaid_List
     */
    public function set_inside_by_path_to_view(
        string $path_to_view
    ): Forms_Admin_Whitelabels_Prepaid_List {
        $this->inside = Presenter::forge($path_to_view);
        return $this;
    }
    
    /**
     *
     * @return Presenter_Whitelabel_Prepaid_List
     */
    public function get_inside(): Presenter_Presenter
    {
        return $this->inside;
    }

    /**
     *
     * @return int
     */
    private function get_count(): int
    {
        $prepaid_count = Model_Whitelabel_Prepaid::count(
            null,
            true,
            [
                "whitelabel_id" => $this->whitelabel['id']
            ]
        );
        
        return $prepaid_count;
    }
    
    /**
     *
     * @return \Pagination|null
     */
    private function get_pagination():? Pagination
    {
        $prepaid_count = $this->get_count();
        
        $pagination_url = "";
        $uri_segments = 0;
        if ((int)$this->source === Helpers_General::SOURCE_ADMIN) {
            $pagination_url = '/whitelabels/prepaid/' .
                $this->whitelabel['id'] . '/s/';
            $uri_segments = 5;
        } else {
            $pagination_url = '/prepaid/s/';
            $uri_segments = 3;
        }
        
        $config = [
            'pagination_url' => $pagination_url,
            'total_items' => $prepaid_count,
            'per_page' => $this->results_per_page,
            'uri_segment' => $uri_segments
        ];
        $pagination = Pagination::forge('prepaidpagination', $config);
        
        return $pagination;
    }
    
    /**
     *
     * @param Pagination $pagination
     * @return array
     */
    private function get_prepaids_list(Pagination $pagination):? array
    {
        $prepaids_list = Model_Whitelabel_Prepaid::fetch_for_whitelabel(
            $this->whitelabel['id'],
            $pagination
        );
        
        return $prepaids_list;
    }
    
    /**
     *
     * @return string
     */
    public function get_sum_of_prepaids(): string
    {
        $result = Model_Whitelabel_Prepaid::get_sum_by_whitelabel($this->whitelabel['id']);
        
        return $result;
    }
    
    /**
     *
     * @param string $sum
     * @return array
     */
    public function get_sum_formatted_plus_alert_class(string $sum): array
    {
        $sum_to_show = Lotto_View::format_currency(
            $sum,
            $this->manager_currency_code,
            true
        );
        
        $sum_prepaid_class = "success";
        if ((int) $sum < 0) {
            $sum_prepaid_class = "danger";
        } elseif ($this->whitelabel['prepaid_alert_limit'] > $sum) {
            $sum_prepaid_class = "warning";
        }
        
        return [
            $sum_prepaid_class,
            $sum_to_show
        ];
    }
    
    /**
     *
     * @return int
     */
    public function process_form(): int
    {
        // Get all languages
        $whitelabel_languages = Model_Whitelabel_Language::get_whitelabel_languages($this->whitelabel);
        
        $pagination = $this->get_pagination();
        $prepaids_list = $this->get_prepaids_list($pagination);
        
        if (is_null($prepaids_list)) {
            return self::RESULT_NULL_DATA;
        }
        
        $sum = $this->get_sum_of_prepaids();

        list(
            $sum_prepaid_class,
            $sum_to_show
        ) = $this->get_sum_formatted_plus_alert_class($sum);
        
        $this->inside->set("whitelabel", $this->whitelabel);
        $this->inside->set("langs", $whitelabel_languages);
        $this->inside->set("manager_currency_code", $this->manager_currency_code);
        $this->inside->set("sum_to_show", $sum_to_show);
        $this->inside->set("sum_prepaid_class", $sum_prepaid_class);
        $this->inside->set("prepaids", $prepaids_list);
        $this->inside->set("pages", $pagination);
        $this->inside->set("page", $this->get_request_page());
        
        return self::RESULT_OK;
    }
}
