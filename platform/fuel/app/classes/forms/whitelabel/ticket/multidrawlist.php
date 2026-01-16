<?php

/**
 * Description of Forms_Whitelabel_Ticket_List
 */
class Forms_Whitelabel_Ticket_Multidrawlist extends Forms_Main
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
     *
     * @var View
     */
    private $inside = null;

    /**
     * @param int $source
     * @param array $whitelabel
     */
    public function __construct(int $source, $whitelabel = [])
    {
        if (!empty($source) && $source === Helpers_General::SOURCE_ADMIN) {
            if (Input::get("filter.whitelabel") != null &&
                Input::get("filter.whitelabel") != "a"
            ) {
                $whitelabel = [];
                $whitelabel['id'] = intval(Input::get("filter.whitelabel"));
            }
        }

        $this->source = $source;
        $this->whitelabel = $whitelabel;
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
     *
     * @return array
     */
    private function prepare_filters(): array
    {
        $filter_add = [];
        $params = [];
        $whitelabel = $this->get_whitelabel();

        if (Input::get("filter.id") != null) {
            $filter_add[] = " AND whitelabel_user_ticket.token = :token";
            $token_ticket_temp = $whitelabel['prefix'] . 'T';
            $token_ticket = str_ireplace($token_ticket_temp, "", Input::get("filter.id"));
            $params[] = [":token", intval($token_ticket)];
        }
        if (Input::get("filter.transactionid") != null) {
            $filter_add[] = " AND whitelabel_transaction.token = :ptoken";
            $token_transaction_temp = $whitelabel['prefix'] . 'P';
            $token_transaction = str_ireplace($token_transaction_temp, "", Input::get("filter.transactionid"));
            $params[] = [":ptoken", intval($token_transaction)];
        }
        if (Input::get("filter.userid") != null) {
            $filter_add[] = " AND whitelabel_user.token = :utoken";
            $token_user_temp = $whitelabel['prefix'] . 'U';
            $token_user = str_ireplace($token_user_temp, "", Input::get("filter.userid"));
            $params[] = [":utoken", intval($token_user)];
        }
        if (Input::get("filter.status") != null && Input::get("filter.status") != "a") {
            $filter_add[] = " AND whitelabel_user_ticket.status = :status";
            $params[] = [":status", intval(Input::get("filter.status"))];
        }
        if (Input::get("filter.payout") != null && Input::get("filter.payout") != "a") {
            $payout_t = " AND whitelabel_user_ticket.status = ";
            $payout_t .= Helpers_General::TICKET_STATUS_WIN;
            $payout_t .= " AND whitelabel_user_ticket.payout = :payout";
            $filter_add[] = $payout_t;
            $params[] = [":payout", intval(Input::get("filter.payout"))];
        }
        if (Input::get("filter.lottery") != null && Input::get("filter.lottery") != "a") {
            $filter_add[] = " AND whitelabel_user_ticket.lottery_id = :lottery";
            $params[] = [":lottery", intval(Input::get("filter.lottery"))];
        }
        if (Input::get("filter.date") != null && Input::get("filter.date") != "a") {
            $date = DateTime::createFromFormat('m/d/Y', Input::get("filter.date"));
            if ($date !== false) {
                $filter_add[] = " AND whitelabel_user_ticket.draw_date = :date";
                $params[] = [":date", $date->format(Helpers_Time::DATETIME_FORMAT)];
            }
        }
        if (Input::get("filter.multidrawid") != null) {
            $filter_add[] = " AND multi_draw.token = :mdtoken";
            $token_multidraw_temp = $whitelabel['prefix'] . 'M';
            $token_multidraw = str_ireplace($token_multidraw_temp, "", Input::get("filter.multidrawid"));
            $params[] = [":mdtoken", intval($token_multidraw)];
        }
        if (Input::get("filter.email") != null) {
            $filter_add[] = " AND whitelabel_user.email LIKE :email";
            $params[] = [":email", '%' . Input::get("filter.email") . '%'];
        }
        if (Input::get("filter.range_start") != '') {
            // get date ranges
            $dates = $this->prepare_dates();

            $filter_add[] = " AND multi_draw.date >= :date_start";
            $params[] = [":date_start", $dates['date_start']];

            $filter_add[] = " AND multi_draw.date <= :date_end";
            $params[] = [":date_end", $dates['date_end']];
        }

        $filter_add_whole = implode("", $filter_add);

        return [$filter_add_whole, $params];
    }

    /**
     *
     * @return array
     */
    private function prepare_filters_data(): array
    {
        $filters_data = [];

        $date = '';
        if (!is_null(Input::get("filter.date"))) {
            $date = Input::get("filter.date");
        }
        $filters_data['date'] = Security::htmlentities($date);

        $filters_data['first_day_of_week'] = Lotto_View::get_first_day_of_week();

        $ticket_id = '';
        if (!is_null(Input::get("filter.id"))) {
            $ticket_id = Input::get("filter.id");
        }
        $filters_data['ticket_id'] = Security::htmlentities($ticket_id);

        $transaction_id = '';
        if (!is_null(Input::get("filter.transactionid"))) {
            $transaction_id = Input::get("filter.transactionid");
        }
        $filters_data['transaction_id'] = Security::htmlentities($transaction_id);

        $user_id = '';
        if (!is_null(Input::get("filter.userid"))) {
            $user_id = Input::get("filter.userid");
        }
        $filters_data['user_id'] = Security::htmlentities($user_id);

        $multidraw_id = '';
        if (!is_null(Input::get("filter.multidrawid"))) {
            $multidraw_id = Input::get("filter.multidrawid");
        }
        $filters_data['multidraw_id'] = Security::htmlentities($multidraw_id);

        $email = '';
        if (!is_null(Input::get("filter.email"))) {
            $email = Input::get("filter.email");
        }
        $filters_data['email'] = Security::htmlentities($email);

        $range_start = '';
        if (!empty(Input::get("filter.range_start"))) {
            $range_start = Input::get("filter.range_start");
        }
        $filters_data['range_start'] = Security::htmlentities($range_start);

        $range_end = '';
        if (!empty(Input::get("filter.range_end"))) {
            $range_end = Input::get("filter.range_end");
        }
        $filters_data['range_end'] = Security::htmlentities($range_end);

        return $filters_data;
    }

    /**
     *
     * @param array $tickets
     * @return array
     */
    private function prepare_tickets_data(array $tickets): array
    {
        $whitelabel = $this->get_whitelabel();

        $all_lotteries = Model_Lottery::get_really_all_lotteries_for_whitelabel($whitelabel);

        $tickets_data = [];

        foreach ($tickets as $ticket) {
            $lottery = $all_lotteries["__by_id"][$ticket['lottery_id']];

            $ticket_data = [];

            $ttoken = "";
            $ticket_token = "-";
            if (!empty($ticket['token'])) {
                $ticket_token = $whitelabel['prefix'] . 'M' . $ticket['token'];
                $ttoken = $ticket['token'];
            }
            $ticket_data['multidraw_token'] = Security::htmlentities($ticket_token);
            $ticket_data['token'] = $ticket['token'];

            $ptoken = "";

            $lottery_name = "";
            if (!empty($lottery['name'])) {
                $lottery_name = _($lottery['name']);
            }
            $ticket_data['lottery_name'] = Security::htmlentities($lottery_name);

            $utoken = "";
            $user_token = "-";
            if (!empty($ticket['utoken'])) {
                $user_token = $whitelabel['prefix'] . 'U' . $ticket['utoken'];
                $utoken = $ticket['utoken'];
            }
            $ticket_data['user_token'] = Security::htmlentities($user_token);

            $user_fullname = _("Anonymous");
            if (!empty($ticket['name']) || !empty($ticket['surname'])) {
                $user_fullname = $ticket['name'] . ' ' . $ticket['surname'];
            }
            $ticket_data['user_fullname'] = Security::htmlentities($user_fullname);

            $user_login = "-";
            if (!empty($ticket['user_login'])) {
                $user_login = $ticket['user_login'];
            }
            $ticket_data['user_login'] = Security::htmlentities($user_login);
            
            $ticket_data['user_email'] = Security::htmlentities($ticket['email']);

            $ticket_data['date'] = Lotto_View::format_date(
                $ticket['date'],
                IntlDateFormatter::MEDIUM,
                IntlDateFormatter::SHORT
            );

            $ticket_data['tickets'] = $ticket['tickets'];

            $ticket_data['first_draw'] = $ticket['first_draw'];
            $ticket_data['valid_to_draw'] = $ticket['valid_to_draw'];
            $ticket_data['current_draw'] = $ticket['current_draw'];

            $ticket_data['is_finished'] = $ticket['is_finished'];

            $ticket_data['tickets_url'] = '/tickets?filter[multidrawid]='.$ticket_data['multidraw_token'];

            $tickets_data[] = $ticket_data;
        }

        return $tickets_data;
    }

    /**
     *
     * @param string $view_template
     * @return int
     */
    public function process_form($view_template): int
    {
        $whitelabel = $this->get_whitelabel();
        $ticket_statuses = Helpers_General::get_ticket_statuses();
        $ticket_payouts = Helpers_General::get_ticket_payouts();

        $filters_data = $this->prepare_filters_data();

        list($filter_add, $params) = $this->prepare_filters();

        $count = Model_Multidraw::get_counted_by_whitelabel_filtered(
            $whitelabel,
            $params,
            $filter_add
        );

        if (is_null($count)) {
            return self::RESULT_NULL_COUNTED;
        }

        $config = [
            'pagination_url' => '/multidraw_tickets' . '?' . http_build_query(Input::get()),
            'total_items' => $count,
            'per_page' => $this->items_per_page,
            'uri_segment' => 'page'
        ];
        $pagination = Pagination::forge('ticketspagination', $config);

        $sort = Lotto_Helper::get_sort(
            [
                'id' => 'desc',
                'amount' => 'desc',
                'draw_date' => 'desc',
                'prize' => 'desc'
            ],
            ['id', 'desc'],
            '/tickets'
        );

        $tickets = Model_Multidraw::get_full_data_paid_for_whitelabel(
            $whitelabel,
            $pagination,
            $sort,
            $params,
            $filter_add
        );

        $lotteries = Model_Lottery::get_all_lotteries_for_whitelabel($whitelabel);
        $tickets_data = $this->prepare_tickets_data($tickets);

        $this->inside = View::forge($view_template);
        $this->inside->set("lotteries", $lotteries);
        $this->inside->set("ticket_statuses", $ticket_statuses);
        $this->inside->set("ticket_payouts", $ticket_payouts);
        $this->inside->set("filters_data", $filters_data);
        $this->inside->set("tickets_data", $tickets_data);
        $this->inside->set("pages", $pagination);
        $this->inside->set("sort", $sort);

        return self::RESULT_OK;
    }
}
