<?php

use Fuel\Core\Response;
use Services\PaymentService;
use Services\Logs\FileLoggerService;

final class Forms_Wordpress_Payment_Tpay implements Forms_Wordpress_Payment_Process
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
    private $ips_permitted = [
        "195.149.229.109",
        "148.251.96.163",
        "178.32.201.77",
        "46.248.167.59",
        "46.29.19.106"
    ];
    
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
     * Payment credentials
     * @var array
     */
    private $payment_credentials = [];
    
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
            Helpers_Payment_Method::TPAYCOM,
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
     * @return Validation object
     */
    public function get_prepared_form()
    {
        $val = Validation::forge("tpay");
                        
        return $val;
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
     * @return \Forms_Wordpress_Payment_Tpay
     */
    public function set_model_whitelabel_payment_method(
        Model_Whitelabel_Payment_Method $model_whitelabel_payment_method = null
    ): Forms_Wordpress_Payment_Tpay {
        if (empty($model_whitelabel_payment_method)) {
            $this->log_error("No model_whitelabel_payment_method set.");
            
            exit(_("Bad request! Please contact us!"));
        }
        
        $this->model_whitelabel_payment_method = $model_whitelabel_payment_method;
        
        return $this;
    }
    
    /**
     *
     * @return null|\Forms_Wordpress_Payment_Tpay
     */
    public function set_payment_credentials():? Forms_Wordpress_Payment_Tpay
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
            (int)$this->model_whitelabel_payment_method->payment_method_id === Helpers_Payment_Method::TPAYCOM)
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
            Helpers_Payment_Method::TPAY_URI . '/' .
            $whitelabel_payment_method_id . '/';
        
        return $confirmation_url;
    }
    
    /**
     *
     * @return array
     */
    public function get_urls(): array
    {
        $wyn_url = $this->get_confirmation_url();

        $pow_url = lotto_platform_home_url(Helper_Route::ORDER_SUCCESS);
        $pow_url_blad = lotto_platform_home_url(Helper_Route::ORDER_FAILURE);
        
        $urls = [
            'wyn' => $wyn_url,
            'pow' => $pow_url,
            'pow_blad' => $pow_url_blad,
        ];
        
        return $urls;
    }
    
    /**
     *
     * @return string
     */
    public function get_description(): string
    {
        $full_token = $this->get_prefixed_transaction_token();
        
        $description = sprintf(_("Transaction %s"), $full_token);
        
        return $description;
    }
    
    /**
     *
     * @return string
     */
    public function get_id_from_payment_data(): string
    {
        $tpay_id = "";
        
        if (!empty($this->payment_credentials['tpay_id'])) {
            $tpay_id = $this->payment_credentials['tpay_id'];
        }
        
        return $tpay_id;
    }
    
    /**
     *
     * @return string
     */
    public function get_security_key(): string
    {
        $tpay_security_key = "";
        
        if (!empty($this->payment_credentials['tpay_security_key'])) {
            $tpay_security_key = $this->payment_credentials['tpay_security_key'];
        }
        
        return $tpay_security_key;
    }
    
    /**
     *
     * @return string
     */
    public function get_md5_hidden(): string
    {
        $tpay_id = $this->get_id_from_payment_data();
        $pln_amount = $this->transaction->amount_payment;
        $full_token = $this->get_prefixed_transaction_token();
        $tpay_security_key = $this->get_security_key();
        
        $md5_hidden_raw = $tpay_id;
        $md5_hidden_raw .= $pln_amount;
        $md5_hidden_raw .= $full_token;
        $md5_hidden_raw .= $tpay_security_key;
        
        $md5_hidden = md5($md5_hidden_raw);
        
        return $md5_hidden;
    }
    
    /**
     *
     * @return string
     */
    public function get_user_email(): string
    {
        $user_email = '';
        if (!empty($this->user['email'])) {
            $user_email = $this->user['email'];
        }
        
        return $user_email;
    }
    
    /**
     *
     * @return string
     */
    public function get_user_name(): string
    {
        $name = '';
        if (!empty($this->user['name'])) {
            $name = $this->user['name'];
            if (!empty($this->user['surname'])) {
                $name .= ' ' . $this->user['surname'];
            }
        }
        
        $user_name = mb_substr($name, 0, 64);
        
        return $user_name;
    }
    
    /**
     *
     * @return string
     */
    public function get_user_address(): string
    {
        $address = '';
        if (!empty($this->user['address_1'])) {
            $address = $this->user['address_1'];
            if (!empty($this->user['address_2'])) {
                $address .= '; ' . $this->user['address_2'];
            }
        }
        
        $user_address = mb_substr($address, 0, 64);
        
        return $user_address;
    }
    
    /**
     *
     * @return string
     */
    public function get_user_city(): string
    {
        $user_city = "";
        
        if (!empty($this->user['city'])) {
            $user_city = mb_substr($this->user['city'], 0, 32);
        }
            
        return $user_city;
    }
    
    /**
     *
     * @return string
     */
    public function get_user_zip(): string
    {
        $user_zip = "";
        
        if (!empty($this->user['zip'])) {
            $user_zip = mb_substr($this->user['zip'], 0, 10);
        }
            
        return $user_zip;
    }
    
    /**
     *
     * @return string
     */
    public function get_user_country(): string
    {
        $user_country = "";
        
        if (!empty($this->user['country'])) {
            $user_country = $this->user['country'];
        }
            
        return $user_country;
    }
    
    /**
     *
     * @return string
     */
    public function get_user_phone(): string
    {
        $user_phone = "";
        
        if (!empty($this->user['phone']) && !empty($this->user['phone_country'])) {
            $user_phone = mb_substr($this->user['phone'], 0, 16);
        }
            
        return $user_phone;
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
        
        $this->set_payment_credentials();
        
        if (empty($this->payment_credentials['tpay_id']) ||
            empty($this->payment_credentials['tpay_security_key'])
        ) {
            $this->transaction->set([
                'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
                'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
            ]);
            $this->transaction->save();
            
            $this->log_error("Empty TPAY id or TPAY security key.");
            
            exit(_("Bad request! Please contact us!"));
        }

        $amount = $this->transaction->amount_payment;
        
        $language = str_replace(['pl', 'en', 'de'], ['PL', 'EN', 'DE'], $this->code);
        
        $urls = $this->get_urls();
        
        $description = $this->get_description();
        
        $tpay_id = $this->get_id_from_payment_data();
        
        $full_token = $this->get_prefixed_transaction_token();
        
        $md5_hidden = $this->get_md5_hidden();
        
        $user_email = $this->get_user_email();
        $user_name = $this->get_user_name();
        $user_address = $this->get_user_address();
        $user_city = $this->get_user_city();
        $user_zip = $this->get_user_zip();
        $user_country = $this->get_user_country();
        $user_phone = $this->get_user_phone();
        
        $tpay = View::forge("wordpress/payment/tpay");
        
        $tpay->set("urls", $urls);
        $tpay->set("whitelabel", $this->whitelabel);
        $tpay->set("transaction", $this->transaction);
        $tpay->set("description", $description);
        $tpay->set("tpay_id", $tpay_id);
        $tpay->set("full_token", $full_token);
        
        $tpay->set("md5_hidden", $md5_hidden);
        
        $tpay->set("user_email", $user_email);
        $tpay->set("user_name", $user_name);
        $tpay->set("user_address", $user_address);
        $tpay->set("user_city", $user_city);
        $tpay->set("user_zip", $user_zip);
        $tpay->set("user_country", $user_country);
        $tpay->set("user_phone", $user_phone);
        
        $tpay->set("pln_amount", $amount);
        $tpay->set("lang", $language);
        
        $this->transaction->set([
            "additional_data" => serialize(["tr_amount" => $amount]),
            'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
            'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
        ]);
        $this->transaction->save();
        
        $this->log_success("Redirecting to TPAY.com.");
        
        ob_clean();
        echo $tpay;
        exit();
    }
    
    /**
     *
     */
    public function process_confirmation()
    {
        try {
            $this->log_info(
                "Received confirmation.",
                Input::post()
            );
            
            if (!in_array($this->ip, $this->ips_permitted) || empty(Input::post())) {
                status_header(400);
                
                $this->log_error(
                    "Bad IP or empty POST.",
                    [$this->ip]
                );
                
                exit(_("Bad request! Please contact us!"));
            }
            
            $status = Input::post('tr_status');
            $err = Input::post('tr_error');
            $crc = Input::post('tr_crc');
            $tid = $this->out_id = Input::post('tr_id');
            $date = Input::post('tr_date');
            $amount = Input::post('tr_amount');
            $email = Input::post('tr_email');
            $merchant_id = Input::post("id");

            $transaction_token = intval(substr(Input::post("tr_crc"), 3));
            
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
            
            $pay_data = unserialize($this->model_whitelabel_payment_method->data);

            $tpay_id = $pay_data['tpay_id'];
            $tpay_key = $pay_data['tpay_security_key'];
            
            $md5_sum = md5($tpay_id . $tid . $amount . $crc . $tpay_key);

            if ($md5_sum != Input::post('md5sum')) {
                status_header(400);
                
                $this->log_error("Bad md5sum.");
                
                exit(_("Bad request! Please contact us!"));
            }
            
            $this->data = [
                "tr_date" => $date,
                "tr_amount" => $amount,
                "tr_email" => $email,
                "tr_paid" => Input::post("tr_paid"),
                "id" => $merchant_id
            ];
            
            if (Input::post("tr_desc") !== null) {
                $this->data["tr_desc"] = Input::post("tr_desc");
            }
            
            if (Input::post("tr_error") !== null &&
                Input::post("tr_error") !== "none"
            ) {
                $this->data["tr_error"] = Input::post("tr_error");
            }
            
            if ($status == "TRUE" && Input::post("tr_error") == "none") {
                $this->ok = true;
                
                $this->log_success("Confirmation successfully processed.");
            } else {
                // it should be marked as failed already, but let's add more data

                $this->transaction->set([
                    'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                    'transaction_out_id' => $this->out_id,
                    'additional_data' => serialize($this->data)
                ]);
                $this->transaction->save();
                
                $this->log_error("Received confirmation with FALSE status.");
            }
            // should always return true
            echo 'TRUE';
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
