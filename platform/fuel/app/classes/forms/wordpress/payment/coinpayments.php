<?php

use Fuel\Core\Response;
use Services\PaymentService;
use Services\Logs\FileLoggerService;


final class Forms_Wordpress_Payment_Coinpayments implements Forms_Wordpress_Payment_Process
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
    private $user = [];
    
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
     * @param array $user
     */
    public function set_user($user)
    {
        $this->user = $user;
    }

    /**
     *
     * @param Model_Whitelabel_Transaction $transaction
     */
    public function set_transaction(Model_Whitelabel_Transaction $transaction)
    {
        $this->transaction = $transaction;
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
        
        $whitelabel_payment_method_id = $this->get_whitelabel_payment_method_id();
        
        Model_Payment_Log::add_log(
            $type,
            Helpers_General::PAYMENT_TYPE_OTHER,
            Helpers_Payment_Method::COINPAYMENTS,
            null,
            $whitelabel_id,
            $transaction_id,
            $message,
            $data,
            $whitelabel_payment_method_id
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
     * @param Model_Whitelabel_Payment_Method $model_whitelabel_payment_method
     * @return \Forms_Wordpress_Payment_Coinpayments
     */
    public function set_model_whitelabel_payment_method(
        Model_Whitelabel_Payment_Method $model_whitelabel_payment_method = null
    ): Forms_Wordpress_Payment_Coinpayments {
        if (empty($model_whitelabel_payment_method)) {
            $this->log_error("No model_whitelabel_payment_method set.");
            
            exit(_("Bad request! Please contact us!"));
        }
        
        $this->model_whitelabel_payment_method = $model_whitelabel_payment_method;
        
        return $this;
    }
    
    /**
     *
     * @return Model_Whitelabel_Transaction
     */
    public function get_transaction(): Model_Whitelabel_Transaction
    {
        return $this->transaction;
    }

    /**
     *
     * @return array
     */
    public function get_data(): array
    {
        return $this->data;
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
     * @return null|string
     */
    public function get_out_id():? string
    {
        return $this->out_id;
    }
    
    /**
     *
     * @return Validation object
     */
    public function get_prepared_form(): Validation
    {
        $val = Validation::forge("coinpayments");
                        
        return $val;
    }
    
    /**
     *
     * @param string $redirect_url Default is empty so it will be exited in the case of no method found
     * @return null|\Model_Whitelabel_Payment_Method
     */
    public function get_model_whitelabel_payment_method(
        string $redirect_url = ""
    ):? Model_Whitelabel_Payment_Method {
        $whitelabel_payment_method_id = (int)$this->transaction->whitelabel_payment_method_id;
        $this->model_whitelabel_payment_method = Model_Whitelabel_Payment_Method::find_by_pk($whitelabel_payment_method_id);
            
        if (!($this->model_whitelabel_payment_method !== null &&
            (int)$this->model_whitelabel_payment_method->whitelabel_id === (int)$this->transaction->whitelabel_id &&
            (int)$this->model_whitelabel_payment_method->payment_method_id === Helpers_Payment_Method::COINPAYMENTS)
        ) {
            status_header(400);
            $this->log_error("Bad payment method.");
            
            if (empty($redirect_url)) {
                exit(_("IPN Error: Bad request! Please contact us!"));
            } else {
                Response::redirect($redirect_url);
            }
        }
        
        return $this->model_whitelabel_payment_method;
    }
    
    /**
     *
     * @param array $request
     */
    private function userdata(&$request): void
    {
        if (!empty($this->user['name'])) {
            $request['first_name'] = mb_substr($this->user['name'], 0, 32);
        }
        if (!empty($this->user['surname'])) {
            $request['last_name'] = mb_substr($this->user['surname'], 0, 32);
        }
        if (!empty($this->user['address_1'])) {
            $request['address_1'] = mb_substr($this->user['address_1'], 0, 128);
        }
        if (!empty($this->user['address_2'])) {
            $request['address_2'] = mb_substr($this->user['address_2'], 0, 128);
        }
        if (!empty($this->user['city'])) {
            $request['city'] = mb_substr($this->user['city'], 0, 64);
        }
        if (!empty($this->user['state'])) {
            $request['state'] = mb_substr(Lotto_View::get_region_name($this->user['state'], false), 0, 64);
        }
        if (!empty($this->user['zip'])) {
            $request['zip'] = mb_substr($this->user['zip'], 0, 32);
        }
        if (!empty($this->user['country'])) {
            $request['country'] = $this->user['country'];
        }
        if (!empty($this->user['phone']) && !empty($this->user['phone_country'])) {
            $request['phone'] = mb_substr($this->user['phone'], 0, 32);
        }
    }
    
    /**
     *
     * @return string
     */
    public function get_confirmation_url(): string
    {
        $whitelabel_payment_method_id = $this->get_whitelabel_payment_method_id();
        if (empty($whitelabel_payment_method_id)) {
            $this->log_error("Lack of whitelabel_payment_method_id!");
            exit(_("Bad request! Please contact us!"));
        }

        /** @var PaymentService $paymentService */
        $paymentService = Container::get(PaymentService::class);

        $confirmation_url = $paymentService->getPaymentConfirmationBaseUrl() . Helper_Route::ORDER_CONFIRM .
            Helpers_Payment_Method::COINPAYMENTS_URI . '/' .
            $whitelabel_payment_method_id . '/';
        
        return $confirmation_url;
    }
    
    /**
     *
     */
    public function process_form(): void
    {
        if ((int)$this->transaction->whitelabel_id !== (int)$this->whitelabel['id'] ||
            (int)$this->transaction->whitelabel_user_id !== (int)$this->user['id']
        ) {
            $this->log_error("Bad request.");
            
            exit(_("Bad request! Please contact us!"));
        }
        
        $payment_params = unserialize($this->model_whitelabel_payment_method['data']);
        
        if (empty($payment_params['merchant_id']) || empty($payment_params['ipn_secret'])) {
            $this->transaction->set([
                'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
                'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
            ]);
            $this->transaction->save();
            
            $this->log_error("Empty Merchant ID or IPN Secret.");
            
            exit(_("Bad request! Please contact us!"));
        }

        $amount = $this->transaction->amount_payment;
        
        $transaction_text = $this->get_prefixed_transaction_token();
        
        $item_name = sprintf(_("Transaction %s"), $transaction_text);
        
        $currency_code = $this->get_payment_currency($this->transaction->payment_currency_id);
        
        $success_url = lotto_platform_home_url(Helper_Route::ORDER_SUCCESS);
        $failure_url = lotto_platform_home_url(Helper_Route::ORDER_FAILURE);
        
        $confirmation_url = $this->get_confirmation_url();
        
        $request = [
            'cmd' => "_pay_simple",
            'reset' => 1,
            'merchant' => $payment_params['merchant_id'],
            'currency' => $currency_code,
            'amountf' => $amount,
            'item_name' => $item_name,
            'invoice' => $transaction_text,
            'want_shipping' => 0,
            'ipn_url' => $confirmation_url,
            'success_url' => $success_url,
            'cancel_url' => $failure_url,
            'email' => substr($this->user['email'], 0, 128),
        ];

        $this->userdata($request);

        $coinpayments = View::forge("wordpress/payment/coinpayments");
        $coinpayments->set("whitelabel", $this->whitelabel);
        $coinpayments->set("transaction", $this->transaction);
        $coinpayments->set("pdata", $payment_params);
        $coinpayments->set("user", $this->user);
        $coinpayments->set("request", $request);
        
        $this->transaction->set([
            'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
            'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
        ]);
        $this->transaction->save();
        
        $this->log_success("Redirecting to CoinPayments.", $request);
        
        ob_clean();
        echo $coinpayments;
    }

    /**
     *
     */
    private function prepare_data(): void
    {
        $this->out_id = Input::post('txn_id');
        $this->data['ipn_version'] = Input::post("ipn_version");
        $this->data['ipn_type'] = Input::post("ipn_type");
        $this->data['ipn_mode'] = Input::post("ipn_mode");
        $this->data['ipn_id'] = Input::post("ipn_id");
        $this->data['merchant'] = Input::post("merchant");
        $this->data['first_name'] = Input::post("first_name");
        $this->data['last_name'] = Input::post("last_name");
        if (!empty(Input::post("company"))) {
            $this->data['company'] = Input::post("company");
        }
        $this->data['email'] = Input::post("email");

        if (!empty(Input::post("address_1"))) {
            $this->data['address_1'] = Input::post("address_1");
        }
        if (!empty(Input::post("address_2"))) {
            $this->data['address_2'] = Input::post("address_2");
        }
        if (!empty(Input::post("city"))) {
            $this->data['city'] = Input::post("city");
        }
        if (!empty(Input::post("state"))) {
            $this->data['state'] = Input::post("state");
        }
        if (!empty(Input::post("zip"))) {
            $this->data['zip'] = Input::post("zip");
        }
        if (!empty(Input::post("country"))) {
            $this->data['country'] = Input::post("country");
        }
        if (!empty(Input::post("country_name"))) {
            $this->data['country_name'] = Input::post("country_name");
        }
        if (!empty(Input::post("phone"))) {
            $this->data['phone'] = Input::post("phone");
        }
        if (!empty(Input::post("send_tx"))) {
            $this->data['send_tx'] = Input::post("send_tx");
        }

        if (!empty(Input::post("received_amount"))) {
            $this->data['received_amount'] = Input::post("received_amount");
        }

        if (!empty(Input::post("received_confirms"))) {
            $this->data['received_confirms'] = Input::post("received_confirms");
        }
        
        $this->data['subtotal'] = Input::post("subtotal");
        $this->data['fee'] = Input::post("fee");
        $this->data['tax'] = Input::post("tax");
        $this->data['shipping'] = Input::post("shipping");
        $this->data['net'] = Input::post("net");
        $this->data['item_amount'] = Input::post("item_amount");

        $this->data['item_name'] = Input::post('item_name');
        $this->data['invoice'] = Input::post('invoice');
        $this->data['amount1'] = floatval(Input::post('amount1'));
        $this->data['amount2'] = floatval(Input::post('amount2'));
        $this->data['currency1'] = Input::post('currency1');
        $this->data['currency2'] = Input::post('currency2');
        $this->data['status'] = intval(Input::post('status'));
        $this->data['status_text'] = Input::post('status_text');
    }
    
    /**
     *
     * @return void
     */
    public function process_confirmation(): void
    {
        try {
            $this->log_info(
                "Received CoinPayments confirmation.",
                Input::post()
            );

            if (empty(Input::post("invoice"))) {
                status_header(400);
                
                $this->log_error("Invoice not found in the POST data.");
                
                exit(_("IPN Error: Bad request! Please contact us!"));
            }
            
            $transaction_token = intval(substr(Input::post('invoice'), 3));
            
            $transaction_temp = Model_Whitelabel_Transaction::find([
                "where" => [
                    "whitelabel_id" => $this->whitelabel['id'],
                    "token" => $transaction_token
                ]
            ]);
            
            if ($transaction_temp === null || count($transaction_temp) == 0) {
                status_header(400);
                
                $this->log_error("Couldn't find transaction.");
                
                exit(_("IPN Error: Bad request! Please contact us!"));
            }
            
            $this->transaction = $transaction_temp[0];
            
            if (!((int)$this->transaction->payment_method_type === Helpers_General::PAYMENT_TYPE_OTHER &&
                !empty($this->transaction->whitelabel_payment_method_id))
            ) {
                status_header(400);
                
                $this->log_error("Bad payment type.");
                
                exit(_("IPN Error: Bad request! Please contact us!"));
            }
            
            $this->get_model_whitelabel_payment_method();
            
            $pay_data = unserialize($this->model_whitelabel_payment_method->data);

            if (empty($pay_data['merchant_id']) || empty($pay_data['ipn_secret'])) {
                status_header(400);
                
                $this->log_error("Empty Merchant ID or IPN Secret.");
                
                exit(_("IPN Error: Bad request! Please contact us!"));
            }

            $cp_merchant_id = $pay_data['merchant_id'];
            $cp_ipn_secret = $pay_data['ipn_secret'];

            if (empty(Input::post('ipn_mode')) || Input::post('ipn_mode') != 'hmac') {
                status_header(400);
                
                $this->log_error("IPN Mode is not HMAC.");
                
                exit(_("IPN Error: IPN Mode is not HMAC"));
            }
            
            if (!isset($_SERVER['HTTP_HMAC']) || empty($_SERVER['HTTP_HMAC'])) {
                status_header(400);
                
                $this->log_error("No HMAC signature sent.");
                
                exit(_("IPN Error: No HMAC signature sent."));
            }
            
            $request = file_get_contents('php://input');
            
            if ($request === false || empty($request)) {
                status_header(400);
                
                $this->log_error("Error reading POST data.");
                
                exit(_("IPN Error: Error reading POST data."));
            }

            if (empty(Input::post('merchant')) ||
                Input::post('merchant') != trim($cp_merchant_id)
            ) {
                status_header(400);
                
                $this->log_error("No or incorrect Merchant ID passed.");
                
                exit(_("IPN Error: No or incorrect Merchant ID passed."));
            }

            $hmac = hash_hmac("sha512", $request, trim($cp_ipn_secret));
            
            if (!hash_equals($hmac, $_SERVER['HTTP_HMAC'])) {
                status_header(400);
                
                $this->log_error("HMAC signature does not match.");
                
                exit(_("IPN Error: HMAC signature does not match"));
            }

            // HMAC Signature verified at this point, load some variables.

            $this->prepare_data();

            $currency_code = $this->get_payment_currency($this->transaction->payment_currency_id);
            
            // Check the original currency to make sure the buyer didn't change it.
            if ((string)$this->data['currency1'] !== (string)$currency_code) {
                $this->transaction->set([
                    'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                    'transaction_out_id' => $this->out_id,
                    'additional_data' => serialize($this->data)
                ]);
                $this->transaction->save();

                status_header(400);
                
                $this->log_error("Original currency mismatch.");
                
                exit(_("IPN Error: Original currency mismatch"));
            }

            // Check amount against order total
            if (round($this->data['amount1'], 2) < $this->transaction->amount_payment) {
                $this->transaction->set([
                    'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                    'transaction_out_id' => $this->out_id,
                    'additional_data' => serialize($this->data)
                ]);
                $this->transaction->save();

                status_header(400);
                
                $this->log_error("Amount is less than order total!");
                
                exit(_("IPN Error: Amount is less than order total!"));
            }

            if ($this->data['status'] >= 100 || $this->data['status'] == 2) {
                // payment is complete or queued for nightly payout, success
                $this->log_success("Payment processed successfully!");
                
                if ((string)$this->data['currency2'] !== "LTCT" ||
                    \Fuel::$env == \Fuel::STAGING
                ) {
                    $this->ok = true;
                } else {
                    $this->transaction->set([
                        'transaction_out_id' => $this->out_id,
                        'additional_data' => serialize($this->data)
                    ]);
                    $this->transaction->save();
                    
                    $this->log_warning("Received payment with LTCT currency. Not accepting.");
                }
            } elseif ($this->data['status'] < 0) {
                // payment error, this is usually final but payments will sometimes
                // be reopened if there was no exchange rate conversion or with seller consent

                $this->transaction->set([
                    'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                    'transaction_out_id' => $this->out_id,
                    'additional_data' => serialize($this->data)
                ]);
                $this->transaction->save();
                
                $this->log_error("Received confirmation with failure status!");
            } else {
                //payment is pending, you can optionally add a note to the order page
                $this->transaction->set([
                    'transaction_out_id' => $this->out_id,
                    'additional_data' => serialize($this->data)
                ]);
                $this->transaction->save();
                
                $this->log_info("Received confirmation with pending status!");
            }
            echo "IPN_OK";
        } catch (Exception $e) {
            status_header(400);

            $this->log_error(
                "Unknown error: " . json_encode($e->getMessage()),
                [json_encode($e)]
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
        exit();
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
        $this->process_confirmation();
                
        $transaction = $this->get_transaction();
        $data = $this->get_data();
        $out_id = $this->get_out_id();
        $ok = $this->get_ok();
                
        return $ok;
    }
}
