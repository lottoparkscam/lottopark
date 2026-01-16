<?php

use Fuel\Core\Response;
use Services\PaymentService;
use Services\Logs\FileLoggerService;

/**
 * Class for PayPal payment
 *
 * @author TomekKonop / edited by Michal Kowalczyk
 * @see https://developer.paypal.com/docs/classic/paypal-payments-standard/integration-guide/Appx_websitestandard_htmlvariables
 *
 * Developer: https://developer.paypal.com/developer/accounts/
 * SandBox login: https://www.sandbox.paypal.com/us/home
 *
 *
 */
final class Forms_Wordpress_Payment_Paypal implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method;
    private FileLoggerService $fileLoggerService;

    /**
     * Production Postback URL
     */
    const VERIFY_URI = 'https://ipnpb.paypal.com/cgi-bin/webscr';

    /**
     * Sandbox Postback URL
     */
    const SANDBOX_VERIFY_URI = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';

    /**
     * Response from PayPal indicating validation was successful
     */
    const VALID = 'VERIFIED';

    /**
     * Response from PayPal indicating validation failed
     */
    const INVALID = 'INVALID';
    
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
     * Translation
     * @var Model_Whitelabel_Transaction
     */
    private $transaction = null;

    /**
     * Payment parmas
     * @var null|Model_Whitelabel_Payment_Method
     */
    private $model_whitelabel_payment_method = null;
    
    /**
     * Request array
     * @var array
     */
    protected $request = [];

    /**
     * Payment credentials
     * @var array
     */
    protected $payment_credentials = [];

    /**
     * Test environment
     * @var array
     */
    protected $test_environment = false;

    /**
     * Url to PayPal server, sandbox or production
     *
     * @var string
     */
    protected $paypal_url = '';

    /**
     * Indicates if the sandbox endpoint is used.
     * @var bool
     */
    private $use_sandbox = false;

    /**
     * Indicates if the local certificates are used.
     * @var bool
     */
    private $use_local_certs = false;

    /**
     *
     * @var string
     */
    private $success_url = "";
    
    /**
     *
     * @var string
     */
    private $failure_url = "";
    
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
        
        $this->success_url = lotto_platform_home_url(Helper_Route::ORDER_SUCCESS);
        $this->failure_url = lotto_platform_home_url(Helper_Route::ORDER_FAILURE);
    }
    
    /**
     *
     * @return \Forms_Wordpress_Payment_Paypal
     */
    public function set_paypal_url_by_payment_credentials(): Forms_Wordpress_Payment_Paypal
    {
        if ((int) $this->payment_credentials['paypaltest'] === 1) {
            $this->test_environment = true;
            $this->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        } else {
            $this->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
        }
        
        return $this;
    }
    
    /**
     *
     * @return null|\Forms_Wordpress_Payment_Paypal
     */
    public function set_payment_credentials():? Forms_Wordpress_Payment_Paypal
    {
        if (empty($this->model_whitelabel_payment_method)) {
            $this->log_error("No model_whitelabel_payment_method set.");
            exit(_("Bad request! Please contact us!"));
        }
        
        $this->payment_credentials = unserialize($this->model_whitelabel_payment_method['data']);
        
        return $this;
    }
    
    /**
     * Set Payment Params
     *
     * @param Model_Whitelabel_Payment_Method $model_whitelabel_payment_method
     * @return null|\Forms_Wordpress_Payment_Paypal
     */
    public function set_model_whitelabel_payment_method(
        Model_Whitelabel_Payment_Method $model_whitelabel_payment_method = null
    ): Forms_Wordpress_Payment_Paypal {
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
     */
    public function set_whitelabel($whitelabel)
    {
        $this->whitelabel = $whitelabel;
    }

    /**
     * Get Payment Params
     *
     * @param Model_Whitelabel_Transaction $transaction
     * @return mixed
     */
    public function get_payment_params(Model_Whitelabel_Transaction $transaction)
    {
        $subtype = Model_Whitelabel_Payment_Method::find_by_pk($transaction->whitelabel_payment_method_id);
        return $subtype;
    }

    /**
     * Set Transaction
     *
     * @param Model_Whitelabel_Transaction $transaction
     */
    public function set_transaction(Model_Whitelabel_Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     *
     * @return type
     */
    public function get_request()
    {
        return $this->request;
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
            Helpers_Payment_Method::PAYPAL,
            null,
            $whitelabel_id,
            $transaction_id,
            'PayPal - ' . $message,
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
     * Set up a connection to the API
     *
     * @param string $client_id
     * @param string $client_secret
     * @param bool   $enable_sandbox Sandbox mode toggle, true for test payments
     * @return \PayPal\Rest\ApiContext
     */
    private function get_api_context(
        $client_id,
        $client_secret,
        $enable_sandbox = false
    ) {
        $api_context = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential($client_id, $client_secret)
        );

        $mode = 'live';
        if ($enable_sandbox) {
            $mode = 'sandbox';
        }
        $api_context->setConfig([
            'mode' => $mode
        ]);

        return $api_context;
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
            Helpers_Payment_Method::PAYPAL_URI . "/" .
            $whitelabel_payment_method_id . "/";
        
        return $confirmation_url;
    }
    
    /**
     *
     * @return string
     */
    public function get_result_url(): string
    {
        $whitelabel_payment_method_id = $this->get_whitelabel_payment_method_id();
        if (empty($whitelabel_payment_method_id)) {
            $this->log_error("Lack of whitelabel_payment_method_id!");
            exit(_("Bad request! Please contact us!"));
        }
        
        $result_url = lotto_platform_home_url_without_language() .
            "/order/result/" .
            Helpers_Payment_Method::PAYPAL_URI .
            "/" .
            $whitelabel_payment_method_id .
            "/";
        
        return $result_url;
    }
    
    /**
     * Generates a unique paypal link and redirects to it
     *
     * @return null
     */
    public function process_form()
    {
        $this->set_payment_credentials();
        
        $this->set_paypal_url_by_payment_credentials();
        
        $token = $this->get_prefixed_transaction_token();

        $currency_code = $this->get_payment_currency($this->transaction->payment_currency_id);
        
        $this->payment_credentials['apiurl'] = $this->paypal_url;

        $item_name = sprintf(
            _("Transaction %s"),
            $token
        );
        
        $confirmation_url = $this->get_confirmation_url();
        
        $result_url = $this->get_result_url();
        
        // PayPal settings.
        $paypal_config = [
            'client_id' => $this->payment_credentials['api_client_id_paypal'],
            'client_secret' => $this->payment_credentials['api_client_secret_paypal'],
            'return_url' => $result_url,
            'cancel_url' => $this->failure_url,
            'notify_url' => $confirmation_url
        ];

        $api_context = $this->get_api_context(
            $paypal_config['client_id'],
            $paypal_config['client_secret'],
            $this->test_environment
        );

        $payer = new \PayPal\Api\Payer();
        $payer->setPaymentMethod('paypal');

        $amount_payable = $this->transaction->amount_payment;

        $order_var = new \PayPal\Api\RelatedResources();
        $order_var->setOrder($token);

        $amount = new \PayPal\Api\Amount();
        $amount->setCurrency($currency_code)
            ->setTotal($amount_payable);

        $transaction = new \PayPal\Api\Transaction();
        $transaction->setAmount($amount)
            ->setDescription($item_name)
            ->setInvoiceNumber($token)
            ->setNotifyUrl($paypal_config['notify_url']);

        $redirect_urls = new \PayPal\Api\RedirectUrls();
        $redirect_urls->setReturnUrl($paypal_config['return_url'])
            ->setCancelUrl($paypal_config['cancel_url']);

        $payment = new \PayPal\Api\Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setTransactions([$transaction])
            ->setRedirectUrls($redirect_urls);

        try {
            $payment->create($api_context);
        } catch (Exception $e) {
            $this->log_error(
                'Communication problem with Paypal site.',
                [$e->getMessage()]
            );
            
            Response::redirect($this->failure_url);
        }

        $additional_data = [];
        $additional_data['request'] = $this->request;
        $additional_data['paymentParams'] = $this->model_whitelabel_payment_method->to_array();

        $this->transaction->set([
            'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
            'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id'],
            'transaction_out_id' => $payment->getToken(),
            "additional_data" => serialize($additional_data),
        ]);
        $this->transaction->save();

        $this->log(
            'Redirecting to PayPal',
            Helpers_General::TYPE_INFO,
            ['request' => $payment]
        );

        Response::redirect($payment->getApprovalLink());
        
        exit(1);
    }

    /**
     * Get transaction and check if exist
     *
     * @param $token
     * @return mixed
     */
    protected function get_transaction($token)
    {
        $token_int = intval(substr($token, 3));

        $transaction = Model_Whitelabel_Transaction::find([
            "where" => [
                "whitelabel_id" => $this->whitelabel['id'],
                "token" => $token_int
            ]
        ]);

        if (!isset($transaction[0]['id'])) {
            status_header(400);
            $this->log_error(
                'Transaction with token ' . $token . ' does not exist',
                ['post' => Input::post()]
            );
            return false;
        }

        return $transaction[0];
    }

    /**
     * Check result
     *
     * Return array if success
     *
     * @return array|bool
     */
    public function receive_order()
    {
        $payment_get_id = Input::get('paymentId');
        $payer_get_id = Input::get('PayerID');
        
        if (empty($payment_get_id) || empty($payer_get_id)) {
            Response::redirect(lotto_platform_home_url('/'));
        }

        // PayPal settings
        $paypal_config = [
            'client_id' => $this->payment_credentials['api_client_id_paypal'],
            'client_secret' => $this->payment_credentials['api_client_secret_paypal'],
        ];

        $api_context = $this->get_api_context(
            $paypal_config['client_id'],
            $paypal_config['client_secret'],
            $this->test_environment
        );

        $payment_id = $payment_get_id;
        $payment = \PayPal\Api\Payment::get($payment_id, $api_context);

        $execution = new \PayPal\Api\PaymentExecution();
        $execution->setPayerId($payer_get_id);

        try {
            // Take the payment
            $payment->execute($execution, $api_context);

            try {
                $payment = \PayPal\Api\Payment::get($payment_id, $api_context);

                $data = [
                    'transaction_id' => $payment->getId(),
                    'payment_amount' => $payment->transactions[0]->amount->total,
                    'payment_status' => $payment->getState(),
                ];

                if ($data['payment_status'] === 'approved') {
                    // Payment successfully added, redirect to the payment complete page.
                    Response::redirect($this->success_url);
                } else {
                    // Payment failed
                    $this->log_error(
                        'Something went wrong with transaction - ' . $payment->getId(),
                        [
                            'post' => Input::post(),
                            'get' => Input::get()
                        ]
                    );
                    Response::redirect($this->failure_url);
                }
            } catch (Exception $e) {
                $this->log_error(
                    'Failed to retrieve payment from PayPal - ' . $payment->getId(),
                    [
                        'post' => Input::post(),
                        'get' => Input::get(),
                        'e' => $e
                    ]
                );
                Response::redirect($this->failure_url);
            }
        } catch (Exception $e) {
            $this->log_error(
                'Failed to take paypal payment - ' . $payment->getId(),
                [
                    'post' => Input::post(),
                    'get' => Input::get(),
                    'e' => $e
                ]
            );
            Response::redirect($this->failure_url);
        }
    }

    /**
     * Checks payments results - IPN
     *
     * Return array if success
     *
     * @return
     */
    public function check_payment_result()
    {
        $invoice = Input::post('invoice');
        
        if (empty($invoice)) {
            $this->log_error('Post data (invoice) is missing');
            return false;
        }
        
        $payment_status = Input::post('payment_status');
        
        // Get transaction info and set main variables
        $this->set_whitelabel(Lotto_Settings::getInstance()->get("whitelabel"));
        $transaction = $this->get_transaction($invoice);

        // Check if transaction exist
        if ($transaction === false) {
            return false;
        }

        $this->set_transaction($transaction);

        $payment_params = $this->get_payment_params($transaction);
        
        $this->set_model_whitelabel_payment_method($payment_params);
        
        $this->set_payment_credentials();
        
        $this->set_paypal_url_by_payment_credentials();

        $this->log(
            'Checking PayPal IPN result',
            Helpers_General::TYPE_INFO,
            ['post' => Input::post()]
        );
        
        $verified = $this->verify_IPN();
        
        // Check if paypal transaction is corrent
        if ($verified) {
            // Check if status is "Completed"
            if ($payment_status !== 'Completed') {
                $this->log_error(
                    'Payment status is not Completed. (' . $payment_status . ')',
                    [
                        'post' => Input::post()
                    ]
                );
                $transaction_status = Helpers_General::STATUS_TRANSACTION_ERROR;
                if ($payment_status === 'Pending') {
                    $transaction_status = Helpers_General::STATUS_TRANSACTION_PENDING;
                }
                $transaction->set([
                    'status' => $transaction_status,
                ]);
                $transaction->save();

                return false;
            }

            $amount_paypal = Input::post('mc_gross');
            $txn_id = Input::post('txn_id');

            $amount = $transaction->amount_payment;
            
            $transaction_additional_data = unserialize($transaction->additional_data);
            
            // Check if the amount is right
            if (round($amount_paypal, 2) == $amount) {
                // SUCCESS
                $this->log_success('Transaction succeeded  (' . $payment_status . ')', Input::post());
                
                return [
                    'transaction' => $transaction,
                    'out_id' => $txn_id,
                    'data' => $transaction_additional_data + [
                        'result' => Input::post(),
                        'server' => $_SERVER
                        ]
                ];
            } else {
                // WRONG AMOUNT
                $this->log_error(
                    'Transaction failed - Amounts are not the same',
                    [
                        'post' => Input::post(),
                        'amount_transaction' => $amount,
                        'amount_paypal' => $amount_paypal
                    ]
                );
                $transaction->set([
                    'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                ]);
                $transaction->save();

                return false;
            }
        } else {
            // Payment failed
            $this->log_error(
                "Transaction can't be verified",
                [
                    'post' => Input::post(),
                    'verified_array' => $verified
                ]
            );

            status_header(400);

            return false;
        }
    }

    /**
     * Sets curl to use php curl's built in certs (may be required in some
     * environments).
     * @return void
     */
    public function use_PHP_certs()
    {
        $this->use_local_certs = false;
    }

    /**
     * Determine endpoint to post the verification data to.
     *
     * @return string
     */
    public function get_paypal_uri()
    {
        if ($this->test_environment) {
            return self::SANDBOX_VERIFY_URI;
        } else {
            return self::VERIFY_URI;
        }
    }

    /**
     * Verification Function
     * Sends the incoming post data back to PayPal using the cURL library.
     *
     * @return bool
     */
    public function verify_IPN()
    {
        if (count($_POST) === 0) {
            throw new Exception("Missing POST Data");
        }

        $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $my_post = [];
        foreach ($raw_post_array as $keyval) {
            $keyval = explode('=', $keyval);
            if (count($keyval) == 2) {
                // Since we do not want the plus in the datetime string
                // to be encoded to a space, we manually encode it.
                if ($keyval[0] === 'payment_date') {
                    if (substr_count($keyval[1], '+') === 1) {
                        $keyval[1] = str_replace('+', '%2B', $keyval[1]);
                    }
                }
                $my_post[$keyval[0]] = urldecode($keyval[1]);
            }
        }

        // Build the body of the verification post request,
        // adding the _notify-validate command.
        $req = 'cmd=_notify-validate';
        $get_magic_quotes_exists = false;
        if (function_exists('get_magic_quotes_gpc')) {
            $get_magic_quotes_exists = true;
        }
        foreach ($my_post as $key => $value) {
            if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
                $value = urlencode(stripslashes($value));
            } else {
                $value = urlencode($value);
            }
            $req .= "&$key=$value";
        }

        // Post the data back to PayPal, using curl. Throw exceptions if errors occur.
        $ch = curl_init($this->get_paypal_uri());
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        // This is often required if the server is missing a global cert bundle, or is using an outdated one.
        if ($this->use_local_certs) {
            curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . "/cert/cacert.pem");
        }
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: PHP-IPN-Verification-Script',
            'Connection: Close',
        ]);

        $res = curl_exec($ch);
        if (! ($res)) {
            $errno = curl_errno($ch);
            $errstr = curl_error($ch);
            curl_close($ch);

            $this->log_error('Verify cURL error [' . $errno . '] ' . $errstr);

            return false;
        }

        $info = curl_getinfo($ch);
        $http_code = $info['http_code'];
        if ($http_code != 200) {
            $this->log_error('Verify responded with http code ' . $http_code);

            return false;
        }

        curl_close($ch);

        // Check if PayPal verifies the IPN data, and if so, return true.
        if ($res == self::VALID) {
            return true;
        } else {
            $this->log_error('Payment failed - Code: 2 ');

            return false;
        }
    }

    /**
     *
     * @return void
     */
    public function create_payment(): void
    {
        echo $this->process_form();
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
