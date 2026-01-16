<?php

use Services\Logs\FileLoggerService;

final class Forms_Wordpress_Payment_Stripe extends Forms_Main implements Forms_Wordpress_Payment_Process
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
     * Payment parmas
     * @var null|Model_Whitelabel_Payment_Method
     */
    private $model_whitelabel_payment_method = null;
    
    /**
     * Payment credentials
     * @var array
     */
    private $payment_data = [];
    
    /**
     *
     * @var bool
     */
    private $should_test = false;
    
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
     * @return \Forms_Wordpress_Payment_Stripe|null
     */
    public function set_payment_data():? Forms_Wordpress_Payment_Stripe
    {
        if (empty($this->model_whitelabel_payment_method)) {
            $this->log_error("No model_whitelabel_payment_method set.");
            
            exit(_("Bad request! Please contact us!"));
        }
        
        $this->payment_data = unserialize($this->model_whitelabel_payment_method['data']);
        
        return $this;
    }
    
    /**
     * Set Payment Params and set URL for process payment to live or test server
     *
     * @param Model_Whitelabel_Payment_Method $model_whitelabel_payment_method
     * @return \Forms_Wordpress_Payment_Stripe
     */
    public function set_model_whitelabel_payment_method(
        Model_Whitelabel_Payment_Method $model_whitelabel_payment_method = null
    ): Forms_Wordpress_Payment_Stripe {
        if (empty($model_whitelabel_payment_method)) {
            $this->log_error("No model_whitelabel_payment_method set.");
            
            exit(_("Bad request! Please contact us!"));
        }
        
        $this->model_whitelabel_payment_method = $model_whitelabel_payment_method;
        
        return $this;
    }

    /**
     * Set Whitelabel
     *
     * @param array $whitelabel
     * @return \Forms_Wordpress_Payment_Stripe
     */
    public function set_whitelabel(array $whitelabel): Forms_Wordpress_Payment_Stripe
    {
        if (empty($whitelabel)) {
            status_header(400);
            $this->log_error("Bad request.");
            exit($this->get_exit_text());
        }
        
        $this->whitelabel = $whitelabel;
        
        return $this;
    }

    /**
     *
     * @param string $token
     * @return \Forms_Wordpress_Payment_Stripe
     */
    private function set_whitelabel_by_token(
        string $token
    ): Forms_Wordpress_Payment_Stripe {
        if (empty($token)) {
            status_header(400);
            
            $message = 'Empty token ';
            $this->log_to_error_file($message);
            
            $this->log_error($message);
            return $this;
        }
        
        $whitelabel_prefix = substr($token, 0, 2);
        
        $whitelabel_models = Model_Whitelabel::find([
            "where" => [
                "prefix" => $whitelabel_prefix
            ]
        ]);
       
        if (empty($whitelabel_models) ||
            empty($whitelabel_models[0])
        ) {
            status_header(400);

            $this->log_to_error_file("Empty whitelabel_model.");

            $error_message = "No whitelabel set for payment method of ID: " .
                Helpers_Payment_Method::STRIPE;
            $this->log_error($error_message);
            exit($this->get_exit_text());
        }
        
        $whitelabel_model = $whitelabel_models[0]->to_array();
        
        if (!Helpers_Whitelabel::is_V1($whitelabel_model['type'])) {
            status_header(400);

            $this->log_to_error_file("Whitelabel TYPE != V1.");

            $this->log_error("Wrong type of the whitelabel. Should be type of V1.");
            exit($this->get_exit_text());
        }
        
        $this->whitelabel = $whitelabel_model;
        
        return $this;
    }
    
    /**
     *
     * @param array $user
     * @return \Forms_Wordpress_Payment_Stripe
     */
    public function set_user(array $user): Forms_Wordpress_Payment_Stripe
    {
        $this->user = $user;
        
        return $this;
    }

    /**
     * Set Transaction
     *
     * @param Model_Whitelabel_Transaction $transaction
     * @return \Forms_Wordpress_Payment_Stripe
     */
    public function set_transaction(
        Model_Whitelabel_Transaction $transaction = null
    ): Forms_Wordpress_Payment_Stripe {
        if (empty($transaction)) {
            status_header(400);
            $this->log_error("Bad request.");
            exit($this->get_exit_text());
        }
        
        $this->transaction = $transaction;
        
        return $this;
    }
    
    /**
     * Get transaction and check if exist
     *
     * @param string $token
     * @return null|Model_Whitelabel_Transaction
     */
    private function get_transaction(string $token):? Model_Whitelabel_Transaction
    {
        $token_int = intval(substr($token, 3));
        
        $this->log_to_error_file('Transaction Token: ' . $token);
        $this->log_to_error_file('Transaction Token_int: ' . $token_int);
        $this->log_to_error_file('Transaction Whitelabel_id: ' . $this->whitelabel['id']);
        
        $transactions = Model_Whitelabel_Transaction::find([
            "where" => [
                "whitelabel_id" => $this->whitelabel['id'],
                "token" => $token_int
            ]
        ]);

        if (!isset($transactions[0]['id'])) {
            status_header(400);
            
            $message = 'Transaction with token ' .
                $token .
                ' does not exist. ' .
                'WhitelabelID: ' . $this->whitelabel['id'] . '.';
            $this->log_to_error_file($message);
            
            $this->log_error(
                'Transaction with token ' . $token . ' does not exist',
                ['post' => Input::post()]
            );
            return null;
        }
        
        $transaction = $transactions[0];
        
        return $transaction;
    }
    
    /**
     *
     * @return null|array
     */
    public function get_whitelabel():? array
    {
        return $this->whitelabel;
    }
    
    /**
     * This function is called to check if whitelabel is type of V2
     * only for whitelabel V2 confirmation from the point of wordpress.php controller
     * should be called, whitelabel of the type V1 should be confirmed
     * from admin.php controller part of the system.
     *
     * @return void
     */
    public function check_whitelabel(): void
    {
        $whitelabel = $this->get_whitelabel();
        if (Helpers_Whitelabel::is_V1($whitelabel['type'])) {
            status_header(400);
            $this->log_error("Wrong type of the whitelabel. Should be type of V2.");
            exit($this->get_exit_text());
        }
    }
    
    /**
     * Set whitelabel by pull from whitelabel_payment_methods table
     * by given value of $whitelabel_payment_method_id
     * So this is needed when we want to confirm payment
     * for all whitelabels
     *
     * @param int $whitelabel_payment_method_id
     */
    private function set_settings_by_whitelabel_payment_method_id(
        int $whitelabel_payment_method_id = null
    ): Forms_Wordpress_Payment_Stripe {
        if (empty($whitelabel_payment_method_id)) {
            status_header(400);
            
            $this->log_error("Empty whitelabel_payment_method_id given.");
            exit($this->get_exit_text());
        }
        
        // Here model of whitelabel payment method will be
        // pulled based on $whitelabel_payment_method_id
        // which is given from webhook on Stripe page
        // and in fact is set as only ONE ID of the payment
        // amongs other (rest of V1 should have the same credentials)
        // and these credentials are used for the rest of process
        $this->model_whitelabel_payment_method = Model_Whitelabel_Payment_Method::find_by_pk(
            $whitelabel_payment_method_id
        );
        
        if (empty($this->model_whitelabel_payment_method)) {
            status_header(400);
            
            $this->log_to_error_file("Empty model_whitelabel_payment_method.");
            
            $error_message = "No payment method of ID: " .
                Helpers_Payment_Method::STRIPE;
            $this->log_error($error_message);
            exit($this->get_exit_text());
        }
        
        return $this;
    }
    
    /**
     *
     * @return array|null
     */
    public function get_whitelabel_from_whitelabel_payment_method():? array
    {
        if (empty($this->model_whitelabel_payment_method)) {
            status_header(400);
            
            $this->log_to_error_file("Empty model_whitelabel_payment_method.");
            
            $error_message = "No payment method of ID: " .
                Helpers_Payment_Method::STRIPE;
            $this->log_error($error_message);
            exit($this->get_exit_text());
        }
        
        $whitelabel_id = (int)$this->model_whitelabel_payment_method->whitelabel_id;
        
        $this->whitelabel = Model_Whitelabel::get_single_by_id($whitelabel_id);
        
        if (empty($this->whitelabel)) {
            status_header(400);
            
            $this->log_to_error_file("No whitelabel data found.");
            
            $error_message = "No whitelabel data found. Payment method of ID: " .
                Helpers_Payment_Method::STRIPE;
            $this->log_error($error_message);
            exit($this->get_exit_text());
        }
        
        return $this->whitelabel;
    }
    
    /**
     * Get Model_Whitelabel_Payment_Method for whitelabel
     * and strictly described Stripe int value from Helpers
     * to make possible continue rest of the process
     *
     * @param bool $is_notification
     * @return null|Model_Whitelabel_Payment_Method
     */
    public function get_model_whitelabel_payment_method(
        bool $is_notification = false
    ):? Model_Whitelabel_Payment_Method {
        $whitelabel_payment_methods = Model_Whitelabel_Payment_Method::find([
            'where' => [
                'whitelabel_id' => $this->whitelabel['id'],
                'payment_method_id' => Helpers_Payment_Method::STRIPE
            ]
        ]);
                
        if (empty($whitelabel_payment_methods)) {
            if ($is_notification) {
                status_header(400);
            }
            
            $error_message = "No payment method of ID: " .
                Helpers_Payment_Method::STRIPE .
                " for whitelabel ID: " . $this->whitelabel['id'];
            $this->log_error($error_message);
            exit($this->get_exit_text());
        }
        
        $whitelabel_payment_method = $whitelabel_payment_methods[0];
        
        return $whitelabel_payment_method;
    }
    
    /**
     * Payment value should be send as integer, but some currencies
     * are signed as without decimal ($zero_decimal_value in DB)
     * in that case value after
     * decimal will be cut, but in other cases value will be
     * multiplied by 100
     *
     * @return int
     */
    private function get_prepared_amount(): int
    {
        $prepared_amount = 0;
        $amount_payment = $this->transaction->amount_payment;
        
        $zero_decimal_value = Model_Whitelabel_Payment_Method_Currency::get_zero_decimal_value(
            (int)$this->model_whitelabel_payment_method['id'],
            (int)$this->transaction->payment_currency_id
        );

        if ($zero_decimal_value === 1) {
            $prepared_amount = intval(round($amount_payment, 0));
        } else {
            $prepared_amount = intval(round($amount_payment * 100, 0));
        }
        
        return $prepared_amount;
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
        
        if (!empty($whitelabel_id)) {
            Model_Payment_Log::add_log(
                $type,
                Helpers_General::PAYMENT_TYPE_OTHER,
                Helpers_Payment_Method::STRIPE,
                null,
                $whitelabel_id,
                $transaction_id,
                $message,
                $data,
                $whitelabel_payment_method_id
            );
        }
        
        if ($type === Helpers_General::TYPE_ERROR &&
            (empty($whitelabel_id) ||
                empty($transaction_id))
        ) {
            $this->fileLoggerService->error(
                $message
            );
        }
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
     * Check credentials needed for communication with Stripe
     *
     * @param bool $is_notification
     * @return void
     */
    public function check_credentials(bool $is_notification = false): void
    {
        if ((int)$this->transaction->whitelabel_id !== (int)$this->whitelabel['id'] ||
            (int)$this->transaction->whitelabel_user_id !== (int)$this->user['id']
        ) {
            if ($is_notification) {
                status_header(400);
            }
            $this->log_error("Bad request.");
            exit($this->get_exit_text());
        }
    }

    /**
     * Check settings for merchant needed for communication with Stripe
     *
     * @param bool $is_notification
     * @return void
     */
    private function check_merchant_settings(bool $is_notification = false): void
    {
        $payment_data = $this->payment_data;
        
        if (empty($payment_data['stripe_publishable_key']) ||
            empty($payment_data['stripe_security_key']) ||
            empty($payment_data['stripe_signing_secret'])
        ) {
            $this->log_to_error_file("Wrong credentials for Whitelabel.");
            
            if ($is_notification) {
                status_header(400);
            } else {
                $this->save_transaction(Helpers_General::STATUS_TRANSACTION_ERROR);
            }
            
            $this->log_error("Empty Publishable Key, Security Key or Signing Secret");
            exit($this->get_exit_text());
        }
    }
    
    /**
     *
     * @return string
     */
    public function get_stripe_publishable_key(): string
    {
        $stripe_publishable_key = '';
        if (!empty($this->payment_data['stripe_publishable_key'])) {
            $stripe_publishable_key = (string)$this->payment_data['stripe_publishable_key'];
        }
        
        return $stripe_publishable_key;
    }
    
    /**
     *
     * @return string
     */
    public function get_stripe_security_key(): string
    {
        $stripe_security_key = '';
        if (!empty($this->payment_data['stripe_security_key'])) {
            $stripe_security_key = (string)$this->payment_data['stripe_security_key'];
        }
        
        return $stripe_security_key;
    }
    
    /**
     *
     * @return string
     */
    public function get_currency_code_lowercase(): string
    {
        $currency_code = '';
        if (!empty($this->transaction->payment_currency_id)) {
            $currency_code_from_transaction = $this->get_payment_currency($this->transaction->payment_currency_id);
        
            // For that payment method currency code is send in lowercase
            $currency_code = strtolower($currency_code_from_transaction);
        }
        
        return $currency_code;
    }
    
    /**
     *
     * @param int $status
     * @return void
     */
    public function save_transaction(int $status = null): void
    {
        $set = [
            'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
            'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
        ];
        
        if (!is_null($status)) {
            $set['status'] = $status;
        }
        
        $this->transaction->set($set);
        $this->transaction->save();
    }
    
    /**
     *
     * @return string
     */
    public function get_user_email(): string
    {
        $user_email = '';
        if (!empty($this->user['email'])) {
            $user_email = (string)$this->user['email'];
        }
        
        return $user_email;
    }
    
    /**
     *
     * @return string
     */
    public function get_success_url(): string
    {
        return lotto_platform_home_url(Helper_Route::ORDER_SUCCESS);
    }
    
    /**
     *
     * @return string
     */
    public function get_failure_url(): string
    {
        return lotto_platform_home_url(Helper_Route::ORDER_FAILURE);
    }
    
    /**
     *
     * @return int
     */
    public function get_quantity(): int
    {
        $quantity = 1;
        
        return $quantity;
    }
    
    /**
     *
     * @return string
     */
    public function get_description(): string
    {
        $token = $this->get_prefixed_transaction_token();
        
        $description = sprintf(_("Transaction %s"), $token);
        
        return $description;
    }
    
    /**
     *
     * @return void
     */
    public function set_stripe_session_api_key(): void
    {
        // Those setting is needed to start process of the payment
        // You can find them on https://dashboard.stripe.com/account/apikeys
        $security_key = $this->get_stripe_security_key();

        // I want to leave here that comment below
        // Set your secret key: remember to change this to your live secret key in production
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        \Stripe\Stripe::setApiKey($security_key);
    }
    
    /**
     *
     * @return null|\Stripe\Checkout\Session
     */
    public function stripe_session_create():? \Stripe\Checkout\Session
    {
        $token = $this->get_prefixed_transaction_token();
        
        $success_url = $this->get_success_url();
        $failure_url = $this->get_failure_url();
        
        $user_email = $this->get_user_email();
        $description = $this->get_description();
        $amount = $this->get_prepared_amount();
        $currency_code = $this->get_currency_code_lowercase();
        $quantity = $this->get_quantity();
                
        // Session created by object from Stripe library to start process
        // of checkout see: https://stripe.com/docs/payments/checkout/server
        $session = \Stripe\Checkout\Session::create([
            'customer_email' => $user_email,
            'payment_method_types' => ['card'],
            'line_items' => [[
              'name' => $token,
              'description' => $description,
              'amount' => $amount,
              'currency' => $currency_code,
              'quantity' => $quantity,
            ]],
            'success_url' => $success_url,
            'cancel_url' => $failure_url,
        ]);
        
        if (empty($session)) {
            return null;
        }
        
        return $session;
    }
    
    /**
     *
     * @return string
     */
    public function get_session_user_id(): string
    {
        $session_user_id = '';
        
        if (!empty(Session::get('userId'))) {
            $session_user_id = Session::get('userId');
        }
        
        return $session_user_id;
    }
    
    /**
     *
     * @return string
     */
    public function get_session_vendor_id(): string
    {
        $session_vendor_id = '';
        
        if (!empty(Session::get('vendorId'))) {
            $session_vendor_id = Session::get('vendorId');
        }
        
        return $session_vendor_id;
    }
    
    /**
     *
     * @return bool
     */
    public function check_session_stripe_metadata(): bool
    {
        $stripe_metadata = false;
        
        if (!empty(Session::get('stripe_metadata')) &&
            (int)Session::get('stripe_metadata') === 1
        ) {
            $stripe_metadata = true;
        }
        
        return $stripe_metadata;
    }
    
    /**
     *
     * @return bool
     */
    public function should_collect_metadata(): bool
    {
        $collect_metadata = false;
        
        if (!empty($this->payment_data['stripe_userid_vendorid_metadata']) &&
            (int)$this->payment_data['stripe_userid_vendorid_metadata'] === 1
        ) {
            $collect_metadata = true;
        }
        
        return $collect_metadata;
    }
    
    /**
     *
     * @param \Stripe\Checkout\Session $stripe_session_object
     * @return null|\Stripe\PaymentIntent
     */
    public function stripe_payment_intent_update(
        \Stripe\Checkout\Session $stripe_session_object = null
    ):? \Stripe\PaymentIntent {
        $should_collect_metadata = $this->should_collect_metadata();
        
        if (!$should_collect_metadata) {
            return null;
        }
        
        $session_stripe_metadata = $this->check_session_stripe_metadata();
        
        if (!$session_stripe_metadata) {
            return null;
        }
        
        $user_id = $this->get_session_user_id();
        if (empty($user_id)) {
            $this->log_error("No userId set in Session!");
            return null;
        }
        
        $vendor_id = $this->get_session_vendor_id();
        if (empty($vendor_id)) {
            $this->log_error("No vendorId set in Session!");
            return null;
        }
        
        if (empty($stripe_session_object->payment_intent)) {
            $this->log_error("No payment_intent value set in stripe session object!");
            return null;
        }
        
        $payment_intent_id = $stripe_session_object->payment_intent;
        
        $metadata = [
            'userId' => $user_id,
            'vendorId' => $vendor_id
        ];
        
        $stripe_payment_intent = \Stripe\PaymentIntent::update(
            $payment_intent_id,
            ['metadata' => $metadata]
        );
        
        if (is_null($stripe_payment_intent)) {
            $this->log_error("Problem with update of Stripe PaymentIntent object");
            return null;
        }
        
        return $stripe_payment_intent;
    }
    
    /**
     * Main function to prepare payment on Stripe for user
     *
     * @return void
     */
    public function process_form(): void
    {
        $this->check_credentials();
        
        $this->set_payment_data();
        
        $this->check_merchant_settings();
        
        $this->save_transaction();
        
        $token = $this->get_prefixed_transaction_token();
        
        // Those setting is needed to start process of the payment
        // You can find them on https://dashboard.stripe.com/account/apikeys
        $publishable_key = $this->get_stripe_publishable_key();
        
        $this->set_stripe_session_api_key();
        
        $stripe_session_object = $this->stripe_session_create();
        
        if (is_null($stripe_session_object)) {
            $this->log_error("Problem with creation of Stripe Checkout Session object");
            exit($this->get_exit_text());
        }
        
        $session_id = $stripe_session_object->id;
        
        $stripe_payment_intent = $this->stripe_payment_intent_update($stripe_session_object);
        
        $post_data = [
            "token" => $token,
            "publishable_key" => $publishable_key,
            "stripe_session_id" => $session_id
        ];
        
        $stripe_view = View::forge("wordpress/payment/stripe");
        $stripe_view->set("post_data", $post_data);
        
        $this->log(
            "Redirecting to checkout.stripe.com.",
            Helpers_General::TYPE_INFO,
            $post_data
        );
        ob_clean();
        
        echo $stripe_view;
    }
    
    /**
     *
     * @return void
     */
    public function prepare_settings_for_confirmation(): void
    {
        $this->log("Received Stripe confirmation.");
        
        $this->check_whitelabel();
        
        $model_whitelabel_payment_method = $this->get_model_whitelabel_payment_method(true);
        
        $this->set_model_whitelabel_payment_method($model_whitelabel_payment_method);

        $this->set_payment_data();
        
        $this->log("Checking payment_credentials.");
        
        $this->check_merchant_settings(true);
    }
    
    /**
     *
     * @param int $whitelabel_payment_method_id
     * @return void
     */
    public function prepare_settings_for_confirmation_all_whitelabels(
        int $whitelabel_payment_method_id
    ): void {
        $this->log_to_error_file("whitelabel_payment_method_id: " . $whitelabel_payment_method_id);
        
        $this->set_settings_by_whitelabel_payment_method_id($whitelabel_payment_method_id);
        
        $this->set_payment_data();
        
        $this->get_whitelabel_from_whitelabel_payment_method();
        
        $this->log("Stripe confirmation process begin.");

        $this->check_merchant_settings(true);
    }
    
    /**
     * Main method for checking result
     * When event on the Stripe page was executed (it should be defined before it)
     *
     * Return array if success, if not it false value will be returned
     *
     * @return array|bool
     */
    public function check_payment_result()
    {
        $this->log("Start of process of checking confirmation.");
        
        $security_key = $this->payment_data['stripe_security_key'];
        
        // Set your secret key: remember to change this to your live secret key in production
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        \Stripe\Stripe::setApiKey($security_key);

        // You can find your endpoint's secret in your webhook settings
        $signing_secret = $this->payment_data['stripe_signing_secret'];

        $this->log_to_error_file('Signing Secret: ' . $signing_secret);
        
        $payload = @file_get_contents('php://input');
        
        if (empty($_SERVER['HTTP_STRIPE_SIGNATURE'])) {
            status_header(400);
            
            $error_message = "No HTTP_STRIPE_SIGNATURE value set in SERVER array. " .
                "ID of payment method: " . Helpers_Payment_Method::STRIPE;
            $this->log_error($error_message);
            exit($this->get_exit_text());
        }
        
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            // Constuct Event variable to make possible to continue Notification
            // process
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $signing_secret
            );
            
            // Handle the checkout.session.completed event
            if ($event->type == 'checkout.session.completed') {
                // Session data is returned in the $event->data->object variable
                // Object containing the API resource relevant to the event.
                // See https://stripe.com/docs/api/events/object for more info
                $session = $event->data->object;

                // This variable consist amount, currency, token we sent to Stripe etc.
                // data
                $display_items = $session->display_items[0];

                if (empty($display_items)) {
                    status_header(400);
                    $this->log_error("Empty Data array!");
                    exit($this->get_exit_text());
                }

                if (empty($display_items->custom->name)) {
                    status_header(400);
                    $this->log_error("Empty transaction token data (Name field)!");
                    exit($this->get_exit_text());
                }

                $token = (string)$display_items->custom->name;
                
                if (empty($this->whitelabel)) {
                    $this->set_whitelabel_by_token($token);
                }
                
                $this->transaction = $this->get_transaction($token);

                if (empty($this->transaction)) {
                    status_header(400);
                    $this->log(
                        "Couldn't find transaction, token: " . $token,
                        Helpers_General::TYPE_INFO,
                        ['post' => Input::post()]
                    );
                    exit($this->get_exit_text());
                }

                $out_id = $session->payment_intent;

                $this->log_success(
                    'Stripe transaction succeeded',
                    [
                        'session' => $session,
                        'server' => Input::server()
                    ]
                );

                status_header(200);

                $data = [
                    'result' => $session,
                    'server' => Input::server()
                ];
                
                // Everything is OK
                return [
                    'transaction' => $this->transaction,
                    'out_id' => $out_id,
                    'data' => $data
                ];
            }
        } catch (\UnexpectedValueException $e) {
            status_header(400);
            $this->log_error("Invalid payload!");
            exit($this->get_exit_text());
        } catch (\Stripe\Error\SignatureVerification $e) {
            status_header(400);
            $this->log_error("Invalid signature! Signature: " . $sig_header);
            exit($this->get_exit_text());
        } catch (\Exception $e) {
            status_header(400);
            $this->log_error("Unknown error: " . json_encode($e->getMessage()), json_encode($e));
            exit($this->get_exit_text());
        }

        return false;
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
        $ok = false;
        
        $this->prepare_settings_for_confirmation();
        $result = $this->check_payment_result();

        if (is_array($result)) {
            $ok = true;
            $transaction = $result['transaction'];
            $out_id = $result['out_id'];
            $data = $result['data'];
        }
                
        return $ok;
    }
}
