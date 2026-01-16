<?php

require_once(APPPATH . 'vendor/cubits/Cubits.php');

use Fuel\Core\Response;
use Services\PaymentService;
use Services\Logs\FileLoggerService;

final class Forms_Wordpress_Payment_Cubits implements Forms_Wordpress_Payment_Process
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
            Helpers_Payment_Method::CUBITS,
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
     * @param Model_Whitelabel_Payment_Method $model_whitelabel_payment_method
     * @return \Forms_Wordpress_Payment_Cubits
     */
    public function set_model_whitelabel_payment_method(
        Model_Whitelabel_Payment_Method $model_whitelabel_payment_method = null
    ): Forms_Wordpress_Payment_Cubits {
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
    public function get_prepared_form()
    {
        $val = Validation::forge("cubits");
        
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
            (int)$this->model_whitelabel_payment_method->payment_method_id === Helpers_Payment_Method::CUBITS)
        ) {
            status_header(400);
            
            $this->log_error("Bad payment method.");
            
            if (empty($redirect_url)) {
                exit(_("Bad request! Please contact us!"));
            } else {
                Response::redirect($redirect_url);
            }
        }
        
        return $this->model_whitelabel_payment_method;
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
            Helpers_Payment_Method::CUBITS_URI . '/' .
            $whitelabel_payment_method_id . '/';
        
        return $confirmation_url;
    }
    
    /**
     *
     * @return void
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
        
        if (empty($payment_params['api_key']) || empty($payment_params['api_secret'])) {
            $this->transaction->set([
                'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
                'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
            ]);
            $this->transaction->save();
            
            $this->log_error("Empty API Key or API Secret.");
            
            exit(_("Bad request! Please contact us!"));
        }

        $cubits_url = "https://pay.cubits.com/api/v1/";

        $success_url = lotto_platform_home_url(Helper_Route::ORDER_SUCCESS);
        $failure_url = lotto_platform_home_url(Helper_Route::ORDER_FAILURE);
        
        $confirmation_url = $this->get_confirmation_url();
        
        try {
            Cubits::configure($cubits_url, false);
            
            $cubits = Cubits::withApiKey($payment_params['api_key'], $payment_params['api_secret']);
            
            $transaction_text = $this->get_prefixed_transaction_token();
            
            $params = [
                'reference' => $transaction_text,
                'callback_url' => $confirmation_url,
                'success_url' => $success_url,
                'cancel_url' => $failure_url
            ];
            
            $amount = $this->transaction->amount_payment;
        
            $currency_code = $this->get_payment_currency($this->transaction->payment_currency_id);
            
            $temp = $cubits->createInvoice(
                $transaction_text,
                $amount,
                $currency_code,
                $params
            );

            $this->transaction->set([
                "transaction_out_id" => $temp->id,
                "additional_data" => serialize(
                    [
                        "address" => $temp->address
                    ]
                ),
                'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
                'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
            ]);
            $this->transaction->save();
            
            $this->log_success("Created invoice. Redirecting to payment page.", $temp);
            
            Response::redirect($temp->invoice_url);
        } catch (Exception $e) {
            $this->transaction->set([
                'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
                'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
            ]);
            $this->transaction->save();
            
            $this->log_error(
                "Something went wrong: " . $e->getMessage(),
                [$e]
            );
            
            exit(_("Bad request! Please contact us!"));
        }
    }
    
    /**
     *
     */
    public function process_confirmation()
    {
        $cubits_callback_id = null;
        $cubits_key = null;
        $cubits_signature = null;
        
        try {
            $inputraw = file_get_contents('php://input');
            
            $this->log_info(
                "Received confirmation.",
                ["input" => $inputraw, "headers" => \Input::headers()]
            );
            
            // getallheaders does not work for CGI, thank you fuelphp!
            //foreach (/* getallheaders() */\Input::headers() as $name => $value) {
            foreach (\Input::headers() as $name => $value) {
                switch ($name) {
                    case "X-Cubits-Callback-Id":
                        $cubits_callback_id = $value;
                        break;
                    case "X-Cubits-Key":
                        $cubits_key = $value;
                        break;
                    case "X-Cubits-Signature":
                        $cubits_signature = $value;
                        break;
                }
            }
            
            $input = json_decode($inputraw);

            if (!($cubits_callback_id !== null &&
                $cubits_key !== null &&
                $cubits_signature !== null &&
                $input !== false &&
                !empty($input->reference))
            ) {
                status_header(400);
                
                $this->log_error(
                    "Wrong headers or input or missing input reference.",
                    [
                        "input" => $input,
                        "headers" => \Input::headers()
                    ]
                );
                
                exit(_("Bad request! Please contact us!"));
            }
            
            $transaction_token = intval(substr($input->reference, 3));
            
            $transaction_temp = Model_Whitelabel_Transaction::find([
                "where" => [
                    "whitelabel_id" => $this->whitelabel['id'],
                    "token" => $transaction_token
                ]
            ]);
            
            if ($transaction_temp === null || count($transaction_temp) == 0) {
                status_header(400);
                
                $this->log_error("Couldn't find transaction.");
                
                exit(_("Bad request! Please contact us!"));
            }
            
            $this->transaction = $transaction_temp[0];
            
            $currency_code = $this->get_payment_currency($this->transaction->payment_currency_id);
            
            if (!(round($input->merchant_amount, 2) == $this->transaction->amount_payment &&
                (string)$input->merchant_currency === (string)$currency_code)
            ) {
                status_header(400);
                
                $this->log_error("Bad amount or currency.");
                
                exit(_("Bad request! Please contact us!"));
            }
            
            if (!($this->transaction->payment_method_type == Helpers_General::PAYMENT_TYPE_OTHER &&
                !empty($this->transaction->whitelabel_payment_method_id))
            ) {
                status_header(400);
                
                $this->log_error("Bad payment type.");
                
                exit(_("Bad request! Please contact us!"));
            }
            
            $this->get_model_whitelabel_payment_method();
            
            $pay_data = unserialize($this->model_whitelabel_payment_method->data);
            $key = $pay_data['api_secret'];

            $msg = $cubits_callback_id . hash('sha256', utf8_encode($inputraw), false);
            $signature = hash_hmac("sha512", $msg, $key);
            
            if ($signature != $cubits_signature) {
                status_header(400);
                
                $this->log_error("Signatures do not match.");
                                
                exit(_("Bad request! Please contact us!"));
            }
            
            // I don't know what to do with this statement
            // At this moment it is useless, because 2 lines below $this->data
            // is overwritten
            $this->data = unserialize($this->transaction->additional_data);
            
            $this->out_id = $input->id;
            
            $this->data = [
                "invoice_currency" => $input->invoice_currency,
                "invoice_amount" => $input->invoice_amount,
                "paid_currency" => $input->paid_currency,
                "paid_amount" => $input->paid_amount,
                "pending_currency" => $input->pending_currency,
                "pending_amount" => $input->pending_amount,
                "status" => $input->status,
                "create_time" => $input->create_time,
                "address" => $input->address,
                "merchant_currency" => $input->merchant_currency,
                "merchant_amount" => $input->merchant_amount,
                "share_to_keep_in_btc" => $input->share_to_keep_in_btc
            ];

            switch ($input->status) {
                case "completed":
                case "overpaid":
                    $this->ok = true;
                    
                    if ($input->status == "completed") {
                        $this->log_success(
                            "Confirmation successfully processed with status: " .
                            $input->status
                        );
                    } else {
                        $this->log_warning(
                            "Confirmation successfully processed with status: " .
                            $input->status
                        );
                    }
                    
                    break;
                case "pending":
                    $this->transaction->set([
                        'transaction_out_id' => $this->out_id,
                        'additional_data' => serialize($this->data)
                    ]);
                    $this->transaction->save();

                    $this->log_info("Received confirmation pending status.");
                    
                    break;
                case "aborted":
                case "timeout":
                case "underpaid":
                    $this->transaction->set([
                        'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                        'transaction_out_id' => $this->out_id,
                        'additional_data' => serialize($this->data)
                    ]);
                    $this->transaction->save();

                    $this->log_error("Received confirmation status: " . $input->status);
                    
                    break;
                default:
                    status_header(400);
                    
                    $this->log_error("This error should not appear.");
                    
                    exit(_("Bad request! Please contact us!"));
                    break;
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
        $this->process_confirmation();
        
        $transaction = $this->get_transaction();
        $data = $this->get_data();
        $out_id = $this->get_out_id();
        $ok = $this->get_ok();
        
        return $ok;
    }
}
