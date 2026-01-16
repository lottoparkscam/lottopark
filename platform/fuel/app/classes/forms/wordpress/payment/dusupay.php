<?php

use Services\PaymentService;
use Services\Logs\FileLoggerService;

final class Forms_Wordpress_Payment_Dusupay implements Forms_Wordpress_Payment_Process
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
     * Payment credentials
     * @var array
     */
    private $payment_credentials = [];

    /**
     * Variable for storing proper url of the server of payments feature on DusuPay
     * Could be url of sandbox or live server
     * @var string
     */
    private $process_url = '';
    
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
     * @return null|\Forms_Wordpress_Payment_Dusupay
     */
    public function set_payment_credentials():? Forms_Wordpress_Payment_Dusupay
    {
        if (empty($this->model_whitelabel_payment_method)) {
            $this->log_error("No model_whitelabel_payment_method set.");
            exit(_("Bad request! Please contact us!"));
        }
        
        $this->payment_credentials = unserialize($this->model_whitelabel_payment_method['data']);
        
        return $this;
    }
    
    /**
     *
     * @return \Forms_Wordpress_Payment_Dusupay
     */
    public function set_process_url_by_payment_credentials(): Forms_Wordpress_Payment_Dusupay
    {
        if (isset($this->payment_credentials['dusupay_test']) &&
            $this->payment_credentials['dusupay_test'] == 1
        ) {
            $this->process_url = 'http://sandbox.dusupay.com/';     // Sandbox URL
        } else {
            $this->process_url = 'https://dusupay.com/';            // Live URL
        }
        
        return $this;
    }
    
    /**
     * Set Payment Params and set URL for process payment to live or sandbox server
     *
     * @param Model_Whitelabel_Payment_Method $model_whitelabel_payment_method
     * @return \Forms_Wordpress_Payment_Dusupay
     */
    public function set_model_whitelabel_payment_method(
        Model_Whitelabel_Payment_Method $model_whitelabel_payment_method = null
    ): Forms_Wordpress_Payment_Dusupay {
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
     *
     * @param array $user
     */
    public function set_user($user)
    {
        $this->user = $user;
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
            Helpers_Payment_Method::DUSUPAY,
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
     * Check credentials needed for communication with DusuPay
     */
    private function check_credentials()
    {
        if ($this->transaction->whitelabel_id != $this->whitelabel['id'] ||
            $this->transaction->whitelabel_user_id != $this->user['id']
        ) {
            $this->log_error("Bad request.");
            exit(_("Bad request! Please contact us!"));
        }
    }

    /**
     * Check settings for merchant needed for communication with DusuPay
     *
     * @param bool $is_notification
     */
    private function check_merchant_settings($is_notification = false)
    {
        $pdata = $this->payment_credentials;
        
        if (empty($pdata['merchant_dusupay_id']) ||
            empty($pdata['merchant_dusupay_apikey'])
        ) {
            if ($is_notification) {
                status_header(400);
            } else {
                $this->transaction->set([
                    'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                    'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
                    'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
                ]);

                $this->transaction->save();
            }
            
            $this->log_error("Empty Merchant ID or Merchant Mackey/Salt Key");
            exit(_("Bad request! Please contact us!"));
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
            Helpers_Payment_Method::DUSUPAY_URI . '/' .
            $whitelabel_payment_method_id . '/';
        
        return $confirmation_url;
    }
    
    /**
     * Main function to prepare payment on DusuPay for user
     */
    public function process_form()
    {
        $this->check_credentials();
        
        $this->set_payment_credentials();
        
        $this->set_process_url_by_payment_credentials();
            
        $this->check_merchant_settings();
        
        $this->transaction->set([
            'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
            'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
        ]);
        $this->transaction->save();
        
        $token = $this->get_prefixed_transaction_token();
        
        $currency_code = $this->get_payment_currency($this->transaction->payment_currency_id);
        
        $redirect_url = lotto_platform_home_url(Helper_Route::ORDER_SUCCESS);
        
        $confirmation_url = $this->get_confirmation_url();
        
        $info_text = sprintf(_("Transaction %s"), $token);
        
        $payment_part_url = 'dusu_payments/dusupay';
        $this->process_url .= $payment_part_url;
        
        $post_data = [
            "token" => $token,
            "apiurl" => $this->process_url,
            "dusupay_merchantId" => $this->payment_credentials['merchant_dusupay_id'],
            "dusupay_amount" => $this->transaction->amount_payment,
            "dusupay_currency" => $currency_code,
            "dusupay_itemId" => $token,
            "dusupay_itemName" => $info_text,
            "dusupay_transactionReference" => $token,
            "dusupay_redirectURL" => $redirect_url,
            "dusupay_successURL" => $confirmation_url,
        ];
        
        $dusupay_view = View::forge("wordpress/payment/dusupay");
        $dusupay_view->set("post_data", $post_data);

        $this->log(
            "Redirecting to dusupay.com.",
            Helpers_General::TYPE_INFO,
            $post_data
        );
        ob_clean();
        
        echo $dusupay_view;
    }
    
    /**
     * Check session transaction
     *
     * @return bool
     */
    protected function check_payment_post_data(): bool
    {
        if (Input::post('dusupay_amount') && Input::post('dusupay_currency') &&
            Input::post('dusupay_itemId') && Input::post('dusupay_transactionReference') &&
            Input::post('dusupay_timestamp') && Input::post('hash') &&
            Input::post('dusupay_transactionId') && Input::post('dusupay_transactionStatus')
        ) {
            return true;
        } else {
            $this->log_error(
                'Required POST fields are missing',
                ['post' => Input::post(), 'server' => Input::server()]
            );
            
            return false;
        }
    }
    
    /**
     * Get transaction and check if exist
     *
     * @param $token
     * @return mixed
     */
    private function get_transaction($token)
    {
        $token_int = intval(substr($token, 3));
        $transaction = Model_Whitelabel_Transaction::find([
            "where" => [
                "whitelabel_id" => $this->whitelabel['id'],
                "token" => $token_int
            ]
        ]);

        if (!isset($transaction[0]['id'])) {
            $this->log_error(
                'Transaction with token ' . $token . ' does not exist',
                ['post' => Input::post()]
            );
            return false;
        }
        
        return $transaction[0];
    }
    
    /**
     * Get Payment Params
     *
     * @param Model_Whitelabel_Transaction $transaction
     * @return Model_Whitelabel_Payment_Method
     */
    public function get_model_whitelabel_payment_method(
        Model_Whitelabel_Transaction $transaction
    ):? Model_Whitelabel_Payment_Method {
        $model_whitelabel_payment_method = Model_Whitelabel_Payment_Method::find_by_pk(
            $transaction->whitelabel_payment_method_id
        );
        return $model_whitelabel_payment_method;
    }
    
    /**
     * Check hash given by DusuPay with prepared one based on proper values from POST
     *
     * @return bool
     */
    private function check_hash()
    {
        $dusupay_amount = Input::post('dusupay_amount');
        $dusupay_currency = Input::post('dusupay_currency');
        $dusupay_item_id = Input::post('dusupay_itemId');
        $dusupay_transaction_reference = Input::post('dusupay_transactionReference');
        $timestamp = Input::post('dusupay_timestamp');
        $dusupay_hash = Input::post('hash');
        
        $hash_string = $dusupay_amount .
            $dusupay_currency .
            $dusupay_item_id .
            $dusupay_transaction_reference .
            $timestamp;
        
        $mackey = $this->payment_credentials['merchant_dusupay_apikey'];
        
        $calculated_hash = hash_hmac('sha1', $hash_string, $mackey);
        
        if ($dusupay_hash !== $calculated_hash) {
            return false;
        }
        
        return true;
    }

    /**
     * Main method for checking result
     *
     * Return array if success
     *
     * @return array|bool
     */
    public function check_payment_result()
    {
        try {
            $this->log("Received DusuPay confirmation.");

            if (empty(Input::post())) {
                status_header(400);
                $this->log_error("Empty POST data array!");
                exit(_("Bad request! Please contact us!"));
            }
            
            if (empty(Input::post("dusupay_transactionReference"))) {
                status_header(400);
                
                $this->log_error(
                    "Empty dusupay_transactionReference variable in POST data!",
                    ['post' => Input::post(), 'server' => Input::server()]
                );
                exit(_("Bad request! Please contact us!"));
            }
            
            if (!$this->check_payment_post_data()) {
                status_header(400);
                exit(_("Bad request! Please contact us!"));
            }

            $token = Input::post("dusupay_transactionReference");
            $transaction = $this->get_transaction($token);
            
            if ($transaction === false) {
                status_header(400);
                $this->log(
                    "Couldn't find transaction, token: " . $token,
                    Helpers_General::TYPE_INFO,
                    ['post' => Input::post()]
                );
                exit(_("Bad request! Please contact us!"));
            }
            
            $payment_params = $this->get_model_whitelabel_payment_method($transaction);
            
            $this->set_model_whitelabel_payment_method($payment_params);
            
            $this->set_payment_credentials();
        
            $this->set_process_url_by_payment_credentials();
            
            $this->check_merchant_settings(true);
            
            $dusupay_transaction_id = Input::post('dusupay_transactionId');
            
            $hash = $this->check_hash();
            if (!$hash) {
                status_header(400);
                $this->log_error(
                    'DusuPay Transaction failed - wrong hash',
                    ['response' => Input::post(), 'server' => Input::server()]
                );
                
                $transaction->set([
                    'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                    'transaction_out_id' => $dusupay_transaction_id
                ]);
                $transaction->save();
                exit(_("Bad request! Please contact us!"));
            }
            
            switch (Input::post('dusupay_transactionStatus')) {
                case 'COMPLETE':
                    $this->log_success(
                        'DusuPay transaction succeeded',
                        ['post' => Input::post()]
                    );
                    return [
                        'transaction' => $transaction,
                        'out_id' => $dusupay_transaction_id,
                        'data' => ['result' => Input::post(), 'server' => Input::server()]
                    ];
                    break;
                case 'PENDING':
                    $this->log(
                        'DusuPay transaction pending',
                        Helpers_General::TYPE_INFO,
                        ['post' => Input::post()]
                    );
                    $transaction->set([
                        'status' => Helpers_General::STATUS_TRANSACTION_PENDING,
                        'transaction_out_id' => $dusupay_transaction_id
                    ]);
                    $transaction->save();
                    break;
                case 'FAILED':
                case 'INVALID':
                    $this->log(
                        'DusuPay transaction failed',
                        Helpers_General::TYPE_ERROR,
                        [
                            'post' => Input::post(),
                            'server' => Input::server()
                        ]
                    );
                    $transaction->set([
                        'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                        'transaction_out_id' => $dusupay_transaction_id
                    ]);
                    $transaction->save();
                    // no break
                default:
                    $this->log(
                        'Undefined DusuPay transaction error.',
                        Helpers_General::TYPE_ERROR,
                        [
                            'post' => Input::post(),
                            'server' => Input::server()
                        ]
                    );
                    $transaction->set([
                        'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                        'transaction_out_id' => $dusupay_transaction_id
                    ]);
                    $transaction->save();
                    break;
            }
            
            return false;
        } catch (Exception $e) {
            status_header(400);
            $this->log_error("Unknown error: " . json_encode($e->getMessage()), json_encode($e));
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
