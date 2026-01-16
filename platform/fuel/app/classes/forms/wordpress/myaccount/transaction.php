<?php

use Helpers\UrlHelper;
use Repositories\SlotOpenGameRepository;
use Services\Logs\FileLoggerService;
use Services\PaymentMethodCustomizationService;
use Helpers\Wordpress\LanguageHelper;

/** @deprecated */
class Forms_Wordpress_Myaccount_Transaction extends Forms_Main
{
    private FileLoggerService $fileLoggerService;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var array
     */
    private $whitelabel = [];

    /**
     * @var array
     */
    private $user = null;

    /**
     *
     * @var Model_Whitelabel_Transaction|null
     */
    private $transaction = null;

    /**
     *
     * @var string
     */
    private $accountlink = "";

    /**
     *
     * @var string
     */
    private $transaction_link = "";

    /**
     *
     * @var array
     */
    private $currencies = [];

    /**
     *
     * @var array
     */
    private $whitelabel_payment_methods_without_currency = [];

    private SlotOpenGameRepository $slotOpenGameRepository;

    private PaymentMethodCustomizationService $paymentMethodCustomizationService;

    public function __construct(array $whitelabel, array $user, string $accountlink)
    {
        $this->whitelabel = $whitelabel;
        $this->user = $user;
        $this->accountlink = $accountlink;

        $this->transaction_link = $accountlink . 'transactions/';

        $this->currencies = Lotto_Settings::getInstance()->get("currencies");

        $this->whitelabel_payment_methods_without_currency = Model_Whitelabel_Payment_Method::get_payment_methods_for_whitelabel($this->whitelabel);
        $this->slotOpenGameRepository = Container::get(SlotOpenGameRepository::class);
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        $this->paymentMethodCustomizationService = Container::get(PaymentMethodCustomizationService::class);
    }

    public function get_errors(): ?array
    {
        return $this->errors;
    }

    private function prepare_transaction_data_to_show(): array
    {
        $to_show = [
            'status_label' => Security::htmlentities(_("Status")),
            'date_label' => Security::htmlentities(_("Date")),
            'confirm_date_label' => Security::htmlentities(_("Confirmation date")),
            'payment_label' => Security::htmlentities(_("Payment method")),
            'amount_label' => Security::htmlentities(_("Amount")),
            'amount_payment_label' => Security::htmlentities(_("Payment amount")),
        ];

        $user_timezone = get_user_timezone();

        $to_show['status_value'] = $this->get_prepared_transaction_status_text($this->transaction);

        $to_show['date_value'] = $this->get_prepared_transaction_date_time(
            $this->transaction,
            $user_timezone,
            false
        );

        $to_show['confirm_date_value'] = $this->get_prepared_transaction_date_confirmed(
            $this->transaction,
            $user_timezone,
            false
        );

        $to_show['payment_method_type_value'] = $this->get_prepared_payment_method_type($this->transaction);

        $to_show['amount'] = $amount = $this->get_prepared_transaction_amount($this->transaction);

        $to_show['amount_payment'] = $this->get_prepared_transaction_amount_payment(
            $this->transaction,
            false
        );

        $to_show['bonus_amount'] = $bonus_amount = $this->get_prepared_transaction_amount($this->transaction, true);

        $to_show['bonus_amount_payment'] = $this->get_prepared_transaction_amount_payment(
            $this->transaction,
            false,
            true
        );

        return $to_show;
    }

    private function prepare_deposit_data_to_show(): array
    {
        $deposit_to_show['deposit_order_header'] = Security::htmlentities(_("Deposit"));

        $deposit_amount = "";
        if (
            !empty($this->transaction['currency_id']) &&
            !empty($this->currencies[$this->transaction['currency_id']]) &&
            !empty($this->currencies[$this->transaction['currency_id']]['code'])
        ) {
            $deposit_amount = Lotto_View::format_currency(
                $this->transaction['amount'],
                $this->currencies[$this->transaction['currency_id']]['code'],
                true
            );
        }
        $deposit_to_show['deposit_amount'] = Security::htmlentities($deposit_amount);

        return $deposit_to_show;
    }

    private function prepare_tickets_data_to_show(
        array $tickets,
        array $lotteries
    ): array {
        $total_sum = 0;
        $num = 0;
        $tickets_to_show = [];

        $user_currency_code = lotto_platform_user_currency();
        $saved_multidraws = [];

        foreach ($tickets as $ticket) {
            if (!empty($ticket['multi_draw_id']) && in_array($ticket['multi_draw_id'], $saved_multidraws)) {
                continue;
            }

            $is_multi_draw = false;
            if (!empty($ticket['multi_draw_id'])) {
                array_push($saved_multidraws, $ticket['multi_draw_id']);
                $is_multi_draw = true;
            }

            if (!isset($lotteries["__by_id"][$ticket['lottery_id']])) {
                $error_message = "There is a problem with lottery settings. " .
                    "No lottery within lotteries list. Lottery ID: " .
                    $ticket['lottery_id'] . " " .
                    "Ticket ID: " .
                    $ticket['id'] . " " .
                    "Whitelabel ID: " . $this->whitelabel['id'];

                $this->fileLoggerService->error(
                    $error_message
                );

                continue;
            }

            $lottery = $lotteries['__by_id'][$ticket['lottery_id']];

            if ((int)$this->transaction['payment_method_type'] === Helpers_General::PAYMENT_TYPE_BONUS_BALANCE) {
                if ($is_multi_draw) {
                    $unit_price = $ticket['multi_draw_bonus_amount'];
                } else {
                    $unit_price = $ticket['bonus_amount'];
                }
            } else {
                if ($is_multi_draw) {
                    $unit_price = $ticket['multi_draw_amount'];
                } else {
                    $unit_price = $ticket['amount'];
                }
            }

            $unit_price_with_curr = Lotto_View::format_currency(
                $unit_price,
                $user_currency_code,
                true
            );
            $field = $ticket['amount'];
            if ((int)$this->transaction['payment_method_type'] === Helpers_General::PAYMENT_TYPE_BONUS_BALANCE) {
                $field = $ticket['bonus_amount'];
            }
            $item_price = round($field / $ticket['count'], 2);
            $item_price_with_curr = Lotto_View::format_currency(
                $item_price,
                $user_currency_code,
                true
            );
            $total_sum = $total_sum + $unit_price;

            $lottery_image = Lotto_View::get_lottery_image($lottery['id']);
            $ticket_to_show = [
                'lottery_image' => UrlHelper::esc_url($lottery_image),
                'lottery_image_alt' => Security::htmlentities(_($lottery['name']))
            ];

            $ticket_text = sprintf(
                _('<a href="%s">%s</a> ticket'),
                lotto_platform_get_permalink_by_slug('play/' . $lottery['slug']),
                _($lottery['name'])
            );

            $allowed_html = ["a" => ["href" => []]];
            $ticket_to_show['ticket_text'] = wp_kses($ticket_text, $allowed_html);

            $ticket_to_show['unit_price'] = Security::htmlentities($unit_price_with_curr);

            $details_url = Helper_Route::get_by_slug('account', 'tickets/details/' . $ticket['token']);
            $ticket_to_show['ticket_details_url'] = UrlHelper::esc_url($details_url);

            $ticket_to_show['ticket_tooltip_text'] = Security::htmlentities(_("Details"));

            if ($is_multi_draw) {
                $item_price = round($ticket['multi_draw_old_ticket_price'] / $ticket['count'], 2);
                $item_price_with_curr = Lotto_View::format_currency(
                    $item_price,
                    $user_currency_code,
                    true
                );

                $ticket_lines_text = sprintf(
                    _("Lines: <span>%s</span> &times; %s"),
                    $ticket['multi_draw_tickets'] . " &times; " . $ticket['count'],
                    $item_price_with_curr
                );

                $multi_draw_helper = new Helpers_Multidraw([]);
                $ticket_lines_text = sprintf(
                    _("Lines: <span>%s</span> &times; %s &times; %s Draws"),
                    $ticket['count'],
                    $item_price_with_curr,
                    $ticket['multi_draw_tickets']
                );
                $old_price = $multi_draw_helper->calculate_old_price($ticket['multi_draw_tickets'], $ticket['multi_draw_old_ticket_price'], 1);
                $old_price_with_currency = Lotto_View::format_currency(
                    $old_price,
                    $user_currency_code,
                    true
                );
                $ticket_lines_text .= " (" . $old_price_with_currency . ")";

                $ticket_lines_text  .= "<br/>" . sprintf(
                    _("<span>%s</span> multi-draw discount"),
                    $ticket['multi_draw_discount'] . '%'
                );

                $field = $ticket['multi_draw_amount'];
                if ((int)$this->transaction['payment_method_type'] === Helpers_General::PAYMENT_TYPE_BONUS_BALANCE) {
                    $field = $ticket['multi_draw_bonus_amount'];
                }
                $discount_with_currency = Lotto_View::format_currency(
                    $field,
                    $user_currency_code,
                    true
                );
                $ticket_lines_text  .= " (" . $discount_with_currency . ")";
                $allowed_html = ["span" => [], "strong" => [], "br" => []];
                $ticket_to_show['ticket_lines_text'] = wp_kses($ticket_lines_text, $allowed_html);
            } else {
                $ticket_lines_text = sprintf(
                    _("Lines: <span>%s</span> &times; %s"),
                    $ticket['count'],
                    $item_price_with_curr
                );

                $allowed_html = ["span" => [], "strong" => []];
                $ticket_to_show['ticket_lines_text'] = wp_kses($ticket_lines_text, $allowed_html);
            }

            $tickets_to_show[] = $ticket_to_show;

            $num++;
        }

        $total_sum_to_show = [
            'text' => Security::htmlentities(_("Total Sum"))
        ];
        $total_sum_with_curr = "";
        if (
            !empty($this->transaction['currency_id']) &&
            !empty($this->currencies[$this->transaction['currency_id']]) &&
            !empty($this->currencies[$this->transaction['currency_id']]['code'])
        ) {
            $total_sum_with_curr = Lotto_View::format_currency(
                $total_sum,
                $this->currencies[$this->transaction['currency_id']]['code'],
                true
            );
        }
        $total_sum_to_show['value'] = Security::htmlentities($total_sum_with_curr);

        return [
            $tickets_to_show,
            $total_sum_to_show
        ];
    }

    private function get_prepared_full_token(array $transaction): string
    {
        $transaction_full_id = $this->whitelabel['prefix'];
        if ($transaction['type'] == Helpers_General::TYPE_TRANSACTION_PURCHASE) {
            $transaction_full_id .= 'P';
        } else {
            $transaction_full_id .= 'D';
        }
        $transaction_full_id .= $transaction['token'];
        $transaction_full_id_text = Security::htmlentities($transaction_full_id);

        return $transaction_full_id_text;
    }

    private function get_prepared_transaction_amount(array $transaction, bool $is_bonus = false): string
    {
        $field = 'amount';
        if ($is_bonus) {
            $field = 'bonus_amount';
        }

        $transaction_amount_temporary = "";

        if (
            !empty($transaction['currency_id']) &&
            !empty($this->currencies[$transaction['currency_id']]) &&
            !empty($this->currencies[$transaction['currency_id']]['code'])
        ) {
            $currency_code = $this->currencies[$transaction['currency_id']]['code'];

            $transaction_amount_temporary = Lotto_View::format_currency(
                $transaction[$field],
                $currency_code,
                true
            );
        }

        $transaction_amount = Security::htmlentities($transaction_amount_temporary);

        return $transaction_amount;
    }

    private function get_prepared_transaction_amount_payment(
        array $transaction,
        bool $prepare_for_list = true,
        bool $is_bonus = false
    ): string {
        $field = 'amount_payment';
        if ($is_bonus) {
            $field = 'bonus_amount_payment';
        }
        $transaction_amount_payment_temporary = "";

        if (
            !empty($transaction['payment_currency_id']) &&
            !empty($this->currencies[$transaction['payment_currency_id']]) &&
            !empty($this->currencies[$transaction['payment_currency_id']]['code']) &&
            isset($transaction[$field]) &&
            (int)$transaction['currency_id'] !== (int)$transaction['payment_currency_id']
        ) {
            $payment_currency_code = $this->currencies[$transaction['payment_currency_id']]['code'];

            $transaction_amount_payment_formatted = Lotto_View::format_currency(
                $transaction[$field],
                $payment_currency_code,
                true
            );

            if ($prepare_for_list) {
                $transaction_amount_payment_temporary = " (" . $transaction_amount_payment_formatted . ")";
            } else {
                $transaction_amount_payment_temporary = $transaction_amount_payment_formatted;
            }
        }

        $transaction_amount_payment = Security::htmlentities($transaction_amount_payment_temporary);

        return $transaction_amount_payment;
    }

    private function get_prepared_transaction_date_time(
        array $transaction,
        string $user_timezone,
        bool $prepare_for_list = true
    ): string {
        $format_date_time = IntlDateFormatter::SHORT;
        if (!$prepare_for_list) {
            $format_date_time = IntlDateFormatter::LONG;
        }

        $transaction_date_time = Lotto_View::format_date(
            $transaction['date'],
            $format_date_time,
            $format_date_time,
            $user_timezone
        );
        $transaction_date = Security::htmlentities($transaction_date_time);

        return $transaction_date;
    }

    private function get_prepared_transaction_date_confirmed(
        array $transaction,
        string $user_timezone,
        bool $prepare_for_list = true
    ): string {
        $format_date_time = IntlDateFormatter::SHORT;
        if (!$prepare_for_list) {
            $format_date_time = IntlDateFormatter::LONG;
        }

        $transaction_date_confirmed_temporary = "";
        if (!empty($transaction['date_confirmed'])) {
            $transaction_date_confirmed_temporary = Lotto_View::format_date(
                $transaction['date_confirmed'],
                $format_date_time,
                $format_date_time,
                $user_timezone
            );
        }
        $transaction_date_confirmed = Security::htmlentities($transaction_date_confirmed_temporary);

        return $transaction_date_confirmed;
    }

    private function get_prepared_payment_method_type(array $transaction): string
    {
        $payment_method_type_temporary = "";
        switch ($transaction['payment_method_type']) {
            case Helpers_General::PAYMENT_TYPE_BONUS_BALANCE:
                $payment_method_type_temporary = _("Bonus balance");
                break;
            case Helpers_General::PAYMENT_TYPE_BALANCE:
                $payment_method_type_temporary = _("Balance");
                break;
            case Helpers_General::PAYMENT_TYPE_CC:
                $payment_method_type_temporary = _("Credit Card");
                break;
            case Helpers_General::PAYMENT_TYPE_OTHER:
                $whitelabel_payment_method_id = 0;
                if (!empty($transaction['whitelabel_payment_method_id'])) {
                    $whitelabel_payment_method_id = (int) $transaction['whitelabel_payment_method_id'];
                }

                $whitelabel_language = LanguageHelper::getCurrentWhitelabelLanguage();
                if (!empty($whitelabel_language)) {
                    $language_id = (int)$whitelabel_language['id'];

                    $paymentMethodCustomize = $this->paymentMethodCustomizationService->getWhitelabelPaymentMethodCustomizeData(
                        $whitelabel_payment_method_id,
                        $language_id
                    );

                    if (!empty($paymentMethodCustomize)) {
                        $payment_method_type_temporary = $paymentMethodCustomize['title'];
                    }
                }

                if (
                    empty($payment_method_type_temporary) &&
                    !empty($whitelabel_payment_method_id) &&
                    !empty($this->whitelabel_payment_methods_without_currency[$whitelabel_payment_method_id]) &&
                    !empty($this->whitelabel_payment_methods_without_currency[$whitelabel_payment_method_id]['name'])
                ) {
                    $payment_method_type_temporary = $this->whitelabel_payment_methods_without_currency[$whitelabel_payment_method_id]['name'];
                }
                break;
        }
        $payment_method_type = Security::htmlentities($payment_method_type_temporary);

        return $payment_method_type;
    }

    private function get_prepared_transaction_status_text(array $transaction): string
    {
        $transaction_status_temporary = "";
        switch ($transaction['status']) {
            case Helpers_General::STATUS_TRANSACTION_PENDING:
                $transaction_status_temporary = _("pending");
                break;
            case Helpers_General::STATUS_TRANSACTION_APPROVED:
                $transaction_status_temporary = _("approved");
                break;
            case Helpers_General::STATUS_TRANSACTION_ERROR:
                $transaction_status_temporary = _("failure");
                break;
        }
        $transaction_status = Security::htmlentities($transaction_status_temporary);

        return $transaction_status;
    }

    private function prepareTransactionsDataToShow(array $transactions = null): array
    {
        $transactions_list = [];

        if (empty($transactions)) {
            return [];
        }

        $user_timezone = get_user_timezone();

        foreach ($transactions as $transaction) {
            $tr_class = '';
            if ($transaction['status'] == Helpers_General::STATUS_TRANSACTION_ERROR) {
                $tr_class = ' class="transaction-shade"';
            }
            $transaction_list['tr_class'] = $tr_class;

            $transaction_list['full_id_text'] = $this->get_prepared_full_token($transaction);

            $amount = $this->get_prepared_transaction_amount($transaction);
            $amount .= $this->get_prepared_transaction_amount_payment($transaction);
            $transaction_list['amount'] = $amount;
            $transaction_list['type'] = $transaction['type'];

            $bonus_amount = $this->get_prepared_transaction_amount($transaction, true);
            $bonus_amount .= $this->get_prepared_transaction_amount_payment($transaction, true);
            $transaction_list['bonus_amount'] = $bonus_amount;

            $transaction_list['date'] = $this->get_prepared_transaction_date_time(
                $transaction,
                $user_timezone
            );

            $transaction_list['date_confirmed'] = $this->get_prepared_transaction_date_confirmed(
                $transaction,
                $user_timezone
            );

            $transaction_list['payment_method_type_num'] = $transaction['payment_method_type'];
            $transaction_list['payment_method_type'] = $this->get_prepared_payment_method_type($transaction);

            $transaction_list['status'] = htmlspecialchars($transaction['status']);

            $transaction_list['status_text'] = $this->get_prepared_transaction_status_text($transaction);

            $trans_details_url = $this->transaction_link .
                'details/' .
                $transaction['token'] .
                '/' .
                Lotto_View::query_vars();
            $transaction_details_url = UrlHelper::esc_url($trans_details_url);
            $transaction_list['details_url'] = $transaction_details_url;

            $transactions_list[] = $transaction_list;
        }

        return $transactions_list;
    }

    private function prepareSortOptions(array $sort): array
    {
        $sort_options = [
            'amount_link_a' => [
                'value' => UrlHelper::esc_url($sort['amount']['link_a']),
                'select' => '',
                'text' => Security::htmlentities(_("Amount")) .
                    ' - ' .
                    Security::htmlentities(_("by lowest"))
            ],
            'amount_link_d' => [
                'value' => UrlHelper::esc_url($sort['amount']['link_d']),
                'select' => '',
                'text' => Security::htmlentities(_("Amount")) .
                    ' - ' .
                    Security::htmlentities(_("by highest"))
            ],
            'date_link_a' => [
                'value' => UrlHelper::esc_url($sort['date']['link_a']),
                'select' => '',
                'text' => Security::htmlentities(_("Date")) .
                    ' - ' .
                    Security::htmlentities(_("by oldest"))
            ],
            'date_link_d' => [
                'value' => UrlHelper::esc_url($sort['date']['link_d']),
                'select' => '',
                'text' => Security::htmlentities(_("Date")) .
                    ' - ' .
                    Security::htmlentities(_("by newest"))
            ]
        ];

        if (
            (string)Input::get("sort") === "amount" &&
            (string)Input::get("sort_order") === "asc"
        ) {
            $sort_options['amount_link_a']['select'] = ' selected="selected"';
        }
        if (
            (string)Input::get("sort") === "amount" &&
            (string)Input::get("sort_order") === "desc"
        ) {
            $sort_options['amount_link_d']['select'] = ' selected="selected"';
        }
        if (
            (string)Input::get("sort") === "date" &&
            (string)Input::get("sort_order") === "asc"
        ) {
            $sort_options['date_link_a']['select'] = ' selected="selected"';
        }
        if (
            (string)Input::get("sort") === "date" &&
            (string)Input::get("sort_order") === "desc"
        ) {
            $sort_options['date_link_d']['select'] = ' selected="selected"';
        }

        return $sort_options;
    }

    private function check_transaction(): int
    {
        if (empty(get_query_var('id'))) {
            $this->errors = ["details" => _("Incorrect transaction.")];
            return self::RESULT_WITH_ERRORS;
        }

        $transaction = Model_Whitelabel_Transaction::get_single_for_whitelabel_by_token(
            $this->whitelabel,
            get_query_var('id')
        );

        if (is_null($transaction)) {
            $this->errors = ["details" => _("Incorrect transaction.")];
            return self::RESULT_WITH_ERRORS;
        }

        if (
            (int)$transaction['whitelabel_id'] !== (int)$this->whitelabel['id'] ||
            (int)$transaction['whitelabel_user_id'] !== (int)$this->user['id']
        ) {
            $this->errors = ["details" => _("Incorrect transaction.")];
            return self::RESULT_WITH_ERRORS;
        }

        $this->transaction = $transaction;

        return self::RESULT_GO_FURTHER;
    }

    /**
     * @param View $view Reference to view
     */
    public function process_form(&$view): int
    {
        $view->set("transaction_link", $this->transaction_link);
        $view->set("whitelabel", $this->whitelabel);
        $view->set('currencies', $this->currencies);
        $view->set("methods", $this->whitelabel_payment_methods_without_currency);

        $action = (string)get_query_var("action");
        $isDetailsType = $action === "details";

        if ($isDetailsType) {
            $view->set("action", "details");

            $result = $this->check_transaction();
            if ($result === self::RESULT_WITH_ERRORS) {
                return $result;
            }

            $to_show = $this->prepare_transaction_data_to_show();
            $tickets = Model_Whitelabel_User_Ticket::get_full_data_with_counted_lines($this->transaction['id']);

            $lotteries = Model_Lottery::get_really_all_lotteries_for_whitelabel($this->whitelabel);
            if ((int)$this->transaction['type'] === Helpers_General::TYPE_TRANSACTION_DEPOSIT) {
                $deposit_to_show = $this->prepare_deposit_data_to_show();
                $view->set('deposit_to_show', $deposit_to_show);
            } elseif ((int)$this->transaction['type'] === Helpers_General::TYPE_TRANSACTION_PURCHASE) {
                list(
                    $tickets_to_show,
                    $total_sum_to_show
                ) = $this->prepare_tickets_data_to_show(
                    $tickets,
                    $lotteries
                );
                $view->set('tickets_to_show', $tickets_to_show);
                $view->set('total_sum_to_show', $total_sum_to_show);
            }

            $view->set("transaction", $this->transaction);
            $view->set("to_show", $to_show);
            $view->set('tickets', $tickets);
            $view->set('lotteries', $lotteries);

            return self::RESULT_OK;
        }

        $status = null;
        $isStatusValid = !empty(Input::get("filter.status")) && Input::get("filter.status") !== "a";
        if ($isStatusValid) {
            $status = intval(Input::get("filter.status"));
        }

        $sort = Lotto_Helper::get_sort(
            ['date' => 'desc', 'amount' => 'asc'],
            ['date', 'desc'],
            $this->transaction_link
        );

        if (IS_CASINO) {
            $transactionsCount = $this->slotOpenGameRepository->countOpenGamesByUserIdWithAnyTransaction($this->user['id']);
            $paginationConfig = [
                'pagination_url' => $this->transaction_link . '?' . http_build_query(Input::get()),
                'total_items' => $transactionsCount,
                'per_page' => 25,
                'uri_segment' => 'show_page'
            ];
            $pagination = Pagination::forge('slot_transactions_pagination', $paginationConfig);

            $orderBy = [];

            foreach ($sort as $columnName => $singleSort) {
                $isCorrectSortRule = $columnName !== 'db' && in_array($singleSort['order'], ['asc', 'desc']);
                if ($isCorrectSortRule) {
                    $orderBy[$columnName] = $singleSort['order'];
                }
            }

            $summedUpTransactions = $this->slotOpenGameRepository->findSummedUpTransactionsWithSortAndFiltersByWhitelabelUserId(
                $this->user['id'],
                $pagination->offset,
                $pagination->per_page,
                $orderBy
            );

            $userCurrencyId = $this->user['currency_id'];
            $transactions = $this->prepareSlotTransactionsData($summedUpTransactions, $userCurrencyId);
            $deposits = Model_Whitelabel_Transaction::get_filtered_data_for_user_and_whitelabel(
                $this->whitelabel,
                $this->user,
                $status,
                $sort,
                $pagination->offset,
                $pagination->per_page,
                true
            );
            $deposits = $this->prepareTransactionsDataToShow($deposits);
            $view->set("deposits", $deposits);
        } else {
            $transactionsCount = Model_Whitelabel_Transaction::get_count_filtered_for_user_and_whitelabel(
                $this->whitelabel,
                $this->user,
                $status
            ) ?? 0;
            $paginationConfig = [
                'pagination_url' => $this->transaction_link . '?' . http_build_query(Input::get()),
                'total_items' => $transactionsCount,
                'per_page' => 25,
                'uri_segment' => 'show_page'
            ];
            $pagination = Pagination::forge('lottery_transactions_pagination', $paginationConfig);

            $transactions = Model_Whitelabel_Transaction::get_filtered_data_for_user_and_whitelabel(
                $this->whitelabel,
                $this->user,
                $status,
                $sort,
                $pagination->offset,
                $pagination->per_page
            );

            $transactions = $this->prepareTransactionsDataToShow($transactions);
        }
        $sortOptions = $this->prepareSortOptions($sort);

        $view->set("transactions", $transactions);
        $view->set("pages", $pagination);
        $view->set("sort", $sort);
        $view->set("sortOptions", $sortOptions);
        $view->set("transactionsCount", $transactionsCount);

        return self::RESULT_OK;
    }

    private function prepareSlotTransactionsData(array $summedUpTransactions, int $userCurrencyId): array
    {
        $preparedSlotTransactions = [];

        foreach ($summedUpTransactions as $transaction) {
            $preparedSlotTransaction = [];

            $userCurrencyCode = $this->currencies[$userCurrencyId]['code'];
            $gameCurrencyCode = $transaction['currency_code'];
            $amount = Helpers_Currency::convert_to_any(
                $transaction['amount'],
                $gameCurrencyCode,
                $userCurrencyCode
            );
            $formattedAmount = Lotto_View::format_currency(
                $amount,
                $userCurrencyCode,
                true
            );

            $preparedSlotTransaction['isLost'] = $amount < 0;
            $preparedSlotTransaction['amount'] = $formattedAmount;
            $preparedSlotTransaction['date'] = $transaction['date'];

            $gameName = ucfirst($transaction['game_name']);
            $providerName = ucfirst($transaction['game_provider_name']);
            $preparedSlotTransaction['game_name'] = "$gameName ($providerName)";
            $preparedSlotTransaction['session_id'] = $transaction['session_id'];

            $preparedSlotTransactions[] = $preparedSlotTransaction;
        }

        return $preparedSlotTransactions;
    }
}
