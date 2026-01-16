<?php

require APPPATH . 'vendor/emerchantpay/Notifications-SDK-php_20161013/Notifications-SDK-php/lib/NotificationRouter.php';

require_once APPPATH . "vendor/emerchantpay/WebServices-SDK-php_20161013/WebServices-SDK-php-EMP/lib/WSSDK/WSSDK.php";
// we extend the WSSDK/Model/OrderSubmit class (to add order_language field)
require_once APPPATH . "vendor/emerchantpay/lotto/LottoWSSDKOrderSubmit.php";
// we extend the WSSDK/Model/Customer class (to remove mandatory first name and last name fields)
require_once APPPATH . "vendor/emerchantpay/lotto/LottoWSSDKCustomer.php";
// extended credit card for previous order ids
require_once APPPATH . "vendor/emerchantpay/lotto/LottoWSSDKPreviousCreditCard.php";

use Fuel\Core\Validation;
use Fuel\Core\Response;
use Services\Logs\FileLoggerService;

/**
 * This is the class process of the Forms_Wordpress_Payment_Emerchantpay payment
 *
 */
final class Forms_Wordpress_Payment_Emerchantpay extends Forms_Main implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method;
    private FileLoggerService $fileLoggerService;

    /**
     *
     * @var array
     */
    private $whitelabel = [];
    
    /**
     *
     * @var array
     */
    protected $user = [];
    
    /**
     *
     * @var Model_Whitelabel_Transaction
     */
    private $transaction = null;

    /**
     *
     * @var null|Model_Whitelabel_Payment_Method
     */
    private $model_whitelabel_payment_method = null;
    
    /**
     *
     * @var array
     */
    private $data = [];
    
    /**
     *
     * @var string
     */
    private $ip = "";
    
    /**
     *
     * @var bool
     */
    private $ok = false;
    
    /**
     *
     * @var null|string
     */
    private $out_id = null;

    /**
     *
     * @var array
     */
    private $ips_permitted = [
        "217.119.96.3",
        "217.119.96.4",
        "217.119.98.3",
        "217.119.98.4"
    ];
    
    /**
     *
     * @var array
     */
    private $lotteries = [];
    
    /**
     *
     * @var array
     */
    private $errors = [];
    
    /**
     *
     * @var Validation
     */
    private $validation = null;
    
    /**
     *
     * @var string
     */
    private $currency_code = "";
    
    /**
     *
     * @param array $whitelabel
     * @param array $user
     * @param Model_Whitelabel_Transaction|null $transaction
     * @param Model_Whitelabel_Payment_Method|null $model_whitelabel_payment_method
     * @param Validation|null $user_validation
     */
    public function __construct(
        ?array $whitelabel = [],
        ?array $user = [],
        ?Model_Whitelabel_Transaction $transaction = null,
        ?Model_Whitelabel_Payment_Method $model_whitelabel_payment_method = null,
        ?Validation $user_validation = null
    ) {
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        $this->whitelabel = $whitelabel;
        $this->user = $user;
        $this->transaction = $transaction;
        $this->model_whitelabel_payment_method = $model_whitelabel_payment_method;
    }

    /**
     *
     * @param string $message
     * @param int $type
     * @param array $data
     * @return void
     */
    protected function log(
        string $message,
        int $type = Helpers_General::TYPE_INFO,
        array $data = []
    ): void {
        if (empty($data)) {
            $data = null;
        }

        $whitelabel_id = $this->get_whitelabel_id();
        $transaction_id = $this->get_transaction_id();
        
        Model_Payment_Log::add_log(
            $type,
            Helpers_General::PAYMENT_TYPE_CC,
            null,
            Helpers_Payment_Method::CC_EMERCHANT,
            $whitelabel_id,
            $transaction_id,
            $message,
            $data,
            null
        );
    }

    /**
     *
     * @param string $message
     * @param array $data
     * @return void
     */
    protected function log_success(string $message, array $data = []): void
    {
        $this->log($message, Helpers_General::TYPE_SUCCESS, $data);
    }
    
    /**
     *
     * @param string $message
     * @param array $data
     * @return void
     */
    protected function log_info(string $message, array $data = []): void
    {
        $this->log($message, Helpers_General::TYPE_INFO, $data);
    }

    /**
     *
     * @param string $message
     * @param array $data
     * @return void
     */
    protected function log_error(string $message, array $data = []): void
    {
        $this->log($message, Helpers_General::TYPE_ERROR, $data);
    }
    
    /**
     *
     * @param string $message
     * @param array $data
     */
    protected function log_warning(string $message, array $data = []): void
    {
        $this->log($message, Helpers_General::TYPE_WARNING, $data);
    }
    
    /**
     *
     * @param string $message
     * @return void
     */
    protected function log_to_error_file(string $message): void
    {
        if ($this->should_test) {
            $this->fileLoggerService->error(
                $message
            );
        }
    }
    
    /**
     *
     * @return Model_Whitelabel_Transaction
     */
    public function get_transaction()
    {
        return $this->transaction;
    }

    /**
     *
     * @return array
     */
    public function get_data()
    {
        return $this->data;
    }
    
    /**
     * @param Model_Whitelabel_Transaction $transaction
     */
    public function set_transaction(Model_Whitelabel_Transaction $transaction)
    {
        $this->transaction = $transaction;
    }
    
    /**
     *
     * @return array
     */
    public function get_user()
    {
        return $this->user;
    }

    /**
     *
     * @return bool
     */
    public function get_ok(): bool
    {
        return $this->ok;
    }
    
    /**
     *
     * @return array
     */
    public function get_lotteries()
    {
        return $this->lotteries;
    }

    /**
     *
     * @return null|string
     */
    public function get_out_id():? string
    {
        return $this->out_id;
    }
    
    /**
     *
     * @param array $lotteries
     */
    public function set_lotteries($lotteries)
    {
        $this->lotteries = $lotteries;
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
     * Probably the body of the function will be changed soon
     * based on the settings for eMerchantPay allowed currencies
     *
     * @return string
     */
    private function get_currency_code()
    {
        $this->currency_code = \WSSDK\CURRENCY::EUR;
        return $this->currency_code;
    }
    
    /**
     *
     * @param array $user
     * @return \Forms_Wordpress_Payment_Emerchantpay
     */
    public function set_user($user): Forms_Wordpress_Payment_Emerchantpay
    {
        $this->user = $user;
        
        return $this;
    }

    /**
     *
     * @param string $ip
     * @return \Forms_Wordpress_Payment_Emerchantpay
     */
    public function set_ip(string $ip): Forms_Wordpress_Payment_Emerchantpay
    {
        $this->ip = $ip;
        
        return $this;
    }
    
    /**
     *
     * @return array
     */
    private function check_emerchant_data_exists()
    {
        $emerchant_method_id = Model_Whitelabel_CC_Method::get_emerchant_method_id();
        $ccmethods_merchant_id = 0;
        
        // TODO: some routing
        $ccmethods = Model_Whitelabel_CC_Method::get_cc_methods_for_whitelabel($this->whitelabel);
        $ccmethods_merchant = [];
        foreach ($ccmethods as $ccmethod) {
            $ccmethods_merchant[intval($ccmethod['method'])] = $ccmethod;
        }
        
        // check if emerchant is set
        if (empty($ccmethods_merchant[$emerchant_method_id])) {
            $ccmethods_merchant_id = $ccmethods_merchant[$emerchant_method_id]['id'];
            
            $this->transaction->set([
                'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                'payment_method_type' => Helpers_General::PAYMENT_TYPE_CC,
                'whitelabel_cc_method_id' => $ccmethods_merchant_id
            ]);
            $this->transaction->save();

            $this->log_error("No eMerchantPay data defined.");
            
            exit(_("Bad request! Please contact us!"));
        }
        
        $pdata = unserialize($ccmethods_merchant[$emerchant_method_id]['settings']);

        if (empty($pdata['accountid']) ||
            empty($pdata['apikey']) ||
            empty($pdata['endpoint'])
        ) {
            $this->transaction->set([
                'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                'payment_method_type' => Helpers_General::PAYMENT_TYPE_CC,
                'whitelabel_cc_method_id' => $ccmethods_merchant_id
            ]);
            $this->transaction->save();

            $this->log_error(
                "eMerchantPay: empty AccountID, APIKey or Endpoint.",
                $pdata
            );
            
            exit(_("Bad request! Please contact us!"));
        }
        
        return [$pdata, $ccmethods_merchant];
    }
    
    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $this->validation = Validation::forge();
        
        if (Input::post("paymentcc.card") === null ||
            Input::post("paymentcc.card") == 0
        ) {
            $this->validation->add("paymentcc.number", _("Card number"))
                ->add_rule('trim')
                ->add_rule('stripslashes')
                ->add_rule(["Lotto_Helper", "strip_spaces"])
                ->add_rule("required")
                ->add_rule('min_length', 12)
                ->add_rule('max_length', 19)
                ->add_rule('valid_string', ['numeric']);

            $this->validation->add("paymentcc.expmonth", _("Expiration date"))
                ->add_rule('trim')
                ->add_rule('stripslashes')
                ->add_rule("required")
                ->add_rule('exact_length', 2)
                ->add_rule('valid_string', ['numeric']);

            $this->validation->add("paymentcc.expyear", _("Expiration date"))
                ->add_rule('trim')
                ->add_rule('stripslashes')
                ->add_rule("required")
                ->add_rule('exact_length', 2)
                ->add_rule('valid_string', ['numeric']);

            $this->validation->add("paymentcc.name", _("Name on card"))
                ->add_rule('trim')
                ->add_rule('stripslashes')
                ->add_rule("required")
                ->add_rule('min_length', 3)
                ->add_rule('max_length', 40)
                ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'utf8']);

            $this->validation->add("paymentcc.remember", _("Save card details for future use"))
                ->add_rule('match_value', 1);
        } else {
            $this->validation->add("paymentcc.card", _("Choose saved card"))
                ->add_rule("required")
                ->add_rule('is_numeric');
        }
        
        $this->validation->add("paymentcc.cvv", _("CVV code"))->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule("required")
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 4)
            ->add_rule('valid_string', ['numeric']);

        if (!$this->validation->run()) {
            $this->errors = Lotto_Helper::generate_errors($this->validation->error());
        } else {
            if ((Input::post("paymentcc.card") === null ||
                Input::post("paymentcc.card") == 0) &&
                !Lotto_Helper::is_valid_card(
                    $this->validation->validated("paymentcc.expmonth"),
                    $this->validation->validated("paymentcc.expyear")
                )
            ) {
                $this->errors = [
                    "paymentcc.expmonth" => _("This card looks expired, please check the expiration date.")
                ];
            }
        }
        
        return $this->validation;
    }
    
    /**
     *
     * @param int $ccmethods_merchant_id
     * @return \WSSDK\Model\PaymentType\CreditCard
     */
    private function get_payment_type($ccmethods_merchant_id)
    {
        $payment_type = null;
        
        if (Input::post("paymentcc.card") !== null &&
            Input::post("paymentcc.card") != 0
        ) {
            $saved_cards = Lotto_Helper::get_e_merchant_pay_saved_cards($this->user['id']);
            $cardno = intval($this->validation->validated("paymentcc.card")) - 1;
            
            if (!empty($saved_cards) && isset($saved_cards[$cardno])) {
                $payment_type = new LottoWSSDKPreviousCreditCard();
                $payment_type->setTransactionType('sale');
                $payment_type->setCVV($this->validation->validated("paymentcc.cvv"));
                $payment_type->setLastOrderId($saved_cards[$cardno]['order_id']);

                Model_Emerchantpay_User_CC::update_last_used($this->user['id']);

                $saved_cards[$cardno]->set(["is_lastused" => 1])->save();
            } else {
                $this->transaction->set([
                    'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                    'payment_method_type' => Helpers_General::PAYMENT_TYPE_CC,
                    'whitelabel_cc_method_id' => $ccmethods_merchant_id
                ]);
                $this->transaction->save();

                $this->log_error(
                    "eMerchantPay: user choosed wrong card.",
                    [$saved_cards, $cardno]
                );
                
                exit(_("Bad request! Please contact us!"));
            }
        } else {
            $payment_type = new \WSSDK\Model\PaymentType\CreditCard();

            $payment_type->setName($this->validation->validated("paymentcc.name"));
            $payment_type->setNumber($this->validation->validated("paymentcc.number"));
            $payment_type->setExpiryMonth($this->validation->validated("paymentcc.expmonth"));
            $payment_type->setExpiryYear($this->validation->validated("paymentcc.expyear"));
            $payment_type->setCVV($this->validation->validated("paymentcc.cvv"));
            $payment_type->setTransactionType('sale');

            if ($this->validation->validated("paymentcc.remember") == 1) {
                $payment_type->setRememberCardFlag(1);
            }
        }
        
        return $payment_type;
    }
    
    /**
     *
     * @return array Consist \WSSDK\Model\Item\OneOffDynamicItem records
     */
    private function get_items()
    {
        $items = [];
        
        if ($this->transaction->type == Helpers_General::TYPE_TRANSACTION_PURCHASE) {
            $transaction_id = null;
            if (!empty($this->transaction->id)) {
                $transaction_id = $this->transaction->id;
            }
            $tickets = Model_Whitelabel_User_Ticket::get_full_data_with_counted_lines($transaction_id);

            foreach ($tickets as $ticket) {
                $lottery = $this->lotteries['__by_id'][$ticket['lottery_id']];
                
                $payment_currency_tab = Helpers_Currency::get_mtab_currency(
                    false,
                    null,
                    $ticket['payment_currency_id']
                );
                $currency_code = $payment_currency_tab['code'];
                
                //$item_price = Lotto_Helper::get_user_converted_price($lottery, $ticket['currency_id']);
                $item_price = $ticket['line_payment_amount'];
                
                $item_code = $this->whitelabel['prefix'] . "_" .
                    Lotto_Helper::get_lottery_short_name($lottery) .
                    "_TICKET";
                $item_name = sprintf(
                    _("%s ticket"),
                    _($lottery['name'])
                );
                $item_price_formatted = number_format($item_price, 2, ".", "");
                
                $item = new \WSSDK\Model\Item\OneOffDynamicItem();
                $item->setCode($item_code);
                $item->setName($item_name);
                $item->setQuantity($ticket['count']);
                $item->setUnitPrice($currency_code, $item_price_formatted);
                $item->setProductType(\WSSDK\Model\Item\DynamicItem::DIGITAL_PRODUCT);

                $items[] = $item;
            }
        } else {
            $item_code = $this->whitelabel['prefix'] . "_DEPOSIT";
            $item_name = _("Deposit");
            $item_price_formatted = number_format($this->transaction->amount_payment, 2, ".", "");
            
            $currency_code = $this->get_payment_currency($this->transaction->payment_currency_id);
            
            $item = new \WSSDK\Model\Item\OneOffDynamicItem();
            $item->setCode($item_code);
            $item->setName($item_name);
            $item->setQuantity(1);
            $item->setUnitPrice($currency_code, $item_price_formatted);
            $item->setProductType(\WSSDK\Model\Item\DynamicItem::DIGITAL_PRODUCT);

            $items[] = $item;
        }
        
        return $items;
    }
    
    /**
     *
     * @return \LottoWSSDKCustomer
     */
    private function prepare_customer()
    {
        $customer = new LottoWSSDKCustomer();
        
        $customer->setEmail($this->user['email']);
        if (!empty($this->user['name'])) {
            $customer->setFirstName($this->user['name']);
        }
        if (!empty($this->user['surname'])) {
            $customer->setLastName($this->user['surname']);
        }
        if (!empty($this->user['address_1'])) {
            $customer->setAddressLine1($this->user['address_1']);
        }
        if (!empty($this->user['address_2'])) {
            $customer->setAddressLine2($this->user['address_2']);
        }
        if (!empty($this->user['city'])) {
            $customer->setCity($this->user['city']);
        }
        if (!empty($this->user['state'])) {
            $state = explode('-', $this->user['state']);
            $customer->setState($state[1]);
        }
        if (!empty($this->user['country'])) {
            $customer->setCountry($this->user['country']);
        }
        if (!empty($this->user['zip'])) {
            $customer->setPostcode($this->user['zip']);
        }
        if (!empty($this->user['phone']) && !empty($this->user['phone_country'])) {
            $customer->setPhone($this->user['phone']);
        }
        
        return $customer;
    }
    
    /**
     *
     * @param \WSSDK $myWSSDK
     * @param boolean $test_request
     * @return int
     */
    private function save_emerchant_customer($myWSSDK, $test_request)
    {
        $customer_id = null;
        // This is assumption that eMerchantPay already saved data of the customer
        $ecustomer = $myWSSDK->customerRetrieveRequest(
            \WSSDK\Model\CustomerRetrieve::ByEmailAddress($this->user['email']),
            $test_request
        );
        $ecustomer = $ecustomer->send();

        $customer_body = $ecustomer->getBody();
        $customer_headers = $ecustomer->getHeaders();
        
        $this->log_info(
            "Retrieved customer data.",
            [$customer_headers, var_export($customer_body, true)]
        );
        
        // eMerchantPay haven't saved data of the customer = warning - possible error
        if (empty($customer_body->customer) || $customer_body->num_records == 0) {
            $this->log_warning(
                "Empty customer response.",
                [$customer_headers, var_export($customer_body, true)]
            );
        } else {
            if ($customer_body->num_records > 1) {
                $this->log_warning(
                    "More than one customer record.",
                    [$customer_headers, var_export($customer_body, true)]
                );
            }

            if (!empty($customer_body->customer)) {
                // We will use only first record from response
                // IN DB this is integer not string
                $customer_id = (string) $customer_body->customer[0]->id;

                $dbc = Model_Emerchantpay_User::forge();
                $dbc->set([
                    "whitelabel_user_id" => $this->user['id'],
                    "customer_id" => $customer_id
                ]);
                $dbc->save();
            }
        }
        
        return $customer_id;
    }
    
    /**
     *
     */
    public function process_form()
    {
        list($pdata, $ccmethods_merchant) = $this->check_emerchant_data_exists();
        $emerchant_method_id = Model_Whitelabel_CC_Method::get_emerchant_method_id();
        
        $ccmethods_merchant_id = $ccmethods_merchant[$emerchant_method_id]['id'];
        
        $currency_code = $this->get_payment_currency($this->transaction->payment_currency_id);
        
        $domain = str_replace(["https://", "http://"], "", $pdata['endpoint']);
        
        try {
            // PREPARE TRANSACTION
            $myWSSDK = new \WSSDK(
                $pdata['accountid'],
                $pdata['apikey'],
                $domain
            );

            $order = new LottoWSSDKOrderSubmit();
            
            $transaction_token_text = $this->get_prefixed_transaction_token();
            
            $order->setOrderReference($transaction_token_text);
            //$order->setOrderLanguage(substr($language['code'], 0, 2)); // TODO: check on language addition
            $order->setIpAddress(Lotto_Security::get_IP());
            $order->setCurrency($currency_code);

            $customer = null;
            $customer_id = null;

            $dbc = Model_Emerchantpay_User::find_by_whitelabel_user_id($this->user['id']);

            // if customer-emerchant exists
            if ($dbc !== null && count($dbc) > 0) {
                $customer_id = $dbc[0]['customer_id'];
                $order->setCustomerId($dbc[0]['customer_id']);

                // why not $customer object?
                // eMerchant sux, it will not let us pass customer object while customer_id is set up
                // so HOW THE FUCK ARE WE SUPPOSED TO PASS THE customer_email WHICH IS REQUIRED??
                $order->setCustomerEmail($this->user['email']);
            } else {
                $customer = $this->prepare_customer();
            }

            $payment_type = $this->get_payment_type($ccmethods_merchant_id);

            $items = $this->get_items();

            // TEST REQUEST (true)
            $test_request = $pdata['test'];
            $req = $myWSSDK->orderSubmitRequest($order, $test_request);
            if ($customer !== null) {
                // Set customer
                $req->setCustomer($customer);
            }
            // Add models
            $req->setPaymentType($payment_type);

            foreach ($items as $item) {
                $req->addItem($item);
            }
            
            // END OF PREPARING TRANSACTION //
            $this->log_info("Attempting to pay via eMerchantPay.");
            
            $res = $req->send();
            
            // GET RESPONSE FROM eMerchantPay //

            $failure_url = lotto_platform_home_url(Helper_Route::ORDER_FAILURE);
            $success_url = lotto_platform_home_url(Helper_Route::ORDER_SUCCESS);
            
            // Get deserialized data from body of request
            $body = $res->getBody();
            $headers = $res->getHeaders();
            
            // Maybe this should be saved to logs in the case of error?
            $error_from_res = $res->getError();

            $this->log_info(
                "Received response.",
                [$headers, var_export($body, true)]
            );
            
            if ($body === false) {
                $this->transaction->set([
                    'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                    'payment_method_type' => Helpers_General::PAYMENT_TYPE_CC,
                    'whitelabel_cc_method_id' => $ccmethods_merchant_id
                ]);
                $this->transaction->save();

                $this->log_error("eMerchantPay: empty body.");
                
                Response::redirect($failure_url);
            }
            ///////////// SAVE EMERCHANT CUSTOMER ID FOR LATER USE ///////////

            if ($dbc === null || count($dbc) === 0) {
                $customer_id = $this->save_emerchant_customer($myWSSDK, $test_request);
            }

            $tout_id = null;
            $data = [];
            $data['test_transaction'] = $test_request ? "1" : "0";

            $type = $body->getName();
            switch ($type) {
                case 'failure':
                    $errdata = [];
                    if (isset($body->errors)) {
                        $serrors = [];
                        foreach ($body->errors->children() as $error) {
                            $serrors[] = [(string) $error->code, (string) $error->text];
                        }
                        $data['errors'] = $serrors;
                        if (isset($body->trans_id)) {
                            $data['trans_id'] = (string) $body->trans_id;
                            $errdata[] = $data['trans_id'];
                        }
                        $errdata[] = $serrors;
                    }
                    
                    $this->log_error(
                        "eMerchantPay failure.",
                        [$type, $errdata]
                    );
                    
                    $this->transaction->set([
                        //"transaction_out_id" => $tout_id,
                        "additional_data" => serialize($data),
                        'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                        'payment_method_type' => Helpers_General::PAYMENT_TYPE_CC,
                        'whitelabel_cc_method_id' => $ccmethods_merchant_id
                    ]);
                    $this->transaction->save();

                    Response::redirect($failure_url);
                    break;
                case 'decline':
                    if (isset($body->transaction)) {
                        $otransaction = $body->transaction;
                        if (isset($otransaction->type)) {
                            $data['transaction_type'] = (string) $otransaction->type;
                        }
                        if (isset($otransaction->response)) {
                            $data['transaction_response'] = (string) $otransaction->response;
                        }
                        if (isset($otransaction->response_code)) {
                            $data['transaction_response_code'] = (string) $otransaction->response_code;
                        }
                        if (isset($otransaction->response_text)) {
                            $data['transaction_response_text'] = (string) $otransaction->response_text;
                        }
                        if (isset($otransaction->trans_id)) {
                            $data['transaction_id'] = (string) $otransaction->trans_id;
                        }
                        if (isset($otransaction->account_id)) {
                            $data['account_id'] = (string) $otransaction->account_id;
                        }
                    }
                    
                    $this->log_error(
                        "eMerchantPay decline.",
                        [$type, $data]
                    );
                    
                    $this->transaction->set([
                        "additional_data" => serialize($data),
                        'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                        'payment_method_type' => Helpers_General::PAYMENT_TYPE_CC,
                        'whitelabel_cc_method_id' => $ccmethods_merchant_id
                    ]);
                    $this->transaction->save();

                    Response::redirect($failure_url);

                    break;
                case 'order':
                    // can be pending or paid
                    if (isset($body->order_id)) {
                        $tout_id = (string) $body->order_id;
                    }
                    if (isset($body->order_datetime)) {
                        $data['order_datetime'] = (string) $body->order_datetime;
                    }
                    if (isset($body->order_total)) {
                        $data['order_total'] = (string) $body->order_total;
                    }
                    if (isset($body->order_status)) {
                        $data['order_status'] = (string) $body->order_status;
                    }
                    if (isset($body->test_transaction)) {
                        $data['test_transaction'] = (string) $body->test_transaction;
                    } else {
                        $data['test_transaction'] = "0";
                    }
                    
                    $cart = [];
                    
                    if (isset($body->cart)) {
                        foreach ($body->cart->item as $item) {
                            $dbitem = [];
                            if (isset($item->id)) {
                                $dbitem['id'] = (string) $item->id;
                            }
                            if (isset($item->code)) {
                                $dbitem['code'] = (string) $item->code;
                            }
                            if (isset($item->name)) {
                                $dbitem['name'] = (string) $item->name;
                            }
                            if (isset($item->description)) {
                                $dbitem['description'] = (string) $item->description;
                            }
                            if (isset($item->qty)) {
                                $dbitem['qty'] = (string) $item->qty;
                            }
                            if (isset($item->digital)) {
                                $dbitem['digital'] = (string) $item->digital;
                            }
                            if (isset($item->discount)) {
                                $dbitem['discount'] = (string) $item->discount;
                            }
                            if (isset($item->predefined)) {
                                $dbitem['predefined'] = (string) $item->predefined;
                            }
                            $cart[] = $dbitem;
                        }
                    }
                    
                    $data['cart'] = $cart;
                    
                    if (isset($body->transaction)) {
                        $otransaction = $body->transaction;
                        if (isset($otransaction->type)) {
                            $data['transaction_type'] = (string) $otransaction->type;
                        }
                        if (isset($otransaction->response)) {
                            $data['transaction_response'] = (string) $otransaction->response;
                        }
                        if (isset($otransaction->response_code)) {
                            $data['transaction_response_code'] = (string) $otransaction->response_code;
                        }
                        if (isset($otransaction->response_text)) {
                            $data['transaction_response_text'] = (string) $otransaction->response_text;
                        }
                        if (isset($otransaction->trans_id)) {
                            $data['transaction_id'] = (string) $otransaction->trans_id;
                        }
                        if (isset($otransaction->account_id)) {
                            $data['account_id'] = (string) $otransaction->account_id;
                        }
                    }

                    switch ($data['order_status']) {
                        case 'Paid':
                            // we accept right now, as notifications are delayed, after receiving notification, we will add more data
                            $this->log_info(
                                "Received order with Paid status.",
                                $data
                            );
                            
                            $this->transaction->set([
                                'payment_method_type' => Helpers_General::PAYMENT_TYPE_CC,
                                'whitelabel_cc_method_id' => $ccmethods_merchant_id
                            ]);
                            
                            $accept_transaction_result = Lotto_Helper::accept_transaction(
                                $this->transaction,
                                $tout_id,
                                $data,
                                $this->whitelabel
                            );

                            // Now transaction returns result as INT value and
                            // we can redirect user to fail page or success page
                            // or simply inform system about that fact
                            if ($accept_transaction_result === Forms_Transactions_Accept::RESULT_WITH_ERRORS) {
                                $this->transaction->set([
                                    'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                                    'payment_method_type' => Helpers_General::PAYMENT_TYPE_CC,
                                    'whitelabel_cc_method_id' => $ccmethods_merchant_id
                                ]);
                                $this->transaction->save();

                                $this->log_error("eMerchantPay: Something wrong went on transaction accept process.");

                                Response::redirect($failure_url);
                            }
                            
                            // save the CC data
                            // this will also happen after receiving notification, but well, let's have this data earlier

                            Lotto_Helper::e_merchant_pay_update_ccs(
                                $myWSSDK,
                                $customer_id,
                                $this->user['email'],
                                $this->whitelabel,
                                $this->transaction,
                                $this->user,
                                $test_request
                            );

                            $this->log_success(
                                "Payment successful, redirecting to success, waiting for notification."
                            );
                            
                            Response::redirect($success_url);
                            break;
                        case 'Pending':
                        default:
                            $this->log_info(
                                "Received order with Pending status. Waiting for notification...",
                                $data
                            );
                            
                            $this->transaction->set([
                                'transaction_out_id' => $tout_id,
                                'payment_method_type' => Helpers_General::PAYMENT_TYPE_CC,
                                'additional_data' => serialize($data),
                                'whitelabel_cc_method_id' => $ccmethods_merchant_id
                            ]);
                            $this->transaction->save();
                            
                            if (isset($otransaction->redirect_url)) {
                                $redirect_url = (string) $otransaction->redirect_url;
                                
                                $this->log_success(
                                    "Found redirect_url! Redirecting the user.",
                                    [$redirect_url]
                                );
                                
                                Response::redirect($redirect_url);
                                exit();
                            }
                            
                            $this->log_success(
                                "Redirecting to success, however waiting for notification."
                            );
                            
                            Response::redirect($success_url);
                            break;
                    }

                    break;
                default:
                    $this->transaction->set([
                        'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                        'payment_method_type' => Helpers_General::PAYMENT_TYPE_CC,
                        'whitelabel_cc_method_id' => $ccmethods_merchant_id
                    ]);
                    
                    $this->log_error(
                        "eMerchantPay unknown response.",
                        [$type]
                    );
                    
                    $this->errors = ["cc" => _("Unknown error! Please contact us!")];
                    break;
            }

            //exit();
        } catch (Exception $e) {
            $this->transaction->set([
                'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                'payment_method_type' => Helpers_General::PAYMENT_TYPE_CC
            ]);
            
            $this->log_error(
                "eMerchantPay unknown error.",
                [$e->getMessage()]
            );
            
            $this->errors = ["cc" => _("Unknown error! Please contact us!")];
        }
    }
    
    /**
     * This function is for call eMerchantPay static function RouteProcess (from library)
     * and process everything what is needed for their system to get everything what
     * is needed by our system - this is strictly separated functionality for eMerchantPay
     * system - prepare needed data and run
     */
    private static function process_notification()
    {
        \NotificationSDK\NotificationRouter::RouteProcess(
            '*',
            new \NotificationSDK\DefaultProcessor(
                // all notifications will be handled by this function
                function (\NotificationSDK\iNotification $notification, $DEBUG) {
                    $emerchant_method_id = Model_Whitelabel_CC_Method::get_emerchant_method_id();
                    
                    $transaction = \Lotto_Settings::getInstance()->get("temp_transaction");
                    $data = \Lotto_Settings::getInstance()->get("temp_data");
                    $out_id = \Lotto_Settings::getInstance()->get("temp_out_id");
                    $whitelabel = \Lotto_Settings::getInstance()->get("temp_whitelabel");
                    $ok = \Lotto_Settings::getInstance()->get("temp_ok");
                    $myWSSDK = \Lotto_Settings::getInstance()->get("temp_wssdk");
                    $test_request = \Lotto_Settings::getInstance()->get("temp_test");

                    // get the data from the notification
                    $post = $notification->getData();
                    
                    \Model_Payment_Log::add_log(
                        Helpers_General::TYPE_INFO,
                        Helpers_General::PAYMENT_TYPE_CC,
                        null,
                        $emerchant_method_id,
                        $whitelabel['id'],
                        $transaction->id,
                        "Readed eMerchantPay confirmation.",
                        [$post]
                    );

                    switch ($notification->getType()) {
                        case 'orderpending':
                        case 'orderdeclined':
                        case 'orderfailure':
                        case 'order':

                            \Model_Payment_Log::add_log(
                                Helpers_General::TYPE_INFO,
                                Helpers_General::PAYMENT_TYPE_CC,
                                null,
                                $emerchant_method_id,
                                $whitelabel['id'],
                                $transaction->id,
                                "Notification type: " . $notification->getType(),
                                null
                            );

                            if (bccomp($post['amount'], $data['order_total'], 2) != 0) {
                                \Model_Payment_Log::add_log(
                                    Helpers_General::TYPE_WARNING,
                                    Helpers_General::PAYMENT_TYPE_CC,
                                    null,
                                    $emerchant_method_id,
                                    $whitelabel['id'],
                                    $transaction->id,
                                    "Amount mismatch.",
                                    [
                                        "data" => $data['order_total'],
                                        "post" => $post['amount']
                                    ]
                                );
                                $data['order_total'] = $post['amount'];
                            }

                            if ($post['order_id'] != $out_id) {
                                \Model_Payment_Log::add_log(
                                    Helpers_General::TYPE_WARNING,
                                    Helpers_General::PAYMENT_TYPE_CC,
                                    null,
                                    $emerchant_method_id,
                                    $whitelabel['id'],
                                    $transaction->id,
                                    "Transaction Out Id (Order ID) mismatch.",
                                    [
                                        "data" => $out_id,
                                        "post" => $post['order_id']
                                    ]
                                );
                                $out_id = $post['order_id'];
                            }

                            if ($post['test_transaction'] != $data['test_transaction']) {
                                \Model_Payment_Log::add_log(
                                    Helpers_General::TYPE_WARNING,
                                    Helpers_General::PAYMENT_TYPE_CC,
                                    null,
                                    $emerchant_method_id,
                                    $whitelabel['id'],
                                    $transaction->id,
                                    "Test transaction mismatch.",
                                    [
                                        "data" => $data['test_transaction'],
                                        "post" => $post['test_transaction']
                                    ]
                                );
                                $data['test_transaction'] = $post['test_transaction'];
                            }

                            if ($data['transaction_id'] != $post['trans_id']) {
                                \Model_Payment_Log::add_log(
                                    Helpers_General::TYPE_WARNING,
                                    Helpers_General::PAYMENT_TYPE_CC,
                                    null,
                                    $emerchant_method_id,
                                    $whitelabel['id'],
                                    $transaction->id,
                                    "Transaction ID mismatch.",
                                    [
                                        "data" => $data['transaction_id'],
                                        "post" => $post['trans_id']
                                    ]
                                );
                                $data['transaction_id'] = $post['trans_id'];
                            }

                            if ($data['transaction_type'] != $post['trans_type']) {
                                \Model_Payment_Log::add_log(
                                    Helpers_General::TYPE_WARNING,
                                    Helpers_General::PAYMENT_TYPE_CC,
                                    null,
                                    $emerchant_method_id,
                                    $whitelabel['id'],
                                    $transaction->id,
                                    "Transaction type mismatch.",
                                    [
                                        "data" => $data['transaction_type'],
                                        "post" => $post['trans_type']
                                    ]
                                );
                                $data['transaction_type'] = $post['trans_type'];
                            }

                            // end of checks
                            // now new data
                            if (isset($post['auth_code'])) {
                                $data['auth_code'] = $post['auth_code'];
                            }
                            if (isset($post['card_brand'])) {
                                $data['card_brand'] = $post['card_brand'];
                            }
                            if (isset($post['card_category'])) {
                                $data['card_category'] = $post['card_category'];
                            }
                            if (isset($post['card_exp_month'])) {
                                $data['card_exp_month'] = $post['card_exp_month'];
                            }
                            if (isset($post['card_exp_year'])) {
                                $data['card_exp_year'] = $post['card_exp_year'];
                            }
                            if (isset($post['card_holder_name'])) {
                                $data['card_holder_name'] = $post['card_holder_name'];
                            }
                            if (isset($post['card_issuing_bank'])) {
                                $data['card_issuing_bank'] = $post['card_issuing_bank'];
                            }
                            if (isset($post['card_issuing_country'])) {
                                $data['card_issuing_country'] = $post['card_issuing_country'];
                            }
                            if (isset($post['card_number'])) {
                                $data['card_number'] = $post['card_number'];
                            }
                            if (isset($post['card_sub_category'])) {
                                $data['card_sub_category'] = $post['card_sub_category'];
                            }
                            if (isset($post['client_id'])) {
                                $data['client_id'] = $post['client_id'];
                            }
                            if (isset($post['customer_address'])) {
                                $data['customer_address'] = $post['customer_address'];
                            }
                            if (isset($post['customer_address2'])) {
                                $data['customer_address2'] = $post['customer_address2'];
                            }
                            if (isset($post['customer_city'])) {
                                $data['customer_city'] = $post['customer_city'];
                            }
                            if (isset($post['customer_company'])) {
                                $data['customer_company'] = $post['customer_company'];
                            }
                            if (isset($post['customer_country'])) {
                                $data['customer_country'] = $post['customer_country'];
                            }
                            if (isset($post['customer_email'])) {
                                $data['customer_email'] = $post['customer_email'];
                            }
                            if (isset($post['customer_first_name'])) {
                                $data['customer_first_name'] = $post['customer_first_name'];
                            }
                            if (isset($post['customer_id'])) {
                                $data['customer_id'] = $post['customer_id'];
                            }
                            if (isset($post['customer_last_name'])) {
                                $data['customer_last_name'] = $post['customer_last_name'];
                            }
                            if (isset($post['customer_phone'])) {
                                $data['customer_phone'] = $post['customer_phone'];
                            }
                            if (isset($post['customer_postcode'])) {
                                $data['customer_postcode'] = $post['customer_postcode'];
                            }
                            if (isset($post['customer_state'])) {
                                $data['customer_state'] = $post['customer_state'];
                            }
                            if (isset($post['domain'])) {
                                $data['domain'] = $post['domain'];
                            }

                            // checking cart items...
                            $i = 1;
                            while (isset($post['item_' . $i . '_code'])) {
                                if (!isset($data['cart'][$i - 1])) {
                                    \Model_Payment_Log::add_log(
                                        Helpers_General::TYPE_WARNING,
                                        Helpers_General::PAYMENT_TYPE_CC,
                                        null,
                                        $emerchant_method_id,
                                        $whitelabel['id'],
                                        $transaction->id,
                                        "Item does not exist!",
                                        [
                                            "post" => $post['item_' . $i . '_id']
                                        ]
                                    );
                                    break;
                                }

                                $dbitem = &$data['cart'][$i - 1];
                                if ($dbitem['id'] != $post['item_' . $i . '_id'] ||
                                    $dbitem['code'] != $post['item_' . $i . '_code'] ||
                                    $dbitem['name'] != $post['item_' . $i . '_name'] ||
                                    $dbitem['description'] != $post['item_' . $i . '_description'] ||
                                    $dbitem['qty'] != $post['item_' . $i . '_qty'] ||
                                    $dbitem['digital'] != $post['item_' . $i . '_digital']
                                ) {
                                    \Model_Payment_Log::add_log(
                                        Helpers_General::TYPE_WARNING,
                                        Helpers_General::PAYMENT_TYPE_CC,
                                        null,
                                        $emerchant_method_id,
                                        $whitelabel['id'],
                                        $transaction->id,
                                        "Item mismatch!",
                                        [
                                            "post" => $post['item_' . $i . '_id']
                                        ]
                                    );
                                }

                                if (isset($post['item_' . $i . '_id'])) {
                                    $dbitem['id'] = $post['item_' . $i . '_id'];
                                }
                                if (isset($post['item_' . $i . '_code'])) {
                                    $dbitem['code'] = $post['item_' . $i . '_code'];
                                }
                                if (isset($post['item_' . $i . '_name'])) {
                                    $dbitem['name'] = $post['item_' . $i . '_name'];
                                }
                                if (isset($post['item_' . $i . '_description'])) {
                                    $dbitem['description'] = $post['item_' . $i . '_description'];
                                }
                                if (isset($post['item_' . $i . '_qty'])) {
                                    $dbitem['qty'] = $post['item_' . $i . '_qty'];
                                }
                                if (isset($post['item_' . $i . '_digital'])) {
                                    $dbitem['digital'] = $post['item_' . $i . '_digital'];
                                }
                                if (isset($post['item_' . $i . '_pass_through'])) {
                                    $dbitem['pass_through'] = $post['item_' . $i . '_pass_through'];
                                }
                                if (isset($post['item_' . $i . '_rebill'])) {
                                    $dbitem['rebill'] = $post['item_' . $i . '_rebill'];
                                }

                                $currencies = [];
                                foreach ($post as $search_item => $value) {
                                    if (substr($search_item, 0, 5) == "item_") {
                                        $sitem_arr = explode("_", $search_item);
                                        if ($sitem_arr[1] == $i &&
                                            $sitem_arr[2] == "unit" &&
                                            $sitem_arr[3] == "price"
                                        ) {
                                            $currencies[] = $sitem_arr[4];
                                        }
                                    }
                                }

                                $dbitem['unit_prices'] = [];
                                foreach ($currencies as $currency) {
                                    $dbitem['unit_prices'][] = [
                                        $currency,
                                        $post['item_' . $i . '_unit_price_' . $currency]
                                    ];
                                }
                                // remove reference
                                unset($dbitem);
                                $i++;
                            }

                            if (isset($post['merchant_user_id'])) {
                                $data['merchant_user_id'] = $post['merchant_user_id'];
                            }
                            if (isset($post['notification_type'])) {
                                $data['notification_type'] = $post['notification_type'];
                            }
                            if (isset($post['order_currency'])) {
                                $data['order_currency'] = $post['order_currency'];
                            }
                            if (isset($post['order_datetime'])) {
                                $data['order_datetime'] = $post['order_datetime'];
                            }
                            if (isset($post['pass_through'])) {
                                $data['pass_through'] = $post['pass_through'];
                            }
                            if (isset($post['payment_method'])) {
                                $data['payment_method'] = $post['payment_method'];
                            }

                            // overwrite response
                            if (isset($post['response'])) {
                                $data['transaction_response'] = $post['response'];
                            }
                            if (isset($post['response_code'])) {
                                $data['transaction_response_code'] = $post['response_code'];
                            }
                            if (isset($post['response_text'])) {
                                $data['transaction_response_text'] = $post['response_text'];
                            }

                            if ($notification->getType() == "order") {
                                $ok = true;

                                //////// GET SAVED CCS DATA //////

                                $user = Model_Whitelabel_User::find([
                                    "where" => [
                                        "whitelabel_id" => $whitelabel['id'],
                                        "email" => $data['customer_email']
                                    ]]);

                                if ($user !== null && count($user) > 0) {
                                    $user = $user[0];
                                    Lotto_Helper::e_merchant_pay_update_ccs(
                                        $myWSSDK,
                                        $data['customer_id'],
                                        $data['customer_email'],
                                        $whitelabel,
                                        $transaction,
                                        $user,
                                        $test_request
                                    );
                                } else {
                                    \Model_Payment_Log::add_log(
                                        Helpers_General::TYPE_WARNING,
                                        Helpers_General::PAYMENT_TYPE_CC,
                                        null,
                                        $emerchant_method_id,
                                        $whitelabel['id'],
                                        null,
                                        "Couldn't find user with specified e-mail.",
                                        [$data['customer_email']]
                                    );
                                }

                                ////////// END OF GET SAVED CCS DATA //////
                            }

                            // pending
                            if ($ok == false) {
                                $transaction->set([
                                    "additional_data" => serialize($data),
                                    "transaction_out_id" => $out_id
                                ]);
                                $transaction->save();
                            }
                            \Lotto_Settings::getInstance()->set("temp_transaction", $transaction);
                            \Lotto_Settings::getInstance()->set("temp_data", $data);
                            \Lotto_Settings::getInstance()->set("temp_out_id", $out_id);
                            \Lotto_Settings::getInstance()->set("temp_ok", $ok);
                            break;

                        default:
                            status_header(400);
                            \Model_Payment_Log::add_log(
                                Helpers_General::TYPE_ERROR,
                                Helpers_General::PAYMENT_TYPE_CC,
                                null,
                                $emerchant_method_id,
                                $whitelabel['id'],
                                null,
                                "Not supported payment notification type.",
                                [$notification->getType()]
                            );
                            exit(_("Bad request! Please contact us!"));
                            break;
                    }
                }
            )
        );
    }
    
    /**
     *
     */
    public function process_confirmation()
    {
        $emerchant_method_id = Model_Whitelabel_CC_Method::get_emerchant_method_id();
        
        try {
            $this->log_info(
                "Received eMerchantPay confirmation.",
                Input::post()
            );
            
            if (!in_array($this->ip, $this->ips_permitted)) {
                status_header(400);
                
                $this->log_error(
                    "Bad IP.",
                    [$this->ip]
                );
                
                exit(_("Bad request! Please contact us!"));
            }

            $transaction_token = intval(substr(Input::post("order_reference"), 3));
            
            $transaction_temp = Model_Whitelabel_Transaction::find([
                "where" => [
                    "whitelabel_id" => $this->whitelabel['id'],
                    "token" => $transaction_token
                ]
            ]);

            if ($transaction_temp === null) {
                status_header(400);
                
                $this->log_error("Bad transaction.");
                
                exit(_("Bad request! Please contact us!"));
            }
            $this->transaction = $transaction_temp[0];
            
            $currency_code = $this->get_payment_currency($this->transaction->payment_currency_id);
            
            if (!((int)$this->transaction->whitelabel_id === (int)$this->whitelabel['id'] &&
                bccomp($this->transaction->amount_payment, Input::post("amount"), 2) == 0 &&
                Input::post("order_currency") == $currency_code)
            ) {
                status_header(400);
                
                $this->log_error("Amount/currency/whitelabel checks failed.");
                
                exit(_("Bad request! Please contact us!"));
            }

            $ccmethods = Model_Whitelabel_CC_Method::get_cc_methods_for_whitelabel($this->whitelabel);
            $ccmethods_merchant = [];
            foreach ($ccmethods as $ccmethod) {
                $ccmethods_merchant[intval($ccmethod['method'])] = $ccmethod;
            }
            
            // check if emerchant is set
            if (empty($ccmethods_merchant[$emerchant_method_id])) {
                $this->log_error("No eMerchantPay data defined.");
                
                exit(_("Bad request! Please contact us!"));
            }

            if (!((int)$this->transaction->payment_method_type === Helpers_General::PAYMENT_TYPE_CC &&
                empty($this->transaction->whitelabel_payment_method_id) &&
                !empty($this->transaction->whitelabel_cc_method_id) &&
                $this->transaction->whitelabel_cc_method_id == $ccmethods_merchant[$emerchant_method_id]['id'])
            ) {
                status_header(400);
                
                $this->log_error("Bad payment type.");
                
                exit(_("Bad request! Please contact us!"));
            }

            $pdata = unserialize($ccmethods_merchant[$emerchant_method_id]['settings']);
            if (empty($pdata['secretkey']) ||
                empty($pdata['accountid']) ||
                empty($pdata['apikey']) ||
                empty($pdata['endpoint'])
            ) {
                $this->log_error(
                    "eMerchantPay: empty SecretKey, accountid, apikey or endpoint.",
                    $pdata
                );
                
                exit(_("Bad request! Please contact us!"));
            }

            $this->data = unserialize($this->transaction->additional_data);
            $this->out_id = $this->transaction->transaction_out_id;
            $this->ok = false;

            $myWSSDK = new \WSSDK(
                $pdata['accountid'],
                $pdata['apikey'],
                str_replace(["https://", "http://"], "", $pdata['endpoint'])
            );

            Lotto_Settings::getInstance()->set("temp_transaction", $this->transaction);
            Lotto_Settings::getInstance()->set("temp_whitelabel", $this->whitelabel);
            Lotto_Settings::getInstance()->set("temp_data", $this->data);
            Lotto_Settings::getInstance()->set("temp_out_id", $this->out_id);
            Lotto_Settings::getInstance()->set("temp_ok", $this->ok);
            Lotto_Settings::getInstance()->set("temp_wssdk", $myWSSDK);
            Lotto_Settings::getInstance()->set("temp_test", $pdata['test']);

            self::process_notification();
            
            // create handler singleton with secret key
            $handler = \NotificationSDK\NotificationRouter::GetNotificationHandler($pdata['secretkey']);

            // Handle the post request data
            if ($_POST) {
                try {
                    // run the handler with the post data, pass through and debug flags,
                    // This will run the code you have written in the DefaultProcessor function above
                    $handler->handle($_POST, true, false);
                    
                    // respond 200 "ok"
                    $this->transaction = Lotto_Settings::getInstance()->get("temp_transaction");
                    $this->data = Lotto_Settings::getInstance()->get("temp_data");
                    $this->out_id = Lotto_Settings::getInstance()->get("temp_out_id");
                    $this->ok = Lotto_Settings::getInstance()->get("temp_ok");

                    $this->log_success("Confirmation successfully processed.");
                    
                    echo "OK";
                } catch (\Exception $ex) {
                    // Handle your errors here
                    status_header(400);
                    
                    $this->log_error(
                        "Unknown error: " . $ex->getMessage(),
                        [$ex]
                    );

                    exit(_("Bad request! Please contact us!"));
                }
            }
        } catch (Exception $e) {
            status_header(400);
            
            $this->log_error(
                "Unknown error: " . $e->getMessage(),
                [$e]
            );
            
            exit(_("Bad request! Please contact us!"));
        }
    }

    /**
     *
     * @return void
     */
    public function create_payment(): void
    {
        $this->process_form();
    }
    
    /**
     *
     * @param Model_Whitelabel_Transaction $transaction
     * @param string $out_id
     * @param array $data
     * @return void
     */
    public function confirm_payment(
        Model_Whitelabel_Transaction &$transaction = null,
        string &$out_id = null,
        array &$data = []
    ): bool {
        $ok = false;
        
        return $ok;
    }
}
