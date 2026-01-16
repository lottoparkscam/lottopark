<?php

/**
 * Description of Forms_Wordpress_Myaccount_Ticket_List
 */
class Forms_Wordpress_Myaccount_Ticket_List
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var array
     */
    private $whitelabel = [];

    /**
     *
     * @var array
     */
    private $user = [];
    
    /**
     *
     * @param array $whitelabel
     * @param array $user
     */
    public function __construct($whitelabel, $user)
    {
        $this->whitelabel = $whitelabel;
        $this->user = $user;
    }
    
    /**
     *
     * @return array
     */
    private function prepare_filters(): array
    {
        $filter_add = [];
        $params = [];
        
        if (Input::get("filter.status") != null &&
            Input::get("filter.status") != "a"
        ) {
            $filter_add[] = " AND status = :status";
            $params[] = [":status", intval(Input::get("filter.status"))];
        }

        $filter_add_whole = implode("", $filter_add);

        return [
            $filter_add_whole,
            $params
        ];
    }

    /**
     *
     * @param View $view
     * @param string $tickets_link
     * @return void
     */
    public function process_form(&$view, $tickets_link): void
    {
        $lotteries = Model_Lottery::get_really_all_lotteries_for_whitelabel($this->whitelabel);
        $raffles = Model_Raffle::for_whitelabel($this->whitelabel['id']);
        $currencies = Lotto_Settings::getInstance()->get("currencies");

        list(
            $filter_add,
            $params
            ) = $this->prepare_filters();

        $count = Model_Whitelabel_User_Ticket::get_counted_by_user_and_whitelabel_filtered(
            $this->whitelabel,
            $this->user,
            $params,
            $filter_add,
            'past'
        );

        $config = [
            'pagination_url' => $tickets_link . '?' . http_build_query(Input::get()),
            'total_items' => $count,
            'per_page' => 25,
            'uri_segment' => 'show_page'
        ];
        $pagination = Pagination::forge('ticketspagination', $config);

        $sort = Lotto_Helper::get_sort(
            [
                'draw_date' => 'desc',
                'id' => 'desc',
                'prize' => 'desc',
                'ticket_amount' => 'desc'
            ],
            ['draw_date', 'desc'],
            $tickets_link
        );
        $tickets = Model_Whitelabel_User_Ticket::get_data_by_user_and_whitelabel_filtered(
            $this->whitelabel,
            $this->user,
            $sort,
            $pagination->offset,
            $pagination->per_page,
            $params,
            $filter_add,
            'past'
        );

        $view->set("pages", $pagination);
        $view->set("tickets", $tickets);

        $view->set("currencies", $currencies);
        $view->set("sort", $sort);

        $view->set("lotteries", $lotteries);
        $view->set('raffles', $raffles);
    }

    /**
     *
     * @param View $view
     * @param string $tickets_link
     * @return void
     */
    public function process_form_awaiting(&$view, $tickets_link): void
    {
        $lotteries = Model_Lottery::get_really_all_lotteries_for_whitelabel($this->whitelabel);
        $currencies = Lotto_Settings::getInstance()->get("currencies");

        list(
            $filter_add,
            $params
            ) = $this->prepare_filters();

        $count = Model_Whitelabel_User_Ticket::get_counted_by_user_and_whitelabel_filtered(
            $this->whitelabel,
            $this->user,
            $params,
            $filter_add,
            'awaiting'
        );

        $config = [
            'pagination_url' => $tickets_link . '?' . http_build_query(Input::get()),
            'total_items' => $count,
            'per_page' => 25,
            'uri_segment' => 'show_page'
        ];
        $pagination = Pagination::forge('ticketspagination', $config);

        $sort = Lotto_Helper::get_sort(
            [
                'draw_date' => 'desc',
                'id' => 'desc',
                'prize' => 'desc',
                'ticket_amount' => 'desc',
            ],
            ['draw_date', 'desc'],
            $tickets_link
        );

        $tickets = Model_Whitelabel_User_Ticket::get_data_by_user_and_whitelabel_filtered(
            $this->whitelabel,
            $this->user,
            $sort,
            $pagination->offset,
            $pagination->per_page,
            $params,
            $filter_add,
            'awaiting'
        );

        $view->set("pages", $pagination);
        $view->set("tickets", $tickets);

        $view->set("currencies", $currencies);
        $view->set("sort", $sort);

        $view->set('lotteries', $lotteries);
    }
}
