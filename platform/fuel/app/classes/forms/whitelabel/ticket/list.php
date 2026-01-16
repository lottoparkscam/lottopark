<?php

use Carbon\Carbon;
use Services\Logs\FileLoggerService;

/**
 * Description of Forms_Whitelabel_Ticket_List
 */
class Forms_Whitelabel_Ticket_List extends Forms_Main
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
    private FileLoggerService $fileLoggerService;

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

        $this->fileLoggerService = Container::get(FileLoggerService::class);

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
            $date = Carbon::createFromFormat('m/d/Y', Input::get("filter.date"));
            if ($date !== false) {
                $filter_add[] = " AND (whitelabel_user_ticket.draw_date BETWEEN :date_start AND :date_end)";

                $date->setTime(0,0,0);
                $params[] = [":date_start", $date->format(Helpers_Time::DATETIME_FORMAT)];

                $date->setTime(23,59,59);
                $params[] = [":date_end", $date->format(Helpers_Time::DATETIME_FORMAT)];
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

            $filter_add[] = " AND whitelabel_user_ticket.date >= :date_start";
            $params[] = [":date_start", $dates['date_start']];

            $filter_add[] = " AND whitelabel_user_ticket.date <= :date_end";
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
            if (!isset($all_lotteries["__by_id"][$ticket['lottery_id']])) {
                $msg = "There is a problem with lottery settings. " .
                    "No lottery within lotteries list. Lottery ID: " .
                    $ticket['lottery_id'] . " " .
                    "Ticket ID: " .
                    $ticket['id'] . " " .
                    "Whitelabel ID: " . $whitelabel['id'];

                $this->fileLoggerService->error(
                    $msg
                );
                
                continue;
            }
            $lottery = $all_lotteries["__by_id"][$ticket['lottery_id']];

            $ticket_data = [];

            $ttoken = "";
            $ticket_token = "-";
            if (!empty($ticket['token'])) {
                $ticket_token = $whitelabel['prefix'] . 'T' . $ticket['token'];
                $ttoken = $ticket['token'];
            }
            $ticket_data['ticket_token'] = Security::htmlentities($ticket_token);

            $ptoken = "";
            $transaction_token = "-";
            if (!empty($ticket['ptoken'])) {
                $transaction_token = $whitelabel['prefix'] . 'P' . $ticket['ptoken'];
                $ptoken = $ticket['ptoken'];
            }
            $ticket_data['transaction_token'] = Security::htmlentities($transaction_token);

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

            $user_login = "-";
            if (!empty($ticket['user_login'])) {
                $user_login = $ticket['user_login'];
            }
            $ticket_data['user_login'] = Security::htmlentities($user_login);

            $multidraw_token = '';
            if (!empty($ticket['mdtoken'])) {
                $multidraw_token = $whitelabel['prefix'] . 'M' . $ticket['mdtoken'];
            }
            $ticket_data['multidraw_token'] = $multidraw_token;
            $ticket_data['token'] = $ticket['token'];

            $user_deleted_url = '/deleted?filter[id]=' . $utoken;
            $user_active_url = '/users?filter[id]=' . $utoken;
            $user_inactive_url = '/inactive?filter[id]=' . $utoken;
            
            $user_fullname = _("Anonymous");
            if (!empty($ticket['name']) || !empty($ticket['surname'])) {
                $user_fullname = $ticket['name'] . ' ' . $ticket['surname'];
            }
            $ticket_data['user_fullname'] = Security::htmlentities($user_fullname);

            $ticket_data['user_email'] = Security::htmlentities($ticket['email']);

            $ticket_data['show_deleted'] = false;
            if ($ticket['is_deleted']) {
                $ticket_data['show_deleted'] = true;
                $ticket_data['show_user_url'] = $user_deleted_url;
            } elseif (($whitelabel['user_activation_type'] == Helpers_General::ACTIVATION_TYPE_REQUIRED &&
                        $ticket['is_active'] && $ticket['is_confirmed']) ||
                    ($whitelabel['user_activation_type'] != Helpers_General::ACTIVATION_TYPE_REQUIRED &&
                        $ticket['is_active'])
                ) {
                $ticket_data['show_user_url'] = $user_active_url;
            } else {
                $ticket_data['show_user_url'] = $user_inactive_url;
            }

            if (!empty($ticket['whitelabel_transaction_id'])) {
                $ticket_data['whitelabel_transaction_id'] = $ticket['whitelabel_transaction_id'];
            }

            $ticket_model = "";
            $ticket_model_extra = "";
            switch ($ticket['model']) {
                case Helpers_General::LOTTERY_MODEL_PURCHASE:
                    $ticket_model = _("Purchase");
                    break;
                case Helpers_General::LOTTERY_MODEL_MIXED:
                    $ticket_model = _("Mixed");
                    if ($ticket['is_insured']) {
                        $ticket_model_extra = ' (' . _("Insured") . ')';
                    } else {
                        $ticket_model_extra = ' (' . _("Purchased") . ')';
                    }
                    break;
                case Helpers_General::LOTTERY_MODEL_PURCHASE_SCAN:
                    $ticket_model = _("Purchase (Scan)");
                    break;
                case Helpers_General::LOTTERY_MODEL_NONE:
                    $ticket_model = _("None");
                    break;
            }
            $model_name = $ticket_model;
            $model_name .= $ticket_model_extra;
            $ticket_data['model_name'] = $model_name;

            $ticket_insured_text = "";
            if ($ticket['is_insured'] && !empty($ticket['tier'])) {
                $ticket_insured_text = '<br>' . _("Tier");
                $ticket_insured_text .= ': ' . $ticket['tier'];
            }
            $ticket_data['tier'] = $ticket_insured_text;

            $cost_manager = Lotto_View::format_currency(
                $ticket['cost_manager'],
                $ticket['manager_currency_code'],
                true
            );
            $ticket_data['cost_manager'] = $cost_manager;

            $costs_other_t = [];
            if ($ticket['manager_currency_code'] !== $ticket['user_currency_code']) {
                $cost_user = Lotto_View::format_currency(
                    $ticket['cost'],
                    $ticket['user_currency_code'],
                    true
                );
                $costs_other_t[] = _("User currency") .
                    ": " . $cost_user;
            }
            if ($ticket['manager_currency_code'] !== $ticket['lottery_currency_code']) {
                $cost_lottery = Lotto_View::format_currency(
                    $ticket['cost_local'],
                    $ticket['lottery_currency_code'],
                    true
                );
                $costs_other_t[] = _("Lottery currency") .
                    ": " . $cost_lottery;
            }
            $costs_other = implode("<br>", $costs_other_t);
            $ticket_data['costs_other'] = $costs_other;

            $income_manager = Lotto_View::format_currency(
                $ticket['income_manager'],
                $ticket['manager_currency_code'],
                true
            );
            $ticket_data['income_manager'] = $income_manager;

            $incomes_other_t = [];
            if ($ticket['manager_currency_code'] !== $ticket['user_currency_code']) {
                $income_user = Lotto_View::format_currency(
                    $ticket['income'],
                    $ticket['user_currency_code'],
                    true
                );
                $incomes_other_t[] = _("User currency") .
                    ": " . $income_user;
            }
            if ($ticket['manager_currency_code'] !== $ticket['lottery_currency_code']) {
                $income_lottery = Lotto_View::format_currency(
                    $ticket['income_local'],
                    $ticket['lottery_currency_code'],
                    true
                );
                $incomes_other_t[] = _("Lottery currency") .
                    ": " . $income_lottery;
            }
            $incomes_other = implode("<br>", $incomes_other_t);
            $ticket_data['incomes_other'] = $incomes_other;

            $margin_manager = Lotto_View::format_currency(
                $ticket['margin_manager'],
                $ticket['manager_currency_code'],
                true
            );
            $ticket_data['margin_manager'] = $margin_manager;

            $margins_other_t = [];
            if ($ticket['manager_currency_code'] !== $ticket['user_currency_code']) {
                $margin_user = Lotto_View::format_currency(
                    $ticket['margin'],
                    $ticket['user_currency_code'],
                    true
                );
                $margins_other_t[] = _("User currency") .
                    ": " . $margin_user;
            }
            if ($ticket['manager_currency_code'] !== $ticket['lottery_currency_code']) {
                $margin_lottery = Lotto_View::format_currency(
                    $ticket['margin_local'],
                    $ticket['lottery_currency_code'],
                    true
                );
                $margins_other_t[] = _("Lottery currency") .
                    ": " . $margin_lottery;
            }
            $margins_other = implode("<br>", $margins_other_t);
            $ticket_data['margins_other'] = $margins_other;
            
            if ($ticket['bonus_cost_manager'] != '0.00') {
                $bonus_cost_manager = Lotto_View::format_currency(
                    $ticket['bonus_cost_manager'],
                    $ticket['manager_currency_code'],
                    true
                );
                $ticket_data['bonus_cost_manager'] = $bonus_cost_manager;

                $bonus_cost_other_t = [];
                if ($ticket['manager_currency_code'] !== $ticket['user_currency_code']) {
                    $bonus_cost_user = Lotto_View::format_currency(
                        $ticket['bonus_cost'],
                        $ticket['user_currency_code'],
                        true
                    );
                    $bonus_cost_other_t[] = _("User currency") .
                        ": " . $bonus_cost_user;
                }
                if ($ticket['manager_currency_code'] !== $ticket['lottery_currency_code']) {
                    $bonus_cost_lottery = Lotto_View::format_currency(
                        $ticket['bonus_cost_local'],
                        $ticket['lottery_currency_code'],
                        true
                    );
                    $bonus_cost_other_t[] = _("Lottery currency") .
                        ": " . $bonus_cost_lottery;
                }
                $bonus_cost_other = implode("<br>", $bonus_cost_other_t);
                $ticket_data['bonus_cost_other'] = $bonus_cost_other;
            }
            
            $amount_manager_text = "";
            $bonus_amount_manager_text = "";
            if ($ticket['amount'] === '0.00' && $ticket['bonus_amount'] === '0.00') {
                $amount_manager_text = _("Free");
                $bonus_amount_manager_text = _("Free");
            } else {
                $amount_manager_text = Lotto_View::format_currency(
                    $ticket['amount_manager'],
                    $ticket['manager_currency_code'],
                    true
                );
            
                $bonus_amount_manager_text = Lotto_View::format_currency(
                    $ticket['bonus_amount_manager'],
                    $ticket['manager_currency_code'],
                    true
                );
            }
            $amounts_temp = [];
            $amounts_other = "";
            
            $lottery_amount = "";
            if ((float)$ticket['amount'] > 0.00) {
                $amount_manager_text = Lotto_View::format_currency(
                    $ticket['amount_manager'],
                    $ticket['manager_currency_code'],
                    true
                );
                
                if ($ticket['user_currency_code'] !== $ticket['manager_currency_code']) {
                    $amount_text = Lotto_View::format_currency(
                        $ticket['amount'],
                        $ticket['user_currency_code'],
                        true
                    );
                    $amounts_temp[] = _("User currency") . ": " . $amount_text;
                }
                
                if ($ticket['lottery_currency_code'] !== $ticket['manager_currency_code']) {
                    $lottery_amount = Lotto_View::format_currency(
                        $ticket['amount_local'],
                        $ticket['lottery_currency_code'],
                        true
                    );
                    $amounts_temp[] = _("Lottery currency") .
                        ": " . $lottery_amount;
                }
                
                $amounts_other = implode("<br>", $amounts_temp);
            }
            $ticket_data['amount_manager'] = $amount_manager_text;
            $ticket_data['amounts_other'] = $amounts_other;

            $bonus_amounts_temp = [];
            $bonus_amounts_other = "";
            
            $lottery_bonus_amount = "";
            if ((float)$ticket['bonus_amount'] > 0.00) {
                if ($ticket['user_currency_code'] !== $ticket['manager_currency_code']) {
                    $bonus_amount_text = Lotto_View::format_currency(
                        $ticket['bonus_amount'],
                        $ticket['user_currency_code'],
                        true
                    );
                    $bonus_amounts_temp[] = _("User currency") . ": " . $bonus_amount_text;
                }
                
                if ($ticket['lottery_currency_code'] !== $ticket['manager_currency_code']) {
                    $lottery_bonus_amount = Lotto_View::format_currency(
                        $ticket['bonus_amount_local'],
                        $ticket['lottery_currency_code'],
                        true
                    );
                    $bonus_amounts_temp[] = _("Lottery currency") .
                        ": " . $lottery_bonus_amount;
                }
                
                $bonus_amounts_other = implode("<br>", $bonus_amounts_temp);
            }
            $ticket_data['bonus_amount_manager'] = $bonus_amount_manager_text;
            $ticket_data['bonus_amounts_other'] = $bonus_amounts_other;

            $ticket_data['date'] = Lotto_View::format_date(
                $ticket['date'],
                IntlDateFormatter::MEDIUM,
                IntlDateFormatter::SHORT
            );

            if (!empty($ticket['draw_date'])) {
                $ticket_data['draw_date'] = Lotto_View::format_date(
                    $ticket['draw_date'],
                    IntlDateFormatter::MEDIUM,
                    IntlDateFormatter::LONG,
                    $lottery['timezone'],
                    false
                );
            } elseif (!empty($ticket['valid_to_draw'])) {
                $ticket_data['valid_to_draw'] = Lotto_View::format_date(
                    $ticket['valid_to_draw'],
                    IntlDateFormatter::SHORT,
                    IntlDateFormatter::LONG,
                    $lottery['timezone'],
                    false
                );
            }

            switch ($ticket['status']) {
                case Helpers_General::TICKET_STATUS_PENDING:
                    $ticket_data['status_class'] = '';
                    $ticket_data['status_text'] = _("Purchased");
                    break;
                case Helpers_General::TICKET_STATUS_WIN:
                    $ticket_data['status_class'] = 'text-success';
                    $ticket_data['status_text'] = _("Win");
                    break;
                case Helpers_General::TICKET_STATUS_NO_WINNINGS:
                    $ticket_data['status_class'] = 'text-danger';
                    $ticket_data['status_text'] = _("No winnings");
                    break;
                case Helpers_General::TICKET_STATUS_QUICK_PICK:
                    $ticket_data['status_class'] = '';
                    $ticket_data['status_text'] = _("Quick Pick");
                    $lottery_closed = Lotto_Helper::is_lottery_closed(
                        $lottery,
                        $ticket['valid_to_draw'],
                        $whitelabel
                    );
                    if ($lottery_closed) {
                        $ticket_data['status_extra_class'] = 'text-warning';
                        $ticket_data['status_extra_text'] = _("Expired");
                    }
                    break;
                case Helpers_General::TICKET_STATUS_CANCELED:
                    $ticket_data['status_class'] = 'text-danger';
                    $ticket_data['status_text'] = _("Cancelled");
                    break;
            }

            $ticket_data['status_win'] = false;
            if ($ticket['status'] == Helpers_General::TICKET_STATUS_WIN) {
                $ticket_data['status_win'] = true;

                $jackpot_text = "";
                if ($ticket['prize_jackpot']) {
                    $jackpot_text = _("Jackpot") . "<br>";
                }
                $ticket_data['jackpot_prize_text'] = $jackpot_text;

                $prize_manager = "";
                $prizes_other = "";
                if ($ticket['prize_net'] > 0) {
                    $prize_manager = Lotto_View::format_currency(
                        $ticket['prize_manager'],
                        $ticket['manager_currency_code'],
                        true
                    );

                    $prizes_other_t = [];
                    if ($ticket['manager_currency_code'] !== $ticket['user_currency_code']) {
                        $prize_user = Lotto_View::format_currency(
                            $ticket['prize'],
                            $ticket['user_currency_code'],
                            true
                        );
                        $prizes_other_t[] = _("User currency") .
                            ": " . $prize_user;
                    }
                    if ($ticket['manager_currency_code'] !== $ticket['lottery_currency_code']) {
                        $prize_lottery = Lotto_View::format_currency(
                            $ticket['prize_local'],
                            $ticket['lottery_currency_code'],
                            true
                        );
                        $prizes_other_t[] = _("Lottery currency") .
                            ": " . $prize_lottery;
                    }
                    $prizes_other = implode("<br>", $prizes_other_t);

                    if ($ticket['prize_local'] != $ticket['prize_net_local']) {
                        $prize_net_manager = Lotto_View::format_currency(
                            $ticket['prize_net_manager'],
                            $ticket['manager_currency_code'],
                            true
                        );
                        $ticket_data['prize_net_manager'] = Security::htmlentities($prize_net_manager);

                        if ($ticket['manager_currency_code'] !== $ticket['lottery_currency_code']) {
                            $prize_net_lottery_temp = Lotto_View::format_currency(
                                $ticket['prize_net_local'],
                                $ticket['lottery_currency_code'],
                                true
                            );
                            $prize_net_lottery = _("Lottery currency") .
                                ": " . $prize_net_lottery_temp;
                            $ticket_data['prize_net_local'] = Security::htmlentities($prize_net_lottery);
                        }
                    }
                }
                $ticket_data['prize_manager'] = Security::htmlentities($prize_manager);
                $ticket_data['prizes_other'] = Security::htmlentities($prizes_other);

                $prize_quickpic_text = "";
                if ($ticket['prize_quickpick'] > 0) {
                    if ($ticket['prize_net'] > 0 ||
                            $ticket['prize_jackpot'] > 0
                        ) {
                        $prize_quickpic_text .=  "<br>";
                    }

                    $prize_quickpic_text .= $ticket['prize_quickpick'];
                    $prize_quickpic_text .= "&times;";
                    $prize_quickpic_text .= _("Quick Pick");
                }
                $ticket_data['prize_quickpick'] = Security::htmlentities($prize_quickpic_text);
            }

            $ticket_payout_class = "";
            $ticket_payout = "";
            if ($ticket['status'] == Helpers_General::TICKET_STATUS_WIN) {
                if ($ticket['payout'] == Helpers_General::TICKET_PAYOUT_PAIDOUT) {
                    $ticket_payout_class = ' class="text-success"';
                    $ticket_payout = _("Yes");
                } else {
                    $ticket_payout_class = ' class="text-danger"';
                    $ticket_payout = _("No");
                }
            }
            $ticket_data['payout_class'] = $ticket_payout_class;
            $ticket_data['payout'] = $ticket_payout;

            $ticket_data['count'] = $ticket['count'];

            $ticket_data['transaction_url'] = '/transactions?filter[id]=' . $ptoken;
            $ticket_data['details_url'] = '/tickets/view/' . $ttoken . Lotto_View::query_vars();

            if (!empty($ticket_data['multidraw_token'])) {
                $ticket_data['multidraw_url'] = '/multidraw_tickets?filter[multidrawid]='.$ticket_data['multidraw_token'];
            }
            
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

        $count = Model_Whitelabel_User_Ticket::get_counted_by_whitelabel_filtered(
            $whitelabel,
            $params,
            $filter_add
        );
        
        if (is_null($count)) {
            return self::RESULT_NULL_COUNTED;
        }
        
        $config = [
            'pagination_url' => '/tickets' . '?' . http_build_query(Input::get()),
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

        $tickets = Model_Whitelabel_User_Ticket::get_full_data_paid_for_whitelabel(
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
