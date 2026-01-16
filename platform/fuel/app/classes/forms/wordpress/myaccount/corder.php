<?php

use Fuel\Core\Input;
use Models\Whitelabel;
use Services\PaymentMethodCustomizationService;
use Services\Logs\FileLoggerService;
use Helpers\Wordpress\LanguageHelper;

/**
 * The name of this class is related to content-order-payment.php file
 * So it should be Content_Order_Payment at the end but it will be very long:)
 */
class Forms_Wordpress_Myaccount_Corder
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
     * @var array
     */
    private $user_currency_tab = [];

    /**
     * @var array
     */
    private $whitelabel_language = [];

    /**
     * @var bool
     */
    private $deposit = false;

    /**
     * @var bool
     */
    private $minreached = false;

    /**
     * @var bool
     */
    private $cardminreached = false;

    /**
     * @var bool
     */
    private $balancepayment = true;

    /**
     * @var array
     */
    private $ccmethods_merchant = [];

    /**
     * @var array|null
     */
    private $emerchant_data = null;

    /**
     *
     * @var float
     */
    private $payment_custom = '';

    /**
     * @var float
     */
    private $total_sum = 0;

    /**
     * @var float
     */
    private $dep_pur_amount = 0;

    /**
     * @var float
     */
    private $emerchant_min_order = 0;

    /**
     * @var float
     */
    private $amount_in_gateway = 0;

    /**
     * @var string
     */
    private $payment_gateway_currency_code = "";

    /**
     * @var string
     */
    private $payment_type = null;

    /**
     * @var string
     */
    private $whitelabel_payment_method_id = null;

    /**
     * @var array
     */
    private $gateway_currency_tab = [];

    /**
     * @var float
     */
    private $entropay_bp = 0;

    /**
     *
     * @var float
     */
    private $converted_multiplier = 0.00;

    /**
     *
     * @var array
     */
    private $promo_code = null;
    
    /**
     * @var bool
     */
    private $bonus_balance_payment = true;

    private PaymentMethodCustomizationService $paymentMethodCustomizationService;

    /**
     *
     * @param array $whitelabel
     * @param int $payment_type
     * @param int $whitelabel_payment_method_id
     * @param array $user
     * @param array $user_currency_tab
     * @param array $wlanguage
     * @param bool $deposit
     */
    public function __construct(
        array $whitelabel = [],
        int $payment_type = null,
        int $whitelabel_payment_method_id = null,
        array $user = null,
        array $user_currency_tab = [],
        array $wlanguage = [],
        bool $deposit = false
    ) {
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        $this->paymentMethodCustomizationService = Container::get(PaymentMethodCustomizationService::class);
        $this->whitelabel = $whitelabel;
        $this->payment_type = $payment_type;
        $this->whitelabel_payment_method_id = $whitelabel_payment_method_id;
        $this->user = $user;
        $this->user_currency_tab = $user_currency_tab;
        
        $whitelabel_language = [];
        if (!empty($wlanguage)) {
            $whitelabel_language = $wlanguage;
        } else {
            $whitelabel_language = LanguageHelper::getCurrentWhitelabelLanguage();
        }
        $this->whitelabel_language = $whitelabel_language;
        
        $this->deposit = $deposit;
    }

    /**
     *
     * @return array
     */
    public function get_errors()
    {
        return $this->errors;
    }

    /**
     *
     * @return bool
     */
    public function get_minreached()
    {
        return $this->minreached;
    }

    /**
     *
     * @return bool
     */
    public function get_cardminreached()
    {
        return $this->cardminreached;
    }

    /**
     *
     * @return bool
     */
    public function get_balancepayment()
    {
        return $this->balancepayment;
    }

    /**
     *
     * @return float
     */
    public function get_payment_custom()
    {
        return $this->payment_custom;
    }

    /**
     *
     * @return float
     */
    public function get_total_sum()
    {
        return $this->total_sum;
    }

    /**
     *
     * @return float
     */
    public function get_dep_pur_amount()
    {
        return $this->dep_pur_amount;
    }

    /**
     *
     * @return float
     */
    public function get_emerchant_min_order()
    {
        return $this->emerchant_min_order;
    }

    /**
     *
     * @return float
     */
    public function get_amount_in_gateway()
    {
        return $this->amount_in_gateway;
    }

    /**
     *
     * @return string
     */
    public function get_payment_gateway_currency_code()
    {
        return $this->payment_gateway_currency_code;
    }

    /**
     *
     * @return string
     */
    public function get_payment_type()
    {
        return $this->payment_type;
    }

    /**
     *
     * @return string
     */
    public function get_whitelabel_payment_method_id()
    {
        return $this->whitelabel_payment_method_id;
    }

    /**
     *
     * @return array
     */
    public function get_gateway_currency_tab()
    {
        return $this->gateway_currency_tab;
    }

    /**
     *
     * @return float
     */
    public function get_entropay_bp()
    {
        return $this->entropay_bp;
    }

    /**
     *
     * @return float
     */
    public function get_converted_multiplier(): float
    {
        return $this->converted_multiplier;
    }

    /**
     *
     * @return bool
     */
    public function get_bonus_balance_payment()
    {
        return $this->bonus_balance_payment;
    }

    /**
     *
     * @return array|null
     */
    public function get_emerchant_data():? array
    {
        $emerchant_method_id = Model_Whitelabel_CC_Method::get_emerchant_method_id();

        if (empty($this->ccmethods_merchant)) { // It should be previously set
            $this->prepare_ccmethods();
        }

        $this->emerchant_data = null;
        if (isset($this->ccmethods_merchant[$emerchant_method_id])) {
            $this->emerchant_data = unserialize($this->ccmethods_merchant[$emerchant_method_id]['settings']);
        }

        return $this->emerchant_data;
    }

    /**
     *
     * @return bool
     */
    public function get_bactive(): bool
    {
        $bactive = false;
        if (!$this->deposit) {
            $bactive = $this->user['balance'] >= $this->total_sum;
        } else {
            if ($this->minreached) {
                $bactive = true;
            }
            if ($this->deposit) {
                $bactive = false;
            }
            if (Input::post("payment.type") == Helpers_General::PAYMENT_TYPE_CC &&
                !$this->cardminreached
            ) {
                $bactive = false;
            }
        }

        return $bactive;
    }

    /**
     * It returns hardcoded data - it was hardcoded within content-order-payment.php
     * file, so I moved that to the function to make possible to dynamically
     * pull data.
     * @return array
     */
    public function get_special_emerachant_data(): array
    {
        // SPECIAL DATA - I LEAVE IT AS IT IS
        $special_data = [
            'ip' => [
                '94.26.28.135'
            ],
            'email' => [
                "ingemp@abv.bg"
            ],
            'phone' => [
                ' (+44 20 3514 2397)'
            ]
        ];

        return $special_data;
    }

    /**
     *
     * @return float
     */
    public function get_amount_payment(): float
    {
        $amount_payment = 0;
        if (!$this->deposit) {
            $amount_payment = $this->total_sum;
        } else {
            if (!empty(Input::post("payment.amount"))) {
                $amount_payment = htmlspecialchars(Input::post("payment.amount"));
            } else {
                if (!empty(Session::get("deposit_amount"))) {
                    $amount_payment = htmlspecialchars(Session::get("deposit_amount"));
                } else {
                    if (!empty($this->entropay_bp)) {
                        $amount_payment = $this->entropay_bp;
                    }
                }
            }
        }

        return $amount_payment;
    }
  
    /**
     * Method for prepare merchant methods data based on DB data and mapped such as ID
     * of emerchant is a key
     *
     * @return void
     */
    private function prepare_ccmethods(): void
    {
        $ccmethods = Model_Whitelabel_CC_Method::get_cc_methods_for_whitelabel($this->whitelabel);

        foreach ($ccmethods as $ccmethod) {
            $this->ccmethods_merchant[intval($ccmethod['method'])] = $ccmethod;
        }
    }

    /**
     *
     * @return array
     */
    private function get_default_currency_raw():? array
    {
        $user_currency_id = $this->user_currency_tab['id'];

        $user_currency_raw = Model_Whitelabel_Default_Currency::get_for_user(
            $this->whitelabel,
            $user_currency_id
        );

        $result_currency_raw = [];

        if (!is_null($user_currency_raw)) {
            $result_currency_raw = $user_currency_raw;
        } else {
            // Should not happend - if yes it means that user uses
            // currency which is currently not defined by whitelabel
            //
            $this->fileLoggerService->error(
                "User uses currency which is not defined in manager section for whitelabel: " . $user_currency_id
            );
            exit("There is a problem on server");
        }

        return $result_currency_raw;
    }

    /**
     *
     * @return void
     */
    private function prepare_dep_pur_amount(): void
    {
        $this->dep_pur_amount = 0;
        $default_currency_raw = $this->get_default_currency_raw();

        if ($this->deposit) {
            $this->balancepayment = false;
            $this->bonus_balance_payment = false;
            $deposit_min_amount_temp = $default_currency_raw['min_deposit_amount'];
            $this->dep_pur_amount = $deposit_min_amount_temp;
        } else {
            $deposit_min_amount_temp = $default_currency_raw['min_purchase_amount'];
            $this->dep_pur_amount = $deposit_min_amount_temp;
        }
    }

    /**
     *
     * @return array
     */
    private function get_manager_currency_tab(): array
    {
        $manager_currency_tab = Helpers_Currency::get_mtab_currency(
            false,
            null,
            $this->whitelabel['manager_site_currency_id']
        );

        return $manager_currency_tab;
    }

    /**
     * @return void
     */
    private function prepare_emerchant_min_order(): void
    {
        $emerchant_method_id = Model_Whitelabel_CC_Method::get_emerchant_method_id();

        $user_currency_code = $this->user_currency_tab['code'];

        $this->emerchant_min_order = 0;
        if (!empty($this->emerchant_data['minorder'])) {
            $emerchant_currency_tab = Model_Whitelabel_CC_Method::get_payment_currency_for_whitelabel(
                $this->whitelabel,
                $emerchant_method_id
            );
            $emerchant_min_order_temp = $this->emerchant_data['minorder'];
            $this->emerchant_min_order = $emerchant_min_order_temp;
            if ($emerchant_currency_tab['code'] !== $user_currency_code) {
                $this->emerchant_min_order = Helpers_Currency::get_recalculated_to_given_currency(
                    $emerchant_min_order_temp,
                    $emerchant_currency_tab,
                    $user_currency_code
                );
            }
        }
    }

    /**
     * @return void
     */
    private function check_variables(): void
    {
        $this->total_sum = 0;
        $this->amount_in_gateway = 0;
        $this->payment_gateway_currency_code = "";
        if ($this->deposit) {
            if (Input::post("payment.amount") !== null) {
                $this->total_sum = Input::post("payment.amount");
            }
            if (Session::get("deposit_amount") !== null) {
                $this->total_sum = Session::get("deposit_amount");
            }

            if (Input::post("payment.amountingateway") !== null) {
                $this->amount_in_gateway = Input::post("payment.amountingateway");
            }
            if (Session::get("deposit_amount_gateway") !== null) {
                $this->amount_in_gateway = Session::get("deposit_amount_gateway");
            }

            if (!empty(Input::post("payment.currencyingateway"))) {
                $this->payment_gateway_currency_code = Input::post("payment.currencyingateway");
            }
            if (!empty(Session::get("deposit_currency_gateway"))) {
                $this->payment_gateway_currency_code = Session::get("deposit_currency_gateway");
            }
//        } elseif (!empty($this->order) && count($this->order) > 0) {
//            $lotteries = lotto_platform_get_lotteries();
//            foreach ($this->order as $key => $item) {
//                $lottery = $lotteries['__by_id'][$item[0]];
//                $pricing = lotto_platform_get_pricing($lottery);
//                $item_price = bcmul($pricing, count($item[1]), 2);
//                $this->total_sum = bcadd($this->total_sum, $item_price, 2);
//            }
//        }
        } else {
            $total_sum = Helpers_Currency::sum_order(false);
            $promocode_obj = Forms_Whitelabel_Bonuses_Promocodes_Code::get_or_create(
                $this->whitelabel,
                Forms_Whitelabel_Bonuses_Promocodes_Code::TYPE_PURCHASE
            );
            $result = $promocode_obj->process_content();
            $code = $promocode_obj->get_promo_code();
            
            if (isset($code) && isset($code['discount_user'])) {
                $this->promo_code = $code;
                $discount = $code['discount_user'];
                $total_sum = $total_sum - $discount;
            }
            $this->total_sum = $total_sum;
        }
    }

    /**
     * @return void
     */
    private function prepare_gateway_currency_tab(): void
    {
        list(
            $payment_type,
            $whitelabel_payment_method_id
        ) = Helpers_Currency::get_first_payment(
            $this->whitelabel,
            $this->deposit,
            $this->balancepayment,
            $this->bonus_balance_payment,
            $this->minreached
        );

        if (is_null($this->payment_type)) {
            $this->payment_type = $payment_type;
        }
        if (empty($this->whitelabel_payment_method_id)) {
            $this->whitelabel_payment_method_id = $whitelabel_payment_method_id;
        }

        $this->gateway_currency_tab = Helpers_Currency::get_default_gateway_currency(
            $this->whitelabel,
            (int)$this->payment_type,
            (int)$this->whitelabel_payment_method_id,
            $this->user_currency_tab
        );
    }

    /**
     * @return void
     */
    private function prepare_entropay_and_multiplier(): void
    {
        $this->converted_multiplier = "1.00";

        // This is in user currency - this should be in that currency
        if (!empty($this->gateway_currency_tab['code']) &&
            (string)$this->gateway_currency_tab['code'] !== (string)$this->user_currency_tab['code']
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
                if ((string)$entropay_currency_tab['code'] !== (string)$this->user_currency_tab['code']) {
                    $this->entropay_bp = Helpers_Currency::get_recalculated_to_given_currency(
                        $entropay_bp_temp,
                        $entropay_currency_tab,
                        $this->user_currency_tab['code']
                    );
                }
            }

            $gateway_currency_mtab = Helpers_Currency::get_mtab_currency(
                true,
                $this->payment_gateway_currency_code
            );

            $this->converted_multiplier = Helpers_Currency::get_converted_mulitiplier(
                $gateway_currency_mtab,
                $this->user_currency_tab
            );
        }
    }

    /**
     * @return void
     */
    public function prepare_content(): void
    {
        /* temporary solution */
        $this->get_emerchant_data();

        $this->check_variables();

        $this->balancepayment = true;
        if (!empty($this->user) && $this->user['balance'] < $this->total_sum) {
            $this->balancepayment = false;
        }

        $this->bonus_balance_payment = true;
        if (!Helpers_Currency::check_is_bonus_balance_in_use() ||
        (!empty($this->user) &&
            ($this->total_sum > (float)$this->user['bonus_balance']))
        ) {
            $this->bonus_balance_payment = false;
        }

        $this->prepare_dep_pur_amount();

        $this->payment_custom = '';
        if (!empty(Input::post("payment.custom"))) {
            $this->payment_custom = htmlspecialchars(Input::post("payment.custom"));
        }

        $this->prepare_emerchant_min_order();

        if ($this->total_sum >= $this->dep_pur_amount) {
            $this->minreached = true;
        }

        if ($this->total_sum >= $this->emerchant_min_order) {
            $this->cardminreached =  true;
        }

        $this->entropay_bp = Lotto_Settings::getInstance()->get("entropay_bp");

        $this->prepare_gateway_currency_tab();

        if (empty($this->payment_gateway_currency_code)) {
            $this->payment_gateway_currency_code = $this->gateway_currency_tab['code']; // Fallback to EUR
        }

        $this->prepare_entropay_and_multiplier();

        /* end of temporary solution */
    }

    private function prepare_balance_elements($type): array
    {
        $whitelabel = Container::get('whitelabel');
        $hide_currency_symbol = $whitelabel->isTheme(Whitelabel::FAIREUM_THEME);

        $balance_class = '';
        $payment_gateway_converted_multiplier = '1.0000';
        $balance_value = 0.00;
        $balance_text = '';

        switch ($type) {
            case Helpers_General::PAYMENT_TYPE_BALANCE:
                if (((int)Input::post('payment.type') === Helpers_General::PAYMENT_TYPE_BALANCE &&
                (int)Input::post('payment.subtype') === 0) ||
                    (Input::post('payment.type') === null && !$this->bonus_balance_payment)
                ) {
                    $balance_class = ' class="active"';
                }
                $balance_value = Lotto_View::format_currency(
                    $this->user['balance'],
                    $this->user_currency_tab['code'],
                    true
                );
                $balance_text = sprintf(
                    _('Pay with account balance <span>(%s)</span>'),
                    $balance_value
                );
            break;
            case Helpers_General::PAYMENT_TYPE_BONUS_BALANCE:
                if (((int)Input::post('payment.type') === Helpers_General::PAYMENT_TYPE_BONUS_BALANCE &&
                (int)Input::post('payment.subtype') === 0) ||
                    Input::post('payment.type') === null
                ) {
                    $balance_class = ' class="active"';
                }
                $balance_value = Lotto_View::format_currency(
                    $this->user['bonus_balance'],
                    $this->user_currency_tab['code'],
                    true,
                    null,
                    2,
                    false,
                    $hide_currency_symbol
                );
                $balance_text = sprintf(
                    _("Pay with bonus balance <span>(%s)</span>"),
                    $balance_value
                );
            break;
        }

        $gateway_currency_mtab = Helpers_Currency::get_mtab_currency(
            true,
            $this->user_currency_tab['code']
        );
        $total_order_value_in_gateway = Helpers_Currency::get_sum_order_in_gateway(
            $gateway_currency_mtab,
            false
        );

        if (isset($this->promo_code) && isset($this->promo_code['discount_user'])) {
            $discount = $this->promo_code['discount_user'];
            $total_order_value_in_gateway = $total_order_value_in_gateway - $discount;
        }

        return [
            $balance_class,
            $payment_gateway_converted_multiplier,
            $total_order_value_in_gateway,
            $balance_text
        ];
    }

    /**
     *
     * @return array
     */
    public function prepare_balance_element(): array
    {
        return $this->prepare_balance_elements(Helpers_General::PAYMENT_TYPE_BALANCE);
    }

    /**
     *
     * @return array
     */
    public function prepare_bonus_balance_element(): array
    {
        return $this->prepare_balance_elements(Helpers_General::PAYMENT_TYPE_BONUS_BALANCE);
    }

    /**
     *
     * @return array
     */
    public function prepare_cc_element(): array
    {
        $cc_class = "";
        if ((Input::post("payment.type") == Helpers_General::PAYMENT_TYPE_CC &&
            Input::post("payment.subtype") == 0) ||
            (Input::post("payment.type") === null &&
            (!$this->balancepayment ||!$this->bonus_balance_payment || !empty($this->entropay_bp)))
        ) {
            $cc_class = ' class="active"';
        }

        $cc_key = "0";
        // This is probably error
        $custom_logotype = "";
        $cc_image = Lotto_View::get_payment_image(
            $cc_key,
            $custom_logotype
        );

        $total_order_value_in_gateway = 0.00;

        $payment_gateway_currency_code_cc = "";
        $payment_gateway_currency_rate_cc = "";
        $payment_gateway_converted_multiplier = "1.0000";
        $emerchant_method_id = Model_Whitelabel_CC_Method::get_emerchant_method_id();
        if (isset($this->ccmethods_merchant[$emerchant_method_id])) {  // Should be set
            $payment_gateway_currency_code_cc = $this->ccmethods_merchant[$emerchant_method_id]['currency_code'];
            $payment_gateway_currency_rate_cc = $this->ccmethods_merchant[$emerchant_method_id]['currency_rate'];
        }
        if (!empty($payment_gateway_currency_code_cc) &&
            (string)$payment_gateway_currency_code_cc !== (string)$this->user_currency_tab['code']
        ) {
            $pgateway_currency_mtab = Helpers_Currency::get_mtab_currency(
                true,
                $payment_gateway_currency_code_cc
            );
            $payment_gateway_converted_multiplier = Helpers_Currency::get_converted_mulitiplier(
                $pgateway_currency_mtab,
                $this->user_currency_tab
            );

            $total_order_value_in_gateway = Helpers_Currency::get_sum_order_in_gateway($pgateway_currency_mtab, false);
            if (isset($this->promo_code) && isset($this->promo_code['discount_user'])) {
                $discount = $this->promo_code['discount_user'];
                $discount = Helpers_Currency::get_recalculated_to_given_currency(
                    $discount,
                    $this->user_currency_tab,
                    $pgateway_currency_mtab['code']
                );
                $total_order_value_in_gateway = $total_order_value_in_gateway - $discount_gateway_curr;
            }
        } else {
            $total_order_value_in_gateway = Helpers_Currency::get_sum_order_in_gateway($this->user_currency_tab, false);
            if (isset($this->promo_code) && isset($this->promo_code['discount_user'])) {
                $discount = $this->promo_code['discount_user'];
                $total_order_value_in_gateway = $total_order_value_in_gateway - $discount;
            }
        }

        return [
            $cc_class,
            $cc_image,
            $payment_gateway_currency_code_cc,
            $payment_gateway_currency_rate_cc,
            $payment_gateway_converted_multiplier,
            $total_order_value_in_gateway
        ];
    }

    /**
     *
     * @param int $type
     * @param array $method
     * @return string
     */
    private function get_text_for_mobile(
        int $type,
        array $method
    ): string {
        $method_text = "";

        if ($type === Helpers_General::IS_MOBILE) {
            $method_text = ' (' . $method['name'] . ')';
        }

        return $method_text;
    }

    /**
     *
     * @param array $whitelabel_payment_method
     * @return string
     */
    private function get_default_payment_method_title(
        array $whitelabel_payment_method
    ): string {
        $default_title =  "";
        if (!empty($whitelabel_payment_method['name'])) {
            $default_title = $whitelabel_payment_method['name'];
        }
        
        //Set default value for Bhartipay payment method
        if ($whitelabel_payment_method['payment_method_id'] == Helpers_Payment_Method::BHARTIPAY) {
            return _('India');
        }
        
        return $default_title;
    }
        
    /**
     *
     * @param array $whitelabel_payment_method
     * @param int $type
     * @return string
     */
    public function get_method_text(
        array $whitelabel_payment_method,
        int $type = Helpers_General::IS_DESKTOP
    ): string {
        $method_text = "";
        
        $language_id = $this->get_language_id();
        
        if (empty($language_id)) {
            $method_text = $this->get_default_payment_method_title($whitelabel_payment_method);
            return $method_text;
        }
                
        $whitelabel_payment_method_id = (int)$whitelabel_payment_method['id'];

        $payment_method_customize = $this->paymentMethodCustomizationService->getWhitelabelPaymentMethodCustomizeData(
            $whitelabel_payment_method_id,
            $language_id
        );

        if (!empty($payment_method_customize)) {
            switch ($type) {
                case Helpers_General::IS_DESKTOP:
                    $method_text = $payment_method_customize['title'];
                    break;
                case  Helpers_General::IS_MOBILE:
                    $method_text = $payment_method_customize['title_for_mobile'];
                    break;
                default:
                    $method_text = "";
                    break;
            }
        } else {
            $method_text = $this->get_default_payment_method_title($whitelabel_payment_method);
        }

        return $method_text;
    }

    /**
     *
     * @param array $whitelabel_payment_method
     * @return string
     */
    public function get_proper_method_name(array $whitelabel_payment_method): string
    {
        $first_line_method_description = "";
        
        $name_to_enter = "";
        
        $default_start_text = _("Pay using ");
        
        $language_id = $this->get_language_id();
        
        if (!empty($language_id)) {
            $whitelabel_payment_method_id = (int)$whitelabel_payment_method['id'];

            $payment_method_customize = $this->paymentMethodCustomizationService->getWhitelabelPaymentMethodCustomizeData(
                $whitelabel_payment_method_id,
                $language_id
            );
            
            if (!empty($payment_method_customize) &&
                !empty($payment_method_customize['title_in_description'])
            ) {
                $default_start_text = "";
                $name_to_enter = $payment_method_customize['title_in_description'];
            } elseif (!empty($whitelabel_payment_method['name'])) {
                $name_to_enter = $this->get_default_payment_method_title($whitelabel_payment_method);
            }
        } elseif (!empty($whitelabel_payment_method['name'])) {
            $name_to_enter = $this->get_default_payment_method_title($whitelabel_payment_method);
        }
        
        if (!empty($name_to_enter)) {
            $first_line_method_description = $default_start_text .
                $name_to_enter . ".";
        }
        
        return $first_line_method_description;
    }
    
    /**
     *
     * @param array $whitelabel_payment_method
     * @return string
     */
    public function get_method_description(array $whitelabel_payment_method): string
    {
        $method_description = "";
        
        $language_id = $this->get_language_id();
        
        if (empty($language_id)) {
            return $method_description;
        }
        
        $whitelabel_payment_method_id = (int)$whitelabel_payment_method['id'];

        $payment_method_customize = $this->paymentMethodCustomizationService->getWhitelabelPaymentMethodCustomizeData(
            $whitelabel_payment_method_id,
            $language_id
        );
        
        if (!empty($payment_method_customize)) {
            $method_description = $payment_method_customize['description'];
        }
        
        return $method_description;
    }
    
    /**
     *
     * @param Model_Whitelabel_Transaction $transaction
     * @return int|null
     */
    private function get_whitelabel_payment_method_id_from_transaction(
        Model_Whitelabel_Transaction $transaction = null
    ):? int {
        if (empty($transaction)) {
            return null;
        }
        
        $type = (int)$transaction->payment_method_type;
        
        switch ($type) {
            case Helpers_General::PAYMENT_TYPE_BALANCE:
                return null;
            case Helpers_General::PAYMENT_TYPE_CC:
                return null;                        // If nothing will change in that case
                                                    // this should be OK
            case Helpers_General::PAYMENT_TYPE_OTHER:
                return $transaction->whitelabel_payment_method_id;
        }
        
        return null;
    }
    
    /**
     *
     * @param int|null $whitelabel_payment_method_id
     * @return array|null
     */
    private function get_whitelabel_payment_method(
        int $whitelabel_payment_method_id = null
    ):? array {
        $whitelabel_payment_method = Model_Whitelabel_Payment_Method::get_single_by_id($whitelabel_payment_method_id);
        
        if (empty($whitelabel_payment_method)) {
            return null;
        }
        
        return $whitelabel_payment_method;
    }
    
    /**
     * ID 2 of the language is equal to English langauge and it will be protection
     * to do not distruct application in the case of lack of proper language_id
     * @return int
     */
    private function get_default_language_id(): int
    {
        return 2;
    }
    
    /**
     *
     * @return int|null
     */
    private function get_language_id():? int
    {
        if (empty($this->whitelabel_language) ||
            empty($this->whitelabel_language['id'])
        ) {
            return null;
        }
        
        $language_id = (int)$this->whitelabel_language['id'];
        
        return $language_id;
    }
    
    /**
     *
     * @param array $whitelabel_payment_method
     * @return int|null
     */
    private function get_language_id_from_whitelabel_payment_method(
        array $whitelabel_payment_method = null
    ):? int {
        $language_id = $this->get_language_id();
        
        if (!empty($language_id)) {
            return $language_id;
        } elseif (empty($whitelabel_payment_method) ||
            empty($whitelabel_payment_method['language_id'])
        ) {
            return null;
        }
        
        // In the case that wlanguage is not set
        return (int)$whitelabel_payment_method['language_id'];
    }
    
    /**
     *
     * @param Model_Whitelabel_Transaction $transaction
     * @return array|null
     */
    private function get_payment_method_customize_by_transaction(
        Model_Whitelabel_Transaction $transaction = null
    ):? array {
        if (empty($transaction)) {
            return null;
        }
        
        $whitelabel_payment_method_id = $this->get_whitelabel_payment_method_id_from_transaction($transaction);
        
        if (empty($whitelabel_payment_method_id)) {
            return null;
        }
        
        $whitelabel_payment_method = $this->get_whitelabel_payment_method($whitelabel_payment_method_id);
        
        if (empty($whitelabel_payment_method)) {
            return null;
        }
        
        $language_id = $this->get_language_id_from_whitelabel_payment_method($whitelabel_payment_method);
        if (empty($language_id)) {
            return null;
        }

        return $this->paymentMethodCustomizationService->getWhitelabelPaymentMethodCustomizeData(
            $whitelabel_payment_method_id,
            $language_id
        );
    }
    
    /**
     *
     * @param Model_Whitelabel_Transaction $transaction
     * @return string
     */
    public function get_additional_success_text(
        Model_Whitelabel_Transaction $transaction = null
    ): string {
        $method_customize = $this->get_payment_method_customize_by_transaction($transaction);
        if (empty($method_customize)) {
            return "";
        }
        
        $additional_success_text = "";
        if (!empty($method_customize['additional_success_text'])) {
            $additional_success_text = $method_customize['additional_success_text'];
        }
        
        return $additional_success_text;
    }
    
    /**
     *
     * @param Model_Whitelabel_Transaction $transaction
     * @return string
     */
    public function get_additional_failure_text(
        Model_Whitelabel_Transaction $transaction = null
    ): string {
        $method_customize = $this->get_payment_method_customize_by_transaction($transaction);
        if (empty($method_customize)) {
            return "";
        }
        
        $additional_failure_text = "";
        if (!empty($method_customize['additional_failure_text'])) {
            $additional_failure_text = $method_customize['additional_failure_text'];
        }
        
        return $additional_failure_text;
    }
    
    /**
     *
     * @param array $whitelabel_payment_method
     * @return string
     */
    public function get_button_payment_hide_status(array $whitelabel_payment_method): string
    {
        $button_payment_hide = '0';
        if ((int)$whitelabel_payment_method['payment_method_id'] === Helpers_Payment_Method::CUSTOM) {
            $model_whitelabel_payment_method = Model_Whitelabel_Payment_Method::find_by_pk($whitelabel_payment_method['id']);
            $custom = new Forms_Wordpress_Payment_Custom();
            $custom->set_model_whitelabel_payment_method($model_whitelabel_payment_method);
            $result = $custom->check_is_custom_url_to_redirect_set();
            if (!$result) {
                $button_payment_hide = '1';
            }
        }
        
        return $button_payment_hide;
    }
    
    /**
     *
     * @param array $whitelabel_payment_method
     * @param int $index
     * @return array
     */
    public function prepare_other_element(
        array $whitelabel_payment_method,
        int $index
    ): array {
        $active_class = "";
        if ((int)Input::post("payment.type") === Helpers_General::PAYMENT_TYPE_OTHER &&
                (int)Input::post("payment.subtype") === $index
        ) {
            $active_class = ' class="active"';
        }

        $bclass = "";
        if ((int)$whitelabel_payment_method['payment_method_id'] === Helpers_Payment_Method::ENTROPAY) {
            $bclass =  ' data-bclass="entropay"';
        }

        $accept_terms_checkbox_methods = [
            Helpers_Payment_Method::TRUEVOCC,
            Helpers_Payment_Method::VISANET
        ];
        
        $has_accept_terms_checkbox = ' data-has_accept_terms_checkbox="0"';
        if (in_array($whitelabel_payment_method['payment_method_id'], $accept_terms_checkbox_methods)) {
            $has_accept_terms_checkbox = ' data-has_accept_terms_checkbox="1"';
        }

        $payment_method_image_key = $whitelabel_payment_method['payment_method_id'];
        
        $custom_logotype_url = $whitelabel_payment_method['custom_logotype'];

        $payment_image = Lotto_View::get_payment_image(
            $payment_method_image_key,
            $custom_logotype_url
        );
        
        $method_text = $this->get_method_text(
            $whitelabel_payment_method,
            Helpers_General::IS_DESKTOP
        );

        $gateway_currency_tab = [
            "id" => $whitelabel_payment_method["cid"],
            "code" => $whitelabel_payment_method["currency_code"],
            "rate" => $whitelabel_payment_method["currency_rate"]
        ];

        list(
            $min_payment_by_currency,
            $currency_code
        ) = Helpers_Currency::get_min_purchase_for_payment_method(
            (int)$whitelabel_payment_method['id'],
            $this->user_currency_tab,
            $gateway_currency_tab
        );

        $payment_gateway_converted_multiplier = "1.0000";
        if ((string)$whitelabel_payment_method['currency_code'] !== (string)$this->user_currency_tab['code']) {
            $pgateway_currency_mtab = Helpers_Currency::get_mtab_currency(
                true,
                $whitelabel_payment_method['currency_code']
            );
            $payment_gateway_converted_multiplier = Helpers_Currency::get_converted_mulitiplier(
                $pgateway_currency_mtab,
                $this->user_currency_tab
            );
            $total_order_value_in_gateway = Helpers_Currency::get_sum_order_in_gateway(
                $pgateway_currency_mtab,
                false
            );

            if (isset($this->promo_code) && isset($this->promo_code['discount_user'])) {
                $discount = $this->promo_code['discount_user'];
                $discount = Helpers_Currency::get_recalculated_to_given_currency(
                    $discount,
                    $this->user_currency_tab,
                    $pgateway_currency_mtab['code']
                );
                $total_order_value_in_gateway = $total_order_value_in_gateway - $discount;
            }
        } else {
            $total_order_value_in_gateway = Helpers_Currency::get_sum_order_in_gateway(
                $this->user_currency_tab,
                false
            );
            if (isset($this->promo_code) && isset($this->promo_code['discount_user'])) {
                $discount = $this->promo_code['discount_user'];
                $total_order_value_in_gateway = $total_order_value_in_gateway - $discount;
            }
        }

        $button_payment_hide = $this->get_button_payment_hide_status($whitelabel_payment_method);
        
        return [
            $active_class,
            $bclass,
            $payment_gateway_converted_multiplier,
            $total_order_value_in_gateway,
            $payment_image,
            $method_text,
            $min_payment_by_currency,
            $has_accept_terms_checkbox,
            $button_payment_hide
        ];
    }

    /**
     *
     * @param array $whitelabel_payment_method
     * @return string
     */
    public function get_min_payment_for_method($whitelabel_payment_method): string
    {
        $gateway_currency_tab = [
            "id" => $whitelabel_payment_method["cid"],
            "code" => $whitelabel_payment_method["currency_code"],
            "rate" => $whitelabel_payment_method["currency_rate"]
        ];

        list(
            $min_payment_by_currency,
            $currency_code
        ) = Helpers_Currency::get_min_purchase_for_payment_method(
            $whitelabel_payment_method['id'],
            $this->user_currency_tab,
            $gateway_currency_tab
        );

        $min_payment_with_currency = Lotto_View::format_currency(
            $min_payment_by_currency,
            $this->user_currency_tab['code'],
            true
        );

        return $min_payment_with_currency;
    }
}
