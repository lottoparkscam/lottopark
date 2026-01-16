<?php

use Fuel\Core\Validation;
use Helpers\CurrencyHelper;
use Helpers\DatabaseHelper;
use Services\Plugin\MauticPluginService;
use Services\Logs\FileLoggerService;
use Wrappers\Event;
use Interfaces\PromoCode\PromoCodeApplicableInterface;
use Interfaces\PromoCode\PromoCodeTransactionApplicableInterface;
use Repositories\Orm\CurrencyRepository;
use Validators\Rules\Amount;
use Validators\Rules\CurrencyCode;
use Traits\Payments\PaymentMethodTrait;

/** @deprecated */
class Forms_Wordpress_Myaccount_Deposit extends Forms_Main implements PromoCodeApplicableInterface, PromoCodeTransactionApplicableInterface
{
    use PaymentMethodTrait;

    /** @var array */
    private $errors = [];

    /** @var array */
    private $whitelabel = [];

    /** @var array */
    private $user = [];

    /** @var array */
    private $user_currency_tab = [];

    /** @var float */
    private $minorder = 0.00;

    /**
     * @var null|Model_Whitelabel_Transaction
     */
    private $transaction = null;

    /** @var int */
    private $payment_type = null;

    private int $transactionType = Helpers_General::TYPE_TRANSACTION_DEPOSIT;

    /** @var int */
    private $whitelabel_payment_method_id = 0;

    /** @var int */
    private $payment_method_id = null;

    /** @var array */
    private $proposal_deposits = [];

    /** @var array */
    private $proposal_deposits_in_currency = [];

    /** @var string */
    private $entropay_bp = "";

    /** @var string */
    private $gateway_currency_code = "";

    /** @var string */
    private $deposit_error_amount = "0.00";

    /** @var string */
    private $custom_field_value = "0.00";

    /** @var null|array */
    private $user_currency_data = null;

    private ?Forms_Whitelabel_Bonuses_Promocodes_Code $promoCodeForm = null;

    private Event $event;

    /**
     *
     * @param array $whitelabel
     * @param int $payment_type
     * @param int $payment_method_id
     * @param int $whitelabel_payment_method_id
     * @param array $emerchant_data
     */
    public function __construct(
        array $whitelabel,
        int $payment_type = null,
        int $payment_method_id = null,
        int $whitelabel_payment_method_id = 0,
        array $emerchant_data = null
    ) {
        $this->whitelabel = $whitelabel;
        $this->payment_type = $payment_type;
        $this->payment_method_id = $payment_method_id;
        $this->whitelabel_payment_method_id = $whitelabel_payment_method_id;

        // The settings consists full user currency tab
        $this->user_currency_tab = CurrencyHelper::getCurrentCurrency()->to_array();

        $user_currency_id = (int)$this->user_currency_tab['id'];
        $user_currency_code = $this->user_currency_tab['code'];
        $this->user_currency_data = Model_Whitelabel_Default_Currency::get_for_user(
            $this->whitelabel,
            $user_currency_id
        );
        $this->minorder = $this->user_currency_data['min_deposit_amount'];

        if (!empty($emerchant_data) &&
            (int)$this->payment_type === Helpers_General::PAYMENT_TYPE_CC
        ) {
            $emerchant_method_id = Model_Whitelabel_CC_Method::get_emerchant_method_id();
            $emerchant_currency_tab = Model_Whitelabel_CC_Method::get_payment_currency_for_whitelabel(
                $this->whitelabel,
                $emerchant_method_id
            );

            $emerchant_data_settings = unserialize($emerchant_data['settings']);

            $emerchant_min_order_temp = $emerchant_data_settings['minorder'];

            $emerchant_min_order = $emerchant_min_order_temp;
            if ($emerchant_currency_tab['code'] !== $user_currency_code) {
                $emerchant_min_order = Helpers_Currency::get_recalculated_to_given_currency(
                    $emerchant_min_order_temp,
                    $emerchant_currency_tab,
                    $user_currency_code
                );
            }

            if ($this->minorder < $emerchant_min_order) {
                $this->minorder = $emerchant_min_order;
            }
        }

        $this->event = Container::get(Event::class);
    }

    public function setPromoCodeForm(Forms_Whitelabel_Bonuses_Promocodes_Code $promoCodeForm): void
    {
        $this->promoCodeForm = $promoCodeForm;
    }

    public function getPromoCodeForm(): ?Forms_Whitelabel_Bonuses_Promocodes_Code
    {
        return $this->promoCodeForm;
    }

    public function getTransactionType(): int
    {
        return $this->transactionType;
    }

    /**
     * @return array
     */
    public function get_errors():? array
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     * @return $this
     */
    public function set_errors(array $errors = null): \Forms_Wordpress_Myaccount_Deposit
    {
        $this->errors = $errors;

        return $this;
    }

    public function get_transaction():? \Model_Whitelabel_Transaction
    {
        return $this->transaction;
    }

    public function get_user_currency_tab(): array
    {
        return $this->user_currency_tab;
    }

    public function get_proposal_deposits(): array
    {
        return $this->proposal_deposits;
    }

    public function get_gateway_currency_code(): string
    {
        return $this->gateway_currency_code;
    }

    public function get_deposit_error_amount(): string
    {
        return $this->deposit_error_amount;
    }

    public function get_deposit_amount_wrong_format(): string
    {
        $amount_wrong_format_text = _("Wrong format!");
        return $amount_wrong_format_text;
    }

    public function set_user(array $user): \Forms_Wordpress_Myaccount_Deposit
    {
        $this->user = $user;

        return $this;
    }

    public function set_payment_type(int $payment_type): \Forms_Wordpress_Myaccount_Deposit
    {
        $this->payment_type = $payment_type;

        return $this;
    }

    public function set_payment_method_id(int $payment_method_id): \Forms_Wordpress_Myaccount_Deposit
    {
        $this->payment_method_id = $payment_method_id;

        return $this;
    }

    /** @return Validation object */
    protected function validate_form(): Validation
    {
        /** @var CurrencyRepository $currencyRepository */
        $currencyRepository = Container::get(CurrencyRepository::class);
        $validation = Validation::forge("deposit");

        $numMin = 0.01;
        if ($this->minorder > 0) {
            $numMin = floatval($this->minorder);
        }

        $amountRule = Amount::build('payment.amount', _('Amount'));
        $amountRule->setValidation($validation);
        $amountRule->addRule('numeric_min', $numMin)
            ->addRule('numeric_max', DatabaseHelper::DECIMAL_MAX_VALUE);
        $amountRule->applyRules();

        $amountGatewayRule = Amount::build('payment.amountingateway', _('Amount in Gateway'));
        $amountGatewayRule->setValidation($validation);
        $amountGatewayRule->addRule('numeric_min', 0.01)
            ->addRule('numeric_max', DatabaseHelper::DECIMAL_MAX_VALUE);
        $amountGatewayRule->applyRules();

        /** @var CurrencyCode $currencyCodeRule */
        $currencyCodeRule = CurrencyCode::build('payment.currencyingateway', _('Currency in Gateway'));
        $currencyCodeRule->configure($currencyRepository);
        $currencyCodeRule->setValidation($validation);
        $currencyCodeRule->applyRules();

        if (!empty(Input::post('payment.userSelectedCurrency'))) {
            /** @var CurrencyCode $userSelectedCurrencyCodeRule */
            $userSelectedCurrencyCodeRule = CurrencyCode::build('payment.userSelectedCurrency', _('User selected currency'));
            $userSelectedCurrencyCodeRule->configure($currencyRepository);
            $userSelectedCurrencyCodeRule->setValidation($validation);
            $userSelectedCurrencyCodeRule->applyRules();
        }

        return $validation;
    }

    public function process_form(): int
    {
        $errors = [];

        $validated_form = $this->validate_form();

        if ($validated_form->run()) {
            $system_currency_tab = Helpers_Currency::get_mtab_currency(false, "USD");

            $manager_currency_tab = Helpers_Currency::get_mtab_currency(
                false,
                null,
                $this->whitelabel['manager_site_currency_id']
            );

            $payment_currency_tab = Helpers_Currency::get_default_gateway_currency(
                $this->whitelabel,
                $this->payment_type,
                $this->whitelabel_payment_method_id,
                $this->user_currency_tab
            );

            $token = Lotto_Security::generate_transaction_token($this->whitelabel['id']);

            $amount = round($validated_form->validated('payment.amount'), 2);
            $amount_usd = Helpers_Currency::get_value_in_USD(
                $amount,
                $this->user_currency_tab,
                $system_currency_tab
            );

            $amount_payment = Helpers_Currency::get_recalculated_to_given_currency(
                $amount,
                $this->user_currency_tab,
                $payment_currency_tab['code']
            );
            $amount_manager = Helpers_Currency::get_value_for_manager(
                $manager_currency_tab,
                $this->user_currency_tab,
                $amount,
                $system_currency_tab,
                $amount_usd,
                $payment_currency_tab,
                $amount_payment
            );

            if (!empty($validated_form->validated('payment.userSelectedCurrency'))) {
                // If user selected custom currency, then error here should terminate payment
                try {
                    $userSelectedPaymentCurrencyTab = $this->getUserSelectedPaymentCurrencyTab($validated_form);

                    $amount_payment = Helpers_Currency::get_recalculated_to_given_currency(
                        $amount,
                        $this->user_currency_tab,
                        $userSelectedPaymentCurrencyTab['code']
                    );

                    if (!empty($userSelectedPaymentCurrencyTab)) {
                        /**
                         * User selected currency on payment page, it is valid for the gateway
                         * Override gateway's default currency with selected to calculate amount to pay for transaction
                         */
                        $payment_currency_tab = $userSelectedPaymentCurrencyTab;
                    }
                } catch (Throwable $exception) {
                    $fileLoggerService = Container::get(FileLoggerService::class);
                    $fileLoggerService->error(
                        "Deposit with user selected currency failed: {$validated_form->validated('payment.userSelectedCurrency')} " .
                        "Whitelabel: {$this->whitelabel['id']} Payment type: {$this->payment_type} Whitelabel Payment Method ID: {$this->whitelabel_payment_method_id} " .
                        "Detailed message: " . $exception->getMessage()
                    );

                    $errors = ['deposit' => _('Unknown error! Please try again later or contact us!')];
                    $this->set_errors($errors);

                    return self::RESULT_WITH_ERRORS;
                }
            }

            if (Input::post("payment.type") == Helpers_General::PAYMENT_TYPE_OTHER) {
                list(
                    $min_purchase_val,
                    $currency_code
                    ) = Helpers_Currency::get_min_purchase_for_payment_method(
                    $this->whitelabel_payment_method_id,
                    $this->user_currency_tab,
                    $payment_currency_tab
                );

                if (!empty($min_purchase_val)) {
                    $min_payment = $min_purchase_val;
                    if ($min_payment > $amount) {
                        $min_payment_formatted = Lotto_View::format_currency(
                            $min_payment,
                            $currency_code,
                            2
                        );
                        $min_payment_text_error = sprintf(
                            _("The minimum order for this payment type is %s."),
                            $min_payment_formatted
                        );
                        $errors = ['deposit' => $min_payment_text_error];
                        $this->set_errors($errors);

                        return self::RESULT_WITH_ERRORS;
                    }
                }

                list(
                    $max_deposit_reached,
                    $max_deposit_in_user_currency_formatted
                    ) = Helpers_Currency::is_max_deposit_reached(
                    $this->whitelabel,
                    $amount,
                    $amount_payment,
                    $this->user_currency_tab,
                    $payment_currency_tab
                );

                if ($max_deposit_reached) {
                    $max_deposit_text_error = sprintf(
                        _("The maximum deposit is  %s."),
                        $max_deposit_in_user_currency_formatted
                    );
                    $errors = ['deposit' => $max_deposit_text_error];
                    $this->set_errors($errors);

                    return self::RESULT_WITH_ERRORS;
                }
            }

            try {
                DB::start_transaction();

                $transaction_set = [
                    'token' => $token,
                    'whitelabel_id' => $this->whitelabel['id'],
                    'whitelabel_user_id' => $this->user['id'],
                    'currency_id' => $this->user_currency_tab['id'],
                    'payment_currency_id' => $payment_currency_tab['id'],
                    'amount' => $amount,
                    'amount_usd' => $amount_usd,
                    'amount_payment' => $amount_payment,
                    'amount_manager' => $amount_manager,
                    'date' => DB::expr("NOW()"),
                    'status' => Helpers_General::STATUS_TRANSACTION_PENDING,
                    'type' => $this->transactionType,
                    'is_casino' => IS_CASINO
                ];

                /** @var Model_Whitelabel_Transaction $transaction */
                $transaction = Model_Whitelabel_Transaction::forge();
                $transaction->set($transaction_set);
                $transaction->save();

                /** @var Model_Whitelabel_User $user */
                $user = Model_Whitelabel_User::find_by_pk($this->user['id']);
                if ($user->sale_status < Helpers_General::SALE_STATUS_STARTED_DEPOSIT) {
                    $user->sale_status = Helpers_General::SALE_STATUS_STARTED_DEPOSIT;
                    $user->save();
                }

                $this->promoCodeForm = Forms_Whitelabel_Bonuses_Promocodes_Code::get_or_create(
                    $this->whitelabel,
                    Forms_Whitelabel_Bonuses_Promocodes_Code::TYPE_DEPOSIT
                );

                $this->processPromoCode();
                $this->usePromoCodeForWhitelabelTransaction($transaction->id);

                $pluginService = Container::get(MauticPluginService::class);
                $pluginService->setWhitelabelUser($user->id);
                $pluginService->setWhitelabelPaymentMethod($this->whitelabel_payment_method_id);

                $pluginData = $pluginService->createDeposit($transaction->id);

                $this->event->trigger('user_deposit', [
                    'whitelabel_id' => $this->whitelabel['id'],
                    'whitelabel_theme' => $this->whitelabel['theme'],
                    'user_id' => $this->user['id'],
                    'plugin_data' => $pluginData
                ]);

                Session::set("transaction", $transaction->id);
                Session::set("deposit", true);
                Session::set("deposit_amount", $transaction->amount);

                Session::set("deposit_amount_gateway", $transaction->amount_payment);
                Session::set("deposit_currency_gateway", $payment_currency_tab['code']);

                $this->transaction = $transaction;
                DB::commit_transaction();
            } catch (Exception $e) {
                DB::rollback_transaction();
                Session::delete("transaction");

                Model_Payment_Log::add_log(
                    Helpers_General::TYPE_ERROR,
                    null,
                    null,
                    null,
                    $this->whitelabel['id'],
                    null,
                    "Something unusual went wrong (Debug: X2).",
                    [$e->getMessage()]
                );

                $errors = ['deposit' => _("Unknown error! Please contact us!")];
                $this->set_errors($errors);

                return self::RESULT_WITH_ERRORS;
            }
        } else {
            $errors = ['payment.custom' => _("Incorrect deposit amount value!")];
            $this->set_errors($errors);

            return self::RESULT_WITH_ERRORS;
        }

        return self::RESULT_OK;
    }

    public function get_field_class(int $index = null): string
    {
        $amount = Input::post("payment.amount");
        if (empty(Input::post("payment.amount"))) {
            $amount = Input::post("deposit.amount");
        }

        $field_class = "";
        if (($amount == $this->proposal_deposits_in_currency[$index] &&
                empty(Input::post("payment.custom"))) ||
            Session::get("deposit_amount") == $this->proposal_deposits_in_currency[$index] ||
            (!empty($this->entropay_bp) &&
                $this->entropay_bp == $this->proposal_deposits_in_currency[$index])
        ) {
            $field_class = ' deposit-amount-active';
        }

        return $field_class;
    }

    public function get_custom_field_class(): string
    {
        $field_class = "";
        if (!empty(Input::post("payment.custom")) ||
            (!empty(Input::post("deposit.amount")) && !in_array(Input::post("deposit.amount"), $this->proposal_deposits_in_currency)) ||
            !empty(Session::get("deposit_amount")) &&
            !in_array(Session::get("deposit_amount"), $this->proposal_deposits_in_currency) ||
            (!empty($this->entropay_bp) &&
                !in_array($this->entropay_bp, $this->proposal_deposits_in_currency))
        ) {
            $field_class .= ' deposit-amount-active';
        }
        if (!empty($this->errors['payment.custom'])) {
            $field_class .= ' deposit-amount-error';
        }

        return $field_class;
    }

    public function get_custom_field_value(): string
    {
//        $this->fourth_field_value = 0;
        $amount = Input::post("payment.custom");
        if (empty(Input::post("payment.custom"))) {
            $amount = Input::post("deposit.amount");
        }
        if (!empty($amount) &&
            !in_array($amount, $this->proposal_deposits_in_currency)) {
            $this->custom_field_value = htmlspecialchars($amount);
        }

        if (!empty(Session::get("deposit_amount") &&
            !in_array(Session::get("deposit_amount"), $this->proposal_deposits_in_currency))
        ) {
            $this->custom_field_value = htmlspecialchars(Session::get("deposit_amount"));
        } elseif (!empty($this->entropay_bp) &&
            !in_array($this->entropay_bp, $this->proposal_deposits_in_currency)
        ) {
            $this->custom_field_value = $this->entropay_bp;
        }

        return $this->custom_field_value;
    }

    public function get_custom_field_value_gateway(string $user_currency_code): string
    {
        $fourth_field_value_in_gateway = $this->custom_field_value;

        if (!empty($this->gateway_currency_code) &&
            (string)$this->gateway_currency_code !== (string)$user_currency_code
        ) {
            $fourth_field_value_in_gateway = Helpers_Currency::get_recalculated_to_given_currency(
                $this->custom_field_value,
                $this->user_currency_tab,
                $this->gateway_currency_code
            );
        }

        return $fourth_field_value_in_gateway;
    }

    private function process_entropay(): void
    {
        // This is in user currency - this should be in that currency
        if (!empty($this->gateway_currency_code) &&
            (string)$this->gateway_currency_code !== (string)$this->user_currency_tab['code']
        ) {
            if (!empty($this->entropay_bp)) {
                $entropay_currency_tab = Model_Whitelabel_Payment_Method_Currency::get_single_row_for_whitelabel_payment_id(
                    $this->whitelabel['id'],
                    $this->whitelabel_payment_method_id,
                    $this->user_currency_tab['id']
                );

                if (empty($entropay_currency_tab)) {
                    return;
                }

                $entropay_bp_temp = $this->entropay_bp;
                $this->entropay_bp = Helpers_Currency::get_recalculated_to_given_currency(
                    $entropay_bp_temp,
                    $entropay_currency_tab,
                    $this->user_currency_tab['code']
                );
            }
        }
    }

    private function process_proposal_deposits(): void
    {
        $this->proposal_deposits = Helpers_Currency::get_deposit_values_for_country(
            $this->whitelabel,
            $this->user_currency_tab,
            $this->gateway_currency_code
        );

        $this->proposal_deposits_in_currency = [
            $this->proposal_deposits['first_in_currency'],
            $this->proposal_deposits['second_in_currency'],
            $this->proposal_deposits['third_in_currency']
        ];
    }

    public function process_content(): void
    {
        $deposit = true;
        Lotto_Settings::getInstance()->set("deposit", $deposit);

        $this->entropay_bp = Lotto_Settings::getInstance()->get("entropay_bp");

        $user_currency_code = $this->user_currency_tab['code'];

        // At this moment it is unused
        //$country_code = Lotto_Helper::get_best_match_user_country();

        list(
            $payment_type,
            $whitelabel_payment_method_id
            ) = Helpers_Currency::get_first_payment(
            $this->whitelabel,
            $deposit
        );

        if (is_null($this->payment_type)) {
            $this->payment_type = $payment_type;
        }
        if (empty($this->whitelabel_payment_method_id)) {
            $this->whitelabel_payment_method_id = $whitelabel_payment_method_id;
        }

        $gateway_currency_tab = Helpers_Currency::get_currencies_tabs(
            $this->whitelabel,
            $this->payment_type,
            $this->whitelabel_payment_method_id
        );

        $user_currency_data = Model_Whitelabel_Default_Currency::get_for_user(
            $this->whitelabel,
            $this->user_currency_tab['id']
        );

        $deposit_min_amount = $user_currency_data['min_deposit_amount'];

        $this->gateway_currency_code = $gateway_currency_tab['code'];
        $this->process_proposal_deposits();
        $this->process_entropay();

        $this->deposit_error_amount = sprintf(
            _("The minimum deposit amount is %s."),
            Lotto_View::format_currency(
                $deposit_min_amount,
                $user_currency_code,
                true
            )
        );
    }

    /** @return bool */
    public function check_promo_active()
    {
        return Model_Whitelabel_Campaign::is_active_deposit($this->whitelabel['id']);
    }

    public function processPromoCode(): void
    {
        if ($this->promoCodeForm) {
            $this->promoCodeForm->process_content();
        }
    }

    public function usePromoCodeForWhitelabelTransaction(int $transactionId): void
    {
        if ($this->promoCodeForm) {
            $this->promoCodeForm->useForWhitelabelTransaction($transactionId);
        }
    }

    /**
     * @throws Exception
     */
    public function getUserSelectedPaymentCurrencyTab(Validation $validated_form): array
    {
        $userSelectedCurrency = $validated_form->validated('payment.userSelectedCurrency');
        $paymentMethodService = $this->selectUserPaymentCurrency($userSelectedCurrency);

        return Helpers_Currency::get_mtab_currency(
            true,
            $this->user_currency_tab['code'],
            $paymentMethodService->getCurrencyId(),
        );
    }
}
