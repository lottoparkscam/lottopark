<?php

use Fuel\Core\Validation;
use Wrappers\Event;
use Services\Plugin\MauticPluginService;

class Forms_Whitelabel_User_Deposit extends Forms_Main
{
    const RESULT_MAX_DEPOSIT_REACHED = 100;

    /** @var array */
    private $whitelabel = [];

    /** @var View */
    private $inside = null;

    /** @var Model_Whitelabel_User */
    private $user = null;

    /** @var array */
    private $currencies = [];

    /** @var array */
    private $system_currency_tab = [];

    /** @var array */
    private $manager_currency_tab = [];

    /** @var array */
    private $user_currency_tab = [];

    /** @var array */
    private $whitelabel_payment_methods_with_currency = [];

    /** @var array */
    private $whitelabel_languages_indexed_by_id = [];

    /** @var array */
    private $gateway_currency_tab = [];

    /** @var array */
    private $whitelabel_payment_methods = [];

    private string $transactionToken;
    private Event $event;

    /**
     * @param array $whitelabel
     * @param Model_Whitelabel_User $user
     */
    public function __construct(
        array $whitelabel = [],
        Model_Whitelabel_User $user = null
    ) {
        $this->event = Container::get(Event::class);
        $this->whitelabel = $whitelabel;

        // TODO: check if $user is null and maybe create one!!!
        $this->user = $user;

        $this->system_currency_tab = Helpers_Currency::get_mtab_currency(false, "USD");

        $this->user_currency_tab = Helpers_Currency::get_mtab_currency(
            false,
            null,
            $user['currency_id']
        );

        $this->manager_currency_tab = Helpers_Currency::get_mtab_currency(
            false,
            null,
            $whitelabel['manager_site_currency_id']
        );

        $this->currencies = Lotto_Settings::getInstance()->get("currencies");

        $whitelabel_payment_methods_without_currency = Model_Whitelabel_Payment_Method::get_payment_methods_for_whitelabel($this->whitelabel);
        $this->whitelabel_payment_methods_with_currency = Helpers_Currency::get_whitelabel_payment_methods_with_currency(
            $whitelabel,
            $whitelabel_payment_methods_without_currency,
            $this->user_currency_tab
        );

        $whitelabel_languages = Model_Whitelabel_Language::get_whitelabel_languages($this->whitelabel);
        $this->whitelabel_languages_indexed_by_id = Lotto_Helper::prepare_languages($whitelabel_languages);

        $whitelabel_payment_methods = $this->get_whitelabel_payment_methods();
        $this->whitelabel_payment_methods = $whitelabel_payment_methods;
    }

    public function get_whitelabel(): array
    {
        return $this->whitelabel;
    }

    public function get_user(): Model_Whitelabel_User
    {
        return $this->user;
    }

    public function get_currencies(): array
    {
        return $this->currencies;
    }

    /** @return View */
    public function get_inside()
    {
        return $this->inside;
    }

    public function get_whitelabel_payment_methods(): array
    {
        return $this->whitelabel_payment_methods_with_currency;
    }

    public function get_whitelabel_languages_indexed_by_id(): array
    {
        return $this->whitelabel_languages_indexed_by_id;
    }

    public function getTransactionToken(): string
    {
        return $this->transactionToken ?? "";
    }

    /** @return Validation object */
    protected function validate_form(): Validation
    {
        $val = Validation::forge();

        $val->add("input.amount", _("Amount"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 0)
            ->add_rule("numeric_max", 9999999999999);

        return $val;
    }

    /**
     *
     * @param float $amount
     * @param int $payment_method_type
     * @param int $whitelabel_payment_method_id
     * @return array
     */
    private function get_payment_variables(
        $amount,
        int $payment_method_type,
        int $whitelabel_payment_method_id
    ): array {
        $whitelabel = $this->get_whitelabel();
        $payment_currency_id = 0;
        $amount_payment = $amount;

        $this->gateway_currency_tab = Helpers_Currency::get_default_gateway_currency(
            $whitelabel,
            $payment_method_type,
            $whitelabel_payment_method_id,
            $this->user_currency_tab
        );

        if (!empty($this->gateway_currency_tab)) {
            if ((string)$this->user_currency_tab['code'] !== (string)$this->gateway_currency_tab['code']) {
                $amount_payment = Helpers_Currency::get_recalculated_to_given_currency(
                    $amount,
                    $this->user_currency_tab,
                    $this->gateway_currency_tab['code']
                );
                $payment_currency_id = $this->gateway_currency_tab['id'];
            } else {
                $user = $this->get_user();
                $payment_currency_id = $user['currency_id'];
            }
        }

        // This is for prevent errors of no currency set for gateway
        if (empty($payment_currency_id)) {
            $fallback_currency_tab = Helpers_Currency::get_mtab_currency();
            if ((string)$this->user_currency_tab['code'] !== (string)$fallback_currency_tab['code']) {
                $amount_payment = Helpers_Currency::get_recalculated_to_given_currency(
                    $amount,
                    $this->user_currency_tab,
                    $fallback_currency_tab['code']
                );
                $payment_currency_id = $fallback_currency_tab['id'];
                $this->gateway_currency_tab = $fallback_currency_tab;
            }
        }

        return [
            $payment_currency_id,
            $amount_payment
        ];
    }

    private function show_cc_option(): bool
    {
        $show_cc_option = false;

        $cc_methods = Model_Whitelabel_CC_Method::get_cc_methods_for_whitelabel($this->whitelabel);
        if (!empty($cc_methods)) {
            $show_cc_option = true;
        }

        return $show_cc_option;
    }

    public function process_manual_deposit_from_crm(int $input_method, float $amount, bool $is_bonus, bool $isCasino): int
    {
        // TODO: {Vordis 2020-09-30 17:37:40} destroy this wrapper - we should have only crm method 
        $input_method_in_manager_format = array_search($input_method, array_keys($this->whitelabel_payment_methods));
        if ($input_method_in_manager_format === false) {
            return self::RESULT_SECURITY_ERROR;
        }
        $input_method_in_manager_format++; // we need to increment it by one, since select items starts from 1, not 0
        return $this->process_manual_deposit($input_method_in_manager_format, $amount, $is_bonus, $isCasino);
    }

    public function process_manual_deposit(int $input_method, float $amount, bool $is_bonus = false, bool $isCasino = false): int
    {
        $whitelabel = $this->get_whitelabel();
        $user = $this->get_user();

        $whitelabel_payment_methods_indexed = array_values($this->whitelabel_payment_methods);

        $is_num_input_method = is_numeric($input_method);
        $is_set_kmethods = isset($whitelabel_payment_methods_indexed[intval($input_method) - 1]);
        $in_array_method = in_array($input_method, ["m2"]);

        if (!(($is_num_input_method && $is_set_kmethods) || $in_array_method)) {
            return self::RESULT_SECURITY_ERROR;
        }

        $payment_method_type = Helpers_General::PAYMENT_TYPE_OTHER;
        $whitelabel_payment_method_id = null;
        if ($in_array_method) {       // I chosen Credit Card
            $payment_method_type = Helpers_General::PAYMENT_TYPE_CC;
        } elseif ($is_num_input_method) {      // Other type of payment
            $method_index = intval($input_method) - 1;
            $whitelabel_payment_method_id = $whitelabel_payment_methods_indexed[$method_index]['id'];
        }

        $amount_usd = Helpers_Currency::get_value_in_USD(
            $amount,
            $this->user_currency_tab,
            $this->system_currency_tab
        );

        list(
                $payment_currency_id,
                $amount_payment
            ) = $this->get_payment_variables(
                $amount,
                $payment_method_type,
                $whitelabel_payment_method_id
            );

        list(
                $max_deposit_reached,
                $max_deposit_in_user_currency
            ) = Helpers_Currency::is_max_deposit_reached(
                $whitelabel,
                $amount,
                $amount_payment,
                $this->user_currency_tab,
                $this->gateway_currency_tab
            );

        if ($max_deposit_reached) {
            if (isset($this->inside)) {
                $max_deposit_text_error = _("The maximum deposit is ") .
                    $max_deposit_in_user_currency;
                $errors = ['input.amount' => $max_deposit_text_error];
                $this->inside->set("errors", $errors);
            }
            return self::RESULT_MAX_DEPOSIT_REACHED;
        }

        $amount_manager = Helpers_Currency::get_value_for_manager(
            $this->manager_currency_tab,
            $this->user_currency_tab,
            $amount,
            $this->system_currency_tab,
            $amount_usd,
            $this->gateway_currency_tab,
            $amount_payment
        );

        $transaction_token = Lotto_Security::generate_transaction_token($whitelabel['id']);
        $this->transactionToken = $transaction_token;

        $transaction_set = [
            'token' => $transaction_token,
            'whitelabel_id' => $whitelabel['id'],
            'whitelabel_user_id' => $user['id'],
            'payment_method_type' => $payment_method_type,
            'whitelabel_payment_method_id' => $whitelabel_payment_method_id,
            'currency_id' => $user['currency_id'],
            'date' => DB::expr('NOW()'),
            'date_confirmed' => DB::expr('NOW()'),
            'status' => Helpers_General::STATUS_TRANSACTION_APPROVED,
            'type' => Helpers_General::TYPE_TRANSACTION_DEPOSIT,
            'payment_currency_id' => $payment_currency_id,
            'is_casino' => $isCasino,
            $is_bonus ? 'bonus_amount' : 'amount' => $amount,
            $is_bonus ? 'bonus_amount_usd' : 'amount_usd' => $amount_usd,
            $is_bonus ? 'bonus_amount_payment' : 'amount_payment' => $amount_payment,
            $is_bonus ? 'bonus_amount_manager' : 'amount_manager' => $amount_manager,
        ];

        /** @var Model_Whitelabel_Transaction $transaction */
        $transaction = Model_Whitelabel_Transaction::forge();
        $transaction->set($transaction_set);
        $transaction->save();

        $balance_total_deposit_update_query = DB::query(
            "UPDATE whitelabel_user 
                SET balance = balance + :amount, 
                total_deposit_manager = COALESCE(total_deposit_manager, 0) + :amount_manager 
                WHERE whitelabel_user.id = :user_id"
        );

        if ($is_bonus) {
            $balance_total_deposit_update_query = DB::query(
                "UPDATE whitelabel_user 
                SET bonus_balance = bonus_balance + :amount
                WHERE whitelabel_user.id = :user_id"
            );
        }

        if ($isCasino) {
            $balance_total_deposit_update_query = DB::query(
                "UPDATE whitelabel_user 
                SET casino_balance = casino_balance + :amount
                WHERE whitelabel_user.id = :user_id"
            );
        }

        $balance_total_deposit_update_query->param(":amount", $amount);
        $balance_total_deposit_update_query->param(":amount_manager", $amount_manager);
        $balance_total_deposit_update_query->param(":user_id", $user->id);

        $user_set = [
                'last_update' => DB::expr("NOW()"),
                'last_deposit_date' => DB::expr("NOW()"),
                'last_deposit_amount_manager' => $amount_manager,
                'sale_status' => 1
            ];
        if ($user->first_deposit_amount_manager == null) {
            $user_set['first_deposit_amount_manager'] = $amount_manager;
        }
        $user->set($user_set);
        $user->save();
        $balance_total_deposit_update_query->execute();

        $updated_user = Model_Whitelabel_User::find_by_pk($user->id);

        if ($is_bonus) {
            $balance_name = 'bonus_balance';
            $balance_amount = $updated_user->bonus_balance;
        } elseif ($isCasino) {
            $balance_name = 'casino_balance';
            $balance_amount = $updated_user->casino_balance;
        } else {
            $balance_name = 'balance';
            $balance_amount = $updated_user->balance;
        }

        $pluginService = Container::get(MauticPluginService::class);
        $pluginService->setWhitelabelUser($user->id);

        $pluginData = $pluginService->createDeposit($transaction->id);

        $this->event->register('user_deposit', 'Events_User_Transaction_Deposit::handle');
        $this->event->trigger('user_deposit', [
            'whitelabel_id' => $whitelabel['id'],
            'user_id' => $user->id,
            'plugin_data' => array_merge([
                $balance_name => $balance_amount,
                'last_update' => time(),
                'sale_status' => 1,
            ], $pluginData),
        ]);

        return self::RESULT_OK;
    }

    public function process_form(string $view_template): int
    {
        $this->inside = View::forge($view_template);

        $user = $this->get_user();
        $whitelabel_languages_indexed_by_id = $this->get_whitelabel_languages_indexed_by_id();
        $currencies = $this->get_currencies();

        $show_cc_option = $this->show_cc_option();

        $this->inside->set("show_cc_option", $show_cc_option);

        $this->inside->set("user", $user);
        $this->inside->set("langs", $whitelabel_languages_indexed_by_id);

        $this->inside->set("methods", $this->whitelabel_payment_methods);
        $this->inside->set("currencies", $currencies);

        if (Input::post("input.amount") === null) {
            return self::RESULT_GO_FURTHER;
        }

        $input_method = Input::post("input.method");

        $val = $this->validate_form();

        $process_result = null;
        if ($val->run()) {
            $amount = $val->validated("input.amount");
            $process_result = $this->process_manual_deposit($input_method, $amount);
        } else {
            $errors = Lotto_Helper::generate_errors($val->error());
            $this->inside->set("errors", $errors);

            return self::RESULT_WITH_ERRORS;
        }
        return $process_result;
    }
}
