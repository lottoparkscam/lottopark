<?php

use Carbon\Carbon;
use Fuel\Core\Database_Result;
use Fuel\Core\DB;
use Wrappers\Event;
use Helpers\ArrayHelper;
use Models\{
    PaymentMethod,
    WhitelabelUser,
    WhitelabelTransaction
};
use Services\{
    LotteryPurchaseLimitService,
    PaymentRequestLockService,
    Plugin\MauticPluginService,
    Logs\FileLoggerService
};

/**
 * Description of Forms_Transactions_Accept
 */
final class Forms_Transactions_Accept extends Forms_Main
{
    const EMAIL_TICKET = 1;
    const EMAIL_DEPOSIT = 2;

    /**
     *
     * @var null|Database_Connection
     */
    private $connection = null;

    /**
     *
     * @var array
     */
    private $whitelabel = [];

    /**
     *
     * @var null|Model_Whitelabel_Transaction
     */
    private $transaction = null;

    /**
     *
     * @var null|string
     */
    private $out_id = null;

    /**
     *
     * @var null|array
     */
    private $additionalData = null;

    /**
     *
     * @var type
     */
    private $cost_percent = "0";

    /**
     *
     * @var type
     */
    private $cost_fixed = "0";

    /**
     *
     * @var null|int
     */
    private $cost_currency_id = null;

    /**
     *
     * @var bool
     */
    private $is_balance = false;

    /**
     *
     * @var bool
     */
    private $is_bonus_balance = false;

    /**
     *
     * @var string
     */
    private $payment_method_name = "";

    /**
     *
     * @var null|Model_Whitelabel_CC_Method|Model_Whitelabel_Payment_Method
     */
    private $payment_methods = null;

    /**
     *
     * @var null|array
     */
    private $user_currency_tab = null;

    /**
     *
     * @var null|array
     */
    private $manager_currency_tab = null;

    /**
     *
     * @var null|array
     */
    private $system_currency_tab = null;

    /**
     *
     * @var null|array
     */
    private $payment_cost_currency_tab = null;

    /**
     *
     * @var string
     */
    private $calc_percent = "0";

    /**
     *
     * @var string
     */
    private $calc_percent_usd = "0";

    /**
     *
     * @var string
     */
    private $calc_fixed = "0";

    /**
     *
     * @var string
     */
    private $calc_fixed_usd = "0";

    /**
     *
     * @var string
     */
    private $total_calc_cost_user = "0";

    /**
     *
     * @var string
     */
    private $total_calc_cost_usd = "0";

    /**
     *
     * @var string
     */
    private $total_calc_cost = "0";

    /**
     *
     * @var string
     */
    private $total_calc_cost_manager = "0";

    /**
     *
     * @var Model_Whitelabel_User
     */
    private $whitelabel_user_model = null;

    /**
     *
     * @var null|array
     */
    private $globals = null;

    /**
     *
     * @var null|array
     */
    private $whitelabel_language = null;

    /**
     *
     * @var null|array
     */
    private $tickets = null;

    /**
     *
     * @var null|string
     */
    private $bonus_ticket_lottery_name = null;
    private PaymentRequestLockService $paymentRequestLockService;
    private Event $event;
    private FileLoggerService $fileLoggerService;
    private ?LotteryPurchaseLimitService $lotteryPurchaseLimitService = null;

    private string $lastPurchasedLotteryName = '';

    /**
     *
     * @param null|array $whitelabel
     * @param null|Model_Whitelabel_Transaction $transaction
     * @param null|string $out_id
     * @param null|array $additionalData
     */
    public function __construct(
        array $whitelabel = null,
        Model_Whitelabel_Transaction $transaction = null,
        string $out_id = null,
        array $additionalData = null
    ) {
        $this->whitelabel = $whitelabel;
        if (!empty($transaction)) {
            $this->transaction = $transaction;
        }
        $this->out_id = $out_id;
        $this->additionalData = $additionalData;
        $this->paymentRequestLockService = Container::get(PaymentRequestLockService::class);
        $this->event = Container::get(Event::class);
        $this->fileLoggerService = Container::get(FileLoggerService::class);
    }

    /**
     *
     * @return array
     */
    public function get_whitelabel(): ?array
    {
        return $this->whitelabel;
    }

    public function setLotteryPurchaseLimitService(LotteryPurchaseLimitService $lotteryPurchaseLimitService): void
    {
        $this->lotteryPurchaseLimitService = $lotteryPurchaseLimitService;
    }

    public function saveTransaction(): void
    {
        $transactionSet = [];

        if (empty($this->transaction->transaction_out_id)) {
            $transactionSet['transaction_out_id'] = $this->out_id;
        }

        if (empty($this->transaction->additional_data)) {
            $transactionSet['additional_data'] = serialize($this->additionalData);
        }

        if (!empty($transactionSet)) {
            $this->transaction->set($transactionSet);
            $this->transaction->save();
        }
    }

    /**
     *
     * @return array|null
     */
    public function get_user_currency_tab(): ?array
    {
        if (empty($this->user_currency_tab)) {
            $this->user_currency_tab = Helpers_Currency::get_mtab_currency(
                false,
                null,
                $this->transaction->currency_id
            );
        }

        return $this->user_currency_tab;
    }

    /**
     *
     * @return array|null
     */
    public function get_manager_currency_tab(): ?array
    {
        if (empty($this->manager_currency_tab)) {
            $this->manager_currency_tab = Helpers_Currency::get_mtab_currency(
                false,
                null,
                $this->whitelabel['manager_site_currency_id']
            );
        }

        return $this->manager_currency_tab;
    }

    /**
     *
     * @return array|null
     */
    public function get_system_currency_tab(): ?array
    {
        if (empty($this->system_currency_tab)) {
            $this->system_currency_tab = Helpers_Currency::get_mtab_currency(
                false,
                "USD"
            );
        }

        return $this->system_currency_tab;
    }

    /**
     *
     * @return array|null
     */
    public function get_payment_cost_currency_tab(): ?array
    {
        if (
            empty($this->payment_cost_currency_tab) &&
            !empty($this->cost_currency_id)
        ) {
            $this->payment_cost_currency_tab = Helpers_Currency::get_mtab_currency(
                false,
                null,
                $this->cost_currency_id
            );
        }

        return $this->payment_cost_currency_tab;
    }

    /**
     *
     * @return void
     */
    public function prepare_payment_method_costs_data(): void
    {
        switch ($this->transaction->payment_method_type) {
            case Helpers_General::PAYMENT_TYPE_BONUS_BALANCE: // bonus balance
                $this->is_bonus_balance = true;
                $this->payment_method_name = _("Bonus balance");
                break;
            case Helpers_General::PAYMENT_TYPE_BALANCE: // balance
                $this->is_balance = true;
                $this->payment_method_name = _("Balance");
                break;
            case Helpers_General::PAYMENT_TYPE_CC: // CC
                $whitelabel_cc_methods = Model_Whitelabel_CC_Method::get_cc_methods_for_whitelabel($this->whitelabel);
                $whitelabel_cc_method_id = $this->transaction->whitelabel_cc_method_id;
                $this->cost_percent = $whitelabel_cc_methods[$whitelabel_cc_method_id]['cost_percent'];
                $this->cost_fixed = $whitelabel_cc_methods[$whitelabel_cc_method_id]['cost_fixed'];
                $this->cost_currency_id = $whitelabel_cc_methods[$whitelabel_cc_method_id]['cost_currency_id'];
                $this->payment_method_name = _("Credit Card");

                $this->payment_methods = $whitelabel_cc_methods;
                break;
            case Helpers_General::PAYMENT_TYPE_OTHER: // other
                $whitelabel_payment_methods_without_currency = Model_Whitelabel_Payment_Method::get_payment_methods_for_whitelabel($this->whitelabel);
                $whitelabel_payment_methods_with_currency = Helpers_Currency::get_whitelabel_payment_methods_with_currency(
                    $this->whitelabel,
                    $whitelabel_payment_methods_without_currency,
                    $this->user_currency_tab
                );

                $whitelabel_payment_method_id = $this->transaction->whitelabel_payment_method_id;

                $this->cost_percent = $whitelabel_payment_methods_with_currency[$whitelabel_payment_method_id]['cost_percent'];
                $this->cost_fixed = $whitelabel_payment_methods_with_currency[$whitelabel_payment_method_id]['cost_fixed'];
                $this->cost_currency_id = $whitelabel_payment_methods_with_currency[$whitelabel_payment_method_id]['cost_currency_id'];
                $this->payment_method_name = $whitelabel_payment_methods_with_currency[$whitelabel_payment_method_id]['pname'];

                $this->payment_methods = $whitelabel_payment_methods_with_currency;
                break;
        }
    }

    /**
     *
     * @return void
     */
    public function calculation_transaction_save(): void
    {
        $payment_cost_to_save = round($this->total_calc_cost, 2);
        $payment_cost_usd_to_save = round($this->total_calc_cost_usd, 2);
        $payment_cost_manager_to_save = round($this->total_calc_cost_manager, 2);

        $set = [
            'status' => Helpers_General::STATUS_TRANSACTION_APPROVED,
            'date_confirmed' => DB::expr("NOW()"),
            'payment_cost' => $payment_cost_to_save,
            'payment_cost_usd' => $payment_cost_usd_to_save,
            'payment_cost_manager' => $payment_cost_manager_to_save,
        ];
        $this->transaction->set($set);
        $this->transaction->save();
    }

    /**
     *
     * @return string
     */
    public function get_calc_percent(): string
    {
        if (
            empty($this->calc_percent) &&
            !empty($this->cost_percent) &&
            $this->cost_percent != "0.00"
        ) {
            $calc_devided = round($this->cost_percent / 100, 4);
            $this->calc_percent = round(
                $calc_devided * $this->transaction->amount,
                Helpers_Currency::RATE_SCALE
            );
        }

        return $this->calc_percent;
    }

    /**
     *
     * @return string
     */
    public function get_calc_percent_usd(): string
    {
        if (
            empty($this->calc_percent_usd) &&
            !empty($this->cost_percent) &&
            $this->cost_percent != "0.00"
        ) {
            // convert to system currency (USD)
            $this->calc_percent_usd = Helpers_Currency::get_value_in_USD(
                $this->calc_percent,
                $this->user_currency_tab,
                $this->system_currency_tab
            );
        }

        return $this->calc_percent_usd;
    }

    /**
     *
     * @return string
     */
    public function get_calc_fixed(): string
    {
        if (
            empty($this->calc_fixed) &&
            !empty($this->cost_fixed) &&
            $this->cost_percent != "0.00" &&
            !empty($this->cost_currency_id)
        ) {
            $this->calc_fixed = $this->cost_fixed;
            $this->get_payment_cost_currency_tab();
        }

        return $this->calc_fixed;
    }

    /**
     *
     * @return string
     */
    public function get_calc_fixed_usd(): string
    {
        if (
            empty($this->calc_fixed_usd) &&
            !empty($this->cost_fixed) &&
            $this->cost_percent != "0.00" &&
            !empty($this->cost_currency_id)
        ) {
            // convert to system currency (USD)
            $this->calc_fixed_usd = Helpers_Currency::get_value_in_USD(
                $this->calc_fixed,
                $this->payment_cost_currency_tab,
                $this->system_currency_tab
            );
        }

        return $this->calc_fixed_usd;
    }

    /**
     *
     * @return string
     */
    public function get_total_calc_cost_user(): string
    {
        $this->total_calc_cost_user = round(
            $this->calc_percent + $this->calc_fixed,
            4
        );

        return $this->total_calc_cost_user;
    }

    /**
     *
     * @return string
     */
    public function get_total_calc_cost_usd(): string
    {
        $this->total_calc_cost_usd = round(
            $this->calc_percent_usd + $this->calc_fixed_usd,
            4
        );

        return $this->total_calc_cost_usd;
    }

    /**
     *
     * @return string
     */
    public function get_total_calc_cost(): string
    {
        $this->total_calc_cost = Helpers_Currency::get_recalculated_to_given_currency(
            $this->total_calc_cost_usd,
            $this->system_currency_tab,
            $this->user_currency_tab['code']
        );

        return $this->total_calc_cost;
    }

    /**
     *
     * @return string
     */
    public function get_total_calc_cost_manager(): string
    {
        $this->total_calc_cost_manager = Helpers_Currency::get_value_for_manager(
            $this->manager_currency_tab,
            $this->user_currency_tab,
            $this->total_calc_cost_user,
            $this->system_currency_tab,
            $this->total_calc_cost_usd,
            null,
            null
        );

        return $this->total_calc_cost_manager;
    }

    /**
     *
     * @return \Model_Whitelabel_User
     */
    public function get_whitelabel_user_model(): ?Model_Whitelabel_User
    {
        $this->whitelabel_user_model = Model_Whitelabel_User::find_by_pk($this->transaction->whitelabel_user_id);

        return $this->whitelabel_user_model;
    }

    /**
     *
     * @return void
     */
    public function prepare_other_settings(): void
    {
        // store instance of whitelabel in separate field for
        // transactions outside of wordpress - we will avoid additional check in this way
        // Note: it cannot be with globals since they are registered
        // for concrete hook, they are unavailable for directory function
        Lotto_Settings::getInstance()->set('transaction_whitelabel', $this->whitelabel);

        $currencies = Helpers_Currency::getCurrencies();

        // globals for hooks
        $this->globals = [ // 31.01.2019 11:11 Vordis TODO: change variables into more presentable form e.g. hook_transaction and auser into user_model
            "transaction_hook" => $this->transaction,
            "user_hook" => $this->whitelabel_user_model,
            "currencies_hook" => $currencies,
            "hpaymentmethod" => $this->payment_methods ?? null,
        ];

        $whitelabel_languages = Model_Whitelabel_Language::get_whitelabel_languages($this->whitelabel);
        $whitelabel_languages_indexed_by_id = Lotto_Helper::prepare_languages($whitelabel_languages);

        $whitelabel_user_language_id = (int) $this->whitelabel_user_model['language_id'];

        $this->whitelabel_language = $whitelabel_languages_indexed_by_id[$whitelabel_user_language_id];
    }

    /**
     *
     * @return void
     */
    public function update_total_purchases_manager(): void
    {
        $total_purchases_update_query = DB::query(
            "UPDATE whitelabel_user 
            SET total_purchases_manager = COALESCE(total_purchases_manager, 0) + :amount, 
            last_update = NOW()
            WHERE whitelabel_user.id = :user_id"
        );
        $total_purchases_update_query->param(":amount", $this->transaction->amount_manager);
        $total_purchases_update_query->param(":user_id", $this->transaction->whitelabel_user_id);

        $total_purchases_update_query->execute();
        $this->get_whitelabel_user_model();
    }

    /**
     *
     * @return void
     */
    public function subtract_from_balance(): void
    {
        $balance_update_query = DB::query(
            "UPDATE whitelabel_user 
            SET balance = balance - :amount, 
            last_update = NOW(), 
            sale_status = :status_purchased 
            WHERE whitelabel_user.id = :user_id"
        );
        $balance_update_query->param(":amount", $this->transaction->amount);
        $balance_update_query->param(":status_purchased", Helpers_General::SALE_STATUS_PURCHASED);
        $balance_update_query->param(":user_id", $this->transaction->whitelabel_user_id);

        $balance_update_query->execute();
        $this->get_whitelabel_user_model();
    }

    /**
     *
     * @return void
     */
    public function subtract_from_bonus_balance(): void
    {
        $balance_update_query = DB::query(
            "UPDATE whitelabel_user 
            SET bonus_balance = bonus_balance - :amount, 
            last_update = NOW(), 
            sale_status = :status_purchased 
            WHERE whitelabel_user.id = :user_id"
        );
        $balance_update_query->param(":amount", $this->transaction->bonus_amount);
        $balance_update_query->param(":status_purchased", Helpers_General::SALE_STATUS_PURCHASED);
        $balance_update_query->param(":user_id", $this->transaction->whitelabel_user_id);

        $balance_update_query->execute();
        $this->get_whitelabel_user_model();
    }

    public function add_lines_to_user(array &$user_set): void
    {
        $current_lines_sold_quantity = (int)$this->whitelabel_user_model['lines_sold_quantity'];
        $new_lines_quantity = 1;

        $user_set['lines_sold_quantity'] = $new_lines_quantity;
    }

    public function save_user_model(array &$user_set)
    {
        ;
    }

    /**
     *
     * @param array $user_set
     * @return void
     */
    public function add_to_balance(array &$user_set): void
    {
        $isCasinoTransaction = (bool)$this->transaction['is_casino'] ?? false;
        $fieldName = (IS_CASINO || $isCasinoTransaction) ? 'casino_balance' : 'balance';
        $balance_update_query = DB::query(
            "UPDATE whitelabel_user 
            SET $fieldName = $fieldName + :amount, 
            total_deposit_manager = COALESCE(total_deposit_manager, 0) + :amount_manager 
            WHERE whitelabel_user.id = :user_id"
        );
        $balance_update_query->param(":amount", $this->transaction->amount);
        $balance_update_query->param(":amount_manager", $this->transaction->amount_manager);
        $balance_update_query->param(":user_id", $this->whitelabel_user_model['id']);

        $pluginService = Container::get(MauticPluginService::class);
        $pluginService->setWhitelabelUser($this->whitelabel_user_model['id']);

        $pluginData = $pluginService->createDeposit($this->transaction->id);

        $user_set['last_update'] = DB::expr("NOW()");
        $user_set['last_deposit_date'] = DB::expr("NOW()");
        $user_set['last_deposit_amount_manager'] = $this->transaction->amount_manager;

        if ($this->whitelabel_user_model->sale_status < Helpers_General::SALE_STATUS_DEPOSITED) {
            $user_set['sale_status'] = Helpers_General::SALE_STATUS_DEPOSITED;
        }

        if ($this->whitelabel_user_model->first_deposit_amount_manager == null) {
            $pluginData['first_deposit_casino'] = IS_CASINO;
            $user_set['first_deposit'] = DB::expr("NOW()");
            $user_set['first_deposit_amount_manager'] = $this->transaction->amount_manager;
        } else {
            if ($this->whitelabel_user_model->second_deposit === null) {
                $user_set['second_deposit'] = DB::expr("NOW()");
            }
        }

        $this->whitelabel_user_model->set($user_set);
        $this->whitelabel_user_model->save();
        $balance_update_query->execute();
        $this->get_whitelabel_user_model();
        $user_set['balance'] = $this->whitelabel_user_model['balance'];
        $user_set['casino_balance'] = $this->whitelabel_user_model['casino_balance'];
        $user_set['total_deposit_manager'] = $this->whitelabel_user_model['total_deposit_manager'];

        $this->event->trigger('user_deposit', [
            'whitelabel_id' => $this->whitelabel['id'],
            'whitelabel_theme' => $this->whitelabel['theme'],
            'user_id' => $this->whitelabel_user_model['id'],
            'plugin_data' => array_merge($user_set, $pluginData)
        ]);
    }

    /**
     *
     * @param float $amount
     * @return void
     */
    public function add_to_bonus_balance(float $amount): void
    {
        $bonus_balance_update_query = DB::query(
            "UPDATE whitelabel_user 
            SET bonus_balance = bonus_balance + :amount, 
            last_update = NOW() 
            WHERE whitelabel_user.id = :user_id"
        );
        $bonus_balance_update_query->param(":amount", $amount);
        $bonus_balance_update_query->param(":user_id", $this->whitelabel_user_model['id']);

        $bonus_balance_update_query->execute();
    }

    /**
     *
     * @return void
     * @throws Exception
     */
    public function subtract_from_prepaid(): void
    {
        $whitelabel_prepaid = new Forms_Admin_Whitelabels_Prepaid_New($this->whitelabel);

        // $prepaid_amount = $this->transaction->cost_manager; - old version, now we have to check if specific lottery should decrease cost
        $prepaid_amount = $whitelabel_prepaid->count_prepaid_amount($this->whitelabel, $this->transaction);

        $whitelabel_transaction_id = $this->transaction->id;
        $result = $whitelabel_prepaid->subtract_prepaid(
            $prepaid_amount,
            $whitelabel_transaction_id,
            false
        );

        if ($result !== Forms_Admin_Whitelabels_Prepaid_New::RESULT_OK) {
            $message = "There was a problem subtracting prepaid. Whitelabel ID: " .
                (int)$this->whitelabel['id'];
            throw new Exception($message);
        }
    }

    /**
     *
     * @return void
     */
    public function set_tickets_paid(): void
    {
        $this->tickets = Model_Whitelabel_User_Ticket::find_by_whitelabel_transaction_id($this->transaction->id);

        $lotteries = Model_Lottery::get_really_all_lotteries_for_whitelabel(["id" => $this->transaction->whitelabel_id]);

        $tickets_normal = [];
        $tickets_multidraw = [];

        foreach ($this->tickets as $ticket) {
            if ($ticket->multi_draw_id === null) {
                $tickets_normal[] = [$ticket];
                continue;
            }
            if (!isset($tickets_multidraw[$ticket->multi_draw_id])) {
                $tickets_multidraw[$ticket->multi_draw_id] = [];
            }
            $tickets_multidraw[$ticket->multi_draw_id][] = $ticket;
        }

        $tickets_all = array_merge($tickets_normal, $tickets_multidraw);

        foreach ($tickets_all as $tickets_group) {
            $ticket_first = ArrayHelper::first($tickets_group);
            $lottery = $lotteries["__by_id"][$ticket_first['lottery_id']];

            // make sure they will be adjusted for the next draw
            $ticket_first_date = Carbon::parse($ticket_first['draw_date'], $lottery['timezone']);
            $next_draw_date = Carbon::parse($lottery['next_date_local'], $lottery['timezone']);

            if ($ticket_first_date < $next_draw_date) {
                foreach ($tickets_group as $ticket_key => $ticket) {
                    $ticket_date = Carbon::parse($ticket->draw_date, $lottery['timezone']);
                    $next_draw_date = Lotto_Helper::get_lottery_next_draw($lottery, true, null, $ticket_key + 1);
                    $ticket->set([
                        'draw_date' => $next_draw_date->format(Helpers_Time::DATETIME_FORMAT),
                        'valid_to_draw' => $next_draw_date->format(Helpers_Time::DATETIME_FORMAT)
                    ]);

                    Model_Payment_Log::add_log(
                        Model_Payment_Log::TYPE_INFO,
                        null,
                        null,
                        null,
                        $this->transaction->whitelabel_id,
                        $this->transaction->id,
                        "The ticket " . $this->whitelabel['prefix'] . 'T' . $ticket->token . " (" . $ticket->id . ") has been moved from " . $ticket_date->format(Helpers_Time::DATETIME_NO_SECONDS_FORMAT) . " to " . $next_draw_date->format(Helpers_Time::DATETIME_NO_SECONDS_FORMAT) . "!"
                    );
                }
                // TODO: inform users about draw change
            }

            foreach ($tickets_group as $ticket_key => $ticket) {
                $ticket->set([
                    'paid' => Helpers_General::TICKET_PAID
                ]);
                $ticket->save();
            }

            $this->lastPurchasedLotteryName = $lottery['name'];
        }
    }

    /**
     *
     * @param string $total_purchases_manager
     * @return bool
     */
    public function add_referafriend_bonus(string $total_purchases_manager): bool
    {
        $referafriend_bonus = new Forms_Wordpress_Bonuses_Referafriend(
            $this->whitelabel,
            $this->whitelabel_user_model,
            $total_purchases_manager
        );
        return $referafriend_bonus->process_form();
    }

    /**
     *
     * @return void
     */
    public function add_welcome_bonus(): void
    {
        // This user data is needed as array instead of object
        $user = Model_Whitelabel_User::get_single_by_id($this->transaction->whitelabel_user_id);

        if (!empty($user)) {
            $welcome_bonus = new Forms_Wordpress_Bonuses_Welcome(
                $this->whitelabel,
                $user,
                false,
                $this->transaction
            );

            $result = $welcome_bonus->process_form();

            if ($result === Forms_Wordpress_Bonuses_Welcome::RESULT_OK) {
                $bonus_ticket = $welcome_bonus->get_new_bonus_ticket();

                $pnl_update_query = DB::query(
                    "UPDATE whitelabel_user 
                    SET pnl_manager = COALESCE(pnl_manager, 0) + :amount, 
                    total_net_income_manager = COALESCE(total_net_income_manager, 0) + :amount, 
                    last_update = NOW()
                    WHERE whitelabel_user.id = :user_id"
                );
                $pnl_update_query->param(":amount", $bonus_ticket->income_manager);
                $pnl_update_query->param(":user_id", $this->whitelabel_user_model['id']);

                $pnl_update_query->execute();
            } elseif ($result === Forms_Wordpress_Bonuses_Welcome::RESULT_NO_BONUS) {
                // do nothing, there is no bonus set up
            } else {
                $this->fileLoggerService->error(
                    "Something wrong with Bonus Welcome. Status returned: " . $result
                );
            }
        }
    }

    /**
     *
     * @return void
     */
    private function add_promocode(): void
    {
        $promo = Model_Whitelabel_Campaign::get_by_user_and_transaction_id(
            $this->whitelabel_user_model->id,
            $this->transaction->id
        );

        if (empty($promo)) {
            return;
        }
        $user = $this->whitelabel_user_model->to_array();

        if (empty($user)) {
            return;
        }
        if ((int)$promo['bonus_type'] === Helpers_General::PROMO_CODE_BONUS_TYPE_FREE_LINE) {
            if (empty($promo['lottery_id'])) {
                return;
            }

            $lottery = Model_Lottery::get_single_row_by_id($promo['lottery_id']);
            $this->bonus_ticket_lottery_name = $lottery['name'];
            $bonus_ticket = new Forms_Wordpress_Bonuses_Ticket_Ticket(
                $this->whitelabel,
                $user,
                $lottery
            );
            $result_ticket = $bonus_ticket->process_form();

            if ($result_ticket !== Forms_Wordpress_Bonuses_Ticket_Ticket::RESULT_OK) {
                $message = "There is something wrong with DB. " .
                        "No bonus ticket added for whitelabel ID: " .
                        $this->whitelabel['id'] .
                        " and user ID: " . $user['id'];
                throw new \Exception($message);
            }

            $lottery_type = $bonus_ticket->get_lottery_type();

            $this->whitelabel_user_ticket = $bonus_ticket->get_new_bonus_ticket();

            $new_bonus_ticket_line = new Forms_Wordpress_Bonuses_Ticket_Line(
                $lottery,
                $lottery_type,
                $this->whitelabel_user_ticket
            );
            $result_ticket_line = $new_bonus_ticket_line->process_form();

            if ($result_ticket_line !== Forms_Wordpress_Bonuses_Ticket_Line::RESULT_OK) {
                $message = "There is something wrong with DB. " .
                        "No bonus ticket line added for whitelabel ID: " .
                        $this->whitelabel['id'] .
                        " and user ID: " . $user['id'];
                throw new \Exception($message);
            }

            Lotto_Helper::create_slips_for_ticket($this->whitelabel_user_ticket);

            $notification_draw = new Helpers_Notifications_Draw();
            $notification_draw->new_record([$this->whitelabel_user_ticket]);
        } elseif ((int)$promo['bonus_type'] === Helpers_General::PROMO_CODE_BONUS_TYPE_BONUS_MONEY) {
            if ((int)$this->transaction->type === Helpers_General::TYPE_TRANSACTION_DEPOSIT) {
                $amount = (float)$promo['bonus_balance_amount'];
                $bonus_balance_type = (int)$promo['bonus_balance_type'];
                $bonus_balance_amount = 0;

                switch ($bonus_balance_type) {
                    case Helpers_General::PROMO_CODE_BONUS_BALANCE_TYPE_PERCENT:
                        $bonus_balance_amount_percent = $amount / 100;
                        $bonus_balance_amount = $bonus_balance_amount_percent * $this->transaction->amount;
                        break;
                    case Helpers_General::PROMO_CODE_BONUS_BALANCE_TYPE_AMOUNT:
                        $bonus_balance_amount = (float)Helpers_Currency::get_recalculated_to_given_currency(
                            $amount,
                            $this->manager_currency_tab,
                            $this->user_currency_tab['code']
                        );
                        break;
                }

                $this->add_to_bonus_balance($bonus_balance_amount);
                Model_Whitelabel_User_Balance_Log::add_whitelabel_user_balance_log(
                    $this->whitelabel_user_model['id'],
                    Carbon::now()->format(Helpers_Time::DATETIME_FORMAT),
                    'Bonus balance updated by promocode.',
                    0,
                    1,
                    $bonus_balance_amount,
                    $this->user_currency_tab['code'],
                    0,
                    null
                );
            } else {
                $message = "Wrong transaction type: " .
                $this->transaction->type .
                        " No bonus balance added for user ID: " .
                        $user['id'];
                throw new \Exception($message);
            }
        }
    }

    /**
     *
     * @param int $type_aff
     * @return void
     */
    public function calculate_for_aff(
        int $type_aff = Helpers_General::TYPE_AFF_SALE
    ): void {
        Lotto_Helper::count_aff_commission(
            $this->whitelabel_user_model,
            $this->transaction,
            $this->whitelabel,
            $type_aff
        );
    }

    /**
     *
     * @return string
     */
    public function get_transaction_full_token(): string
    {
        $transaction_full_token = $this->whitelabel['prefix'] .
            'D' .
            $this->transaction->token;

        return $transaction_full_token;
    }

    /**
     *
     * @param int $type_of_email
     * @return void
     */
    public function email_send(int $type_of_email = self::EMAIL_TICKET): void
    {
        // Prepare email content
        $email_data = [
            'payment_method_name' => $this->payment_method_name,
            'amount' => $this->transaction->amount,
            'currency' => $this->user_currency_tab['code'],
            'user_timezone' => $this->whitelabel_user_model->timezone
        ];

        // Get tickets list for email
        $tickets_for_email = Model_Whitelabel_User_Ticket::get_tickets_for_email_by_transaction_id($this->transaction->id);

        $email_helper = new Helpers_Mail($this->whitelabel, $this->whitelabel_user_model);

        switch ($type_of_email) {
            case self::EMAIL_TICKET:
                $email_helper->send_ticket_email(
                    $this->whitelabel_user_model->email,
                    $this->whitelabel_language['code'],
                    $email_data,
                    $tickets_for_email
                );
                break;
            case self::EMAIL_DEPOSIT:
                $email_data['transaction_id'] = $this->get_transaction_full_token();

                $email_helper->send_deposit_success_email(
                    $this->whitelabel_user_model->email,
                    $this->whitelabel_language['code'],
                    $email_data
                );
                break;
            default:
                break;
        }

        if (isset($this->bonus_ticket_lottery_name)) {
            $data = [
                'lottery_name' => $this->bonus_ticket_lottery_name,
                'user_timezone' => $this->whitelabel_user_model->timezone,
            ];

            $email_helper = new Helpers_Mail($this->whitelabel, $this->whitelabel_user_model);
            $email_helper->send_promo_code_free_ticket_email(
                $this->whitelabel_user_model->email,
                $this->whitelabel_language['code'],
                $data
            );
        }
    }

    /**
     *
     * @return void
     */
    public function process_purchase(): void
    {
        $user_set = [];
        $this->update_total_purchases_manager();
        $user_set['last_purchase_amount_manager'] = $this->transaction->amount_manager;
        $user_set['last_purchase_date'] = DB::expr("NOW()");

        if (!Helpers_Whitelabel::is_V1($this->whitelabel['type'])) {
            $this->subtract_from_prepaid();
        }

        $this->set_tickets_paid();

        if (
            !empty($this->whitelabel_user_model->referrer_id) &&
            $this->whitelabel_user_model->refer_bonus_used == 0
        ) {
            $total_purchases_manager = $this->whitelabel_user_model->total_purchases_manager;
            $user_set['refer_bonus_used'] = $this->add_referafriend_bonus($total_purchases_manager);
        }

        $this->add_promocode();

        if ($this->whitelabel_user_model->first_purchase === null) {
            $this->add_welcome_bonus();

            $user_set['last_update'] = DB::expr("NOW()");
            $user_set['first_purchase'] = DB::expr("NOW()");
            $user_set['sale_status'] = Helpers_General::SALE_STATUS_PURCHASED;

            if (!IS_CASINO) {
                $this->calculate_for_aff(Helpers_General::TYPE_AFF_FTP);
            }
        } else {
            if ($this->whitelabel_user_model->second_purchase === null) {
                $user_set['second_purchase'] = DB::expr("NOW()");
            }
        }

        // save for balance
        $this->whitelabel_user_model->set($user_set);
        $this->whitelabel_user_model->save();
        if ($this->is_balance) {
            $this->subtract_from_balance();
        } elseif ($this->is_bonus_balance) {
            $this->subtract_from_bonus_balance();
        }

        $this->calculate_for_aff();

        Lotto_Helper::create_slips($this->transaction);

        // register purchase hook, and its unique variables
        $this->globals['htickets'] = $this->tickets;
        $this->globals['client_ip_hook'] = Lotto_Security::get_IP();

        Lotto_Helper::hook_with_globals("purchase", $this->globals);

        $this->event->trigger('user_purchase', [
            'whitelabel_id' => $this->whitelabel['id'],
            'user_id' => $this->whitelabel_user_model['id'],
            'plugin_data' => array_merge($user_set, [
                'total_net_income' => $this->whitelabel_user_model->total_net_income_manager,
                'pnl' => $this->whitelabel_user_model->pnl_manager,
                'last_purchase_lottery' => $this->lastPurchasedLotteryName
            ]),
        ]);

        // Add record to user_draw_notification table for draw email notification
        $notification_draw = new Helpers_Notifications_Draw();
        $notification_draw->new_record($this->tickets);

        $this->email_send();
    }

    /**
     *
     * @return void
     */
    public function process_deposit(): void
    {
        $user_set = [];

        $this->add_to_balance($user_set);

        // register hook for deposits
        Lotto_Helper::hook_with_globals("deposit", $this->globals);

        $this->add_promocode();
        $this->email_send(self::EMAIL_DEPOSIT);
    }

    /**
     *
     * @return int
     */
    public function process_form(): int
    {
        /**
         * RESET PAYMENT REQUESTS LOCK
         *
         * This try block cannot be moved down. If this part of code is below transaction accept part,
         * user's balance won't be updated. Probably it's because new ORM and new DB connection working in the same time.
         */
        try {
            /** @var WhitelabelTransaction $transaction */
            $transaction = WhitelabelTransaction::find($this->transaction['id']);

            /** @var WhitelabelUser $whitelabelUser */
            $whitelabelUser = $transaction->whitelabelUser;

            if (!empty($transaction->whitelabelPaymentMethod)) {
                /** @var PaymentMethod $paymentMethod */
                $paymentMethod = $transaction->whitelabelPaymentMethod->paymentMethod;

                $this->paymentRequestLockService->setUserAndPaymentMethod($whitelabelUser, $paymentMethod);
                $this->paymentRequestLockService->resetRequestsCount();
            }
        } catch (Throwable $exception) {
            $this->fileLoggerService->error(
                "Cannot reset requests count when received payment confirmation: {$exception->getMessage()}"
            );
        }

        try {
            $this->connection = \Fuel\Core\DB::instance();
            $this->connection->start_transaction();
            $queryToLock = DB::query('SELECT `status` FROM whitelabel_transaction WHERE id = :id FOR UPDATE');
            $queryToLock->param('id', $this->transaction->id);
            /** @var Database_Result $lockedTransaction */
            $lockedTransaction = $queryToLock->execute();
            $lockedTransaction = $lockedTransaction->as_array();

            $this->saveTransaction();

            if ((int)$lockedTransaction[0]['status'] !== Helpers_General::STATUS_TRANSACTION_APPROVED) {
                $this->get_user_currency_tab();
                $this->get_manager_currency_tab();
                $this->get_system_currency_tab();

                // calculate payment method costs
                $this->prepare_payment_method_costs_data();

                /* calculate payment cost */
                $this->get_calc_percent();
                $this->get_calc_percent_usd();

                $this->get_calc_fixed();
                $this->get_calc_fixed_usd();

                $this->get_total_calc_cost_user();
                $this->get_total_calc_cost_usd();
                $this->get_total_calc_cost();
                $this->get_total_calc_cost_manager();

                $this->calculation_transaction_save();

                // get auser - I changed that to whitelabel_user_model
                $this->get_whitelabel_user_model();

                $this->prepare_other_settings();

                if ((int)$this->transaction->type === Helpers_General::TYPE_TRANSACTION_PURCHASE) {
                    // If anything went wrong here, it should rollback entire purchase transaction. Used only for bonus balance purchases
                    if (!empty($this->lotteryPurchaseLimitService)) {
                        $isUserPurchaseLimitNotUpdated = !$this->lotteryPurchaseLimitService->addOrUpdatePurchaseLimitEntriesForAllowedBasket();
                        if ($isUserPurchaseLimitNotUpdated) {
                            throw new Exception('Could not update user purchase limit counter!');
                        }
                    }
                    $this->process_purchase();
                } elseif ((int)$this->transaction->type === Helpers_General::TYPE_TRANSACTION_DEPOSIT) {
                    $this->process_deposit();
                }
            }

            $this->connection->in_transaction();
            $this->connection->commit_transaction();
        } catch (\Exception $e) {
            $this->fileLoggerService->error("Transaction rollback! {$e->getMessage()}");

            DB::rollback_transaction();

            $message = "Rollback transaction. Message: " .
                $e->getMessage() . " Trace: " . $e->getTraceAsString();

            $this->fileLoggerService->error(
                $message
            );

            return self::RESULT_WITH_ERRORS;
        }

        return self::RESULT_OK;
    }
}
