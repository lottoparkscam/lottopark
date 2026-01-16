<?php

require_once APPPATH . "vendor/paysafecard/PaymentClass.php";

use Fuel\Core\Response;
use Services\PaymentService;
use Services\Logs\FileLoggerService;

final class Forms_Wordpress_Payment_Paysafecard implements Forms_Wordpress_Payment_Process
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
     * @var array
     */
    private $special_errors = [
        2017
    ];
    
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
            Helpers_Payment_Method::PAYSAFECARD,
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
     * @return \Forms_Wordpress_Payment_Paysafecard
     */
    public function set_model_whitelabel_payment_method(
        Model_Whitelabel_Payment_Method $model_whitelabel_payment_method = null
    ): Forms_Wordpress_Payment_Paysafecard {
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
        $val = Validation::forge("paysafecard");
                                
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
            (int)$this->model_whitelabel_payment_method->payment_method_id === Helpers_Payment_Method::PAYSAFECARD)
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
            Helpers_Payment_Method::PAYSAFECARD_URI . '/' .
            $whitelabel_payment_method_id . '/' .
            '/?payment_id={payment_id}';
        
        return $confirmation_url;
    }
    
    /**
     *
     */
    public function process_form()
    {
        if ($this->transaction->whitelabel_id != $this->whitelabel['id'] ||
            $this->transaction->whitelabel_user_id != $this->user['id']
        ) {
            $this->log_error("Bad request.");
            
            exit(_("Bad request! Please contact us!"));
        }
        
        $payment_params = unserialize($this->model_whitelabel_payment_method['data']);
        
        if (empty($payment_params['api_key']) || !isset($payment_params['test'])) {
            $this->transaction->set([
                'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
                'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
            ]);
            $this->transaction->save();
            
            $this->log_error("Empty Api Key or Test flag.");
            
            exit(_("Bad request! Please contact us!"));
        }

        $correlation_id = $this->get_prefixed_transaction_token();

        $currency_code = $this->get_payment_currency($this->transaction->payment_currency_id);
        
        $pscpayment = new PaysafecardPaymentController(
            $payment_params['api_key'],
            $payment_params['test'] == 1 ? 'TEST' : 'PRODUCTION'
        );

        $amount = $this->transaction->amount_payment;
        
        $customer_id = $this->get_prefixed_user_token($this->user);
        
        $customer_ip = Lotto_Security::get_IP();

        $success_url = lotto_platform_home_url(Helper_Route::ORDER_SUCCESS);
        $failure_url = lotto_platform_home_url(Helper_Route::ORDER_FAILURE);
        
        $confirmation_url = $this->get_confirmation_url();

        $response = $pscpayment->createPayment(
            $amount,
            $currency_code,
            $customer_id,
            $customer_ip,
            $success_url,
            $failure_url,
            $confirmation_url,
            $correlation_id
        );

        if ($response == false) {
            $error = $pscpayment->getError();
            $this->transaction->set([
                'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
                'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
            ]);
            $this->transaction->save();
            
            $this->log_error(
                "paysafecard error.",
                $error
            );
            
            Response::redirect($failure_url);
            exit();
        } elseif (isset($response["object"])) {
            $this->prepare_data($response, false);

            $this->transaction->set([
                "additional_data" => serialize($this->data),
                "transaction_out_id" => $this->out_id,
                'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
                'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
            ]);
            $this->transaction->save();

            $this->log_success("Redirecting to paysafecard.");
                        
            if (isset($response["redirect"])) {
                Response::redirect($response["redirect"]['auth_url']);
                exit();
            }
        }
    }
    
    /**
     *
     * @param array $response
     * @param boolean $is_confirmation
     */
    private function prepare_data($response, $is_confirmation)
    {
        $this->data['object'] = $response['object'];
        $this->out_id = $response['id'];
        $this->data['created'] = $response['created'];
        $this->data['updated'] = $response['updated'];
        $this->data['amount'] = $response['amount'];
        $this->data['currency'] = $response['currency'];
        $this->data['status'] = $response['status'];
        $this->data['type'] = $response['type'];
        $this->data['customer'] = $response['customer'];
        
        if ($is_confirmation) {
            if (isset($response['status_before_expiration'])) {
                $this->data['status_before_expiration'] = $response['status_before_expiration'];
            }
            if (isset($response['card_details'])) {
                $this->data['card_details'] = $response['card_details'];
            }
        }
    }
    
    /**
     *
     */
    public function process_confirmation()
    {
        try {
            $this->log_info(
                "Received paysafecard confirmation.",
                [Input::post(), Input::get()]
            );
            
            $post = Input::post();
            
            if (!isset($post['mtid']) || empty(Input::get("payment_id"))) {
                status_header(400);
                
                $this->log_error(
                    "MTID not found.",
                    Input::post()
                );
                
                exit(_("Bad request! Please contact us!"));
            }
            
            // This line seems to be no needed because in the next line
            // variable of the same name is used
            // $mtid = $post['mtid'];
            $mtid = explode("_", $post['mtid']);
            
            if (!isset($mtid[2]) && Input::get("payment_id") == $mtid[2]) {
                status_header(400);
                
                $this->log_error(
                    "Transaction id not found or do not match.",
                    [$mtid, Input::get("payment_id")]
                );
                
                exit(_("Bad request! Please contact us!"));
            }

            $transaction_token = intval(substr($mtid[2], 3));
            
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
            
            if (!((int)$this->transaction->payment_method_type === Helpers_General::PAYMENT_TYPE_OTHER &&
                !empty($this->transaction->whitelabel_payment_method_id))
            ) {
                status_header(400);
                
                $this->log_error("Bad payment type.");
                
                exit(_("Bad request! Please contact us!"));
            }
            
            $this->get_model_whitelabel_payment_method();
            
            if ($this->transaction->transaction_out_id !== $post['mtid']) {
                status_header(400);
                
                $this->log_error("Bad transaction ID.");
                
                exit(_("Bad request! Please contact us!"));
            }
            
            $pay_data = unserialize($this->model_whitelabel_payment_method->data);

            $environment = 'PRODUCTION';
            if ((int)$pay_data['test'] === 1) {
                $environment = 'TEST';
            }

            $pscpayment = new PaysafecardPaymentController($pay_data['api_key'], $environment);

            $response = $pscpayment->retrievePayment(Input::get("payment_id"));

            $this->log_info(
                "Retrieved paysafecard payment.",
                [$response]
            );

            if ($response == true) {
                if (isset($response["object"])) {
                    if ($response["status"] == "AUTHORIZED") {
                        // capture payment
                        $response = $pscpayment->capturePayment(Input::get("payment_id"));
                        $error = $pscpayment->getError();

                        $this->log_info(
                            "Retrieved paysafecard capture response.",
                            [$response]
                        );

                        if ($response == true) {
                            if (isset($response["object"])) {
                                $this->prepare_data($response, true);
                                
                                if ($response["status"] == "SUCCESS") {
                                    $this->ok = true;
                                    
                                    $this->log_success("Confirmation successfully processed.");
                                } elseif ($response['status'] == "EXPIRED") {
                                    $this->transaction->set([
                                        'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                                        'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
                                        'transaction_out_id' => $this->out_id,
                                        'additional_data' => serialize($this->data)
                                    ]);
                                    $this->transaction->save();

                                    $this->log_error("Received transaction with expired status.");
                                } else {
                                    $this->log_warning("Received transaction with unsupported status.");
                                }
                            }
                            
                            if (isset($error['number']) && in_array($error["number"], $this->special_errors)) {
                                // Before it was $id as a parameter within retrivePayment() function
                                // Transaction already succeeded, logging response only
                                $response = $pscpayment->retrievePayment(Input::get("payment_id"));
                                
                                $this->log_warning(
                                    "Invalid payment state error (2017).",
                                    [$response]
                                );
                            }
                        }
                    }
                }
            } else {
                $error = $pscpayment->getError();
                $this->transaction->set([
                    'status' => Helpers_General::STATUS_TRANSACTION_ERROR
                ]);
                $this->transaction->save();
                
                $this->log_error(
                    "paysafecard error.",
                    $error
                );
                
                status_header(400);
                exit(_("Bad request! Please contact us!"));
            }
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
