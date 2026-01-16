<?php

use Fuel\Core\Response;
use Services\PaymentService;
use Services\Logs\FileLoggerService;

final class Forms_Wordpress_Payment_Skrill extends Forms_Main implements Forms_Wordpress_Payment_Process
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
     * Payment credentials
     * @var array
     */
    protected $payment_data = [];
    
    /**
     *
     * @var array
     */
    private $ips_permitted = [
        [
            'ip' => '91.208.28.0',
            'mask' => '24'
        ],
        [
            'ip' => '93.191.174.0',
            'mask' => '24'
        ],
        [
            'ip' => '193.105.47.0',
            'mask' => '24'
        ],
        [
            'ip' => '195.69.173.0',
            'mask' => '24'
        ],
        ['ip' => '18.156.81.39'],
        ['ip' => '3.64.161.98'],
        ['ip' => '18.195.181.168'],
        ['ip' => '52.16.193.112'],
        ['ip' => '54.228.179.122'],
        ['ip' => '34.249.111.249'],
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
            Helpers_Payment_Method::SKRILL,
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
        $val = Validation::forge("skrill");
        
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
     * @return Model_Whitelabel_Transaction
     */
    public function get_transaction():? Model_Whitelabel_Transaction
    {
        return $this->transaction;
    }
    
    /**
     *
     * @param Model_Whitelabel_Payment_Method $model_whitelabel_payment_method
     * @return \Forms_Wordpress_Payment_Skrill
     */
    public function set_model_whitelabel_payment_method(
        Model_Whitelabel_Payment_Method $model_whitelabel_payment_method = null
    ): Forms_Wordpress_Payment_Skrill {
        if (empty($model_whitelabel_payment_method)) {
            $this->log_error("No model_whitelabel_payment_method set.");
            
            exit(_("Bad request! Please contact us!"));
        }
        
        $this->model_whitelabel_payment_method = $model_whitelabel_payment_method;
        
        return $this;
    }
    
    /**
     *
     * @return Model_Whitelabel_Payment_Method
     */
    public function get_model_whitelabel_payment_method():? Model_Whitelabel_Payment_Method
    {
        $whitelabel_payment_method_id = $this->get_whitelabel_payment_method_id();
        
        $this->model_whitelabel_payment_method = Model_Whitelabel_Payment_Method::find_by_pk(
            $whitelabel_payment_method_id
        );
        
        if (empty($this->model_whitelabel_payment_method)) {
            status_header(400);
            
            $this->log_to_error_file("Empty model_whitelabel_payment_method.");
            
            $error_message = "No payment method of ID: " .
                Helpers_Payment_Method::SKRILL;
            $this->log_error($error_message);
            
            exit($this->get_exit_text());
        }
        
        return $this->model_whitelabel_payment_method;
    }

    /**
     *
     * @return array|null
     */
    public function get_payment_data():? array
    {
        $model_whitelabel_payment_method = $this->get_model_whitelabel_payment_method();
        
        if (!empty($model_whitelabel_payment_method['data']) &&
            !empty(unserialize($model_whitelabel_payment_method['data']))
        ) {
            $this->payment_data = unserialize($model_whitelabel_payment_method['data']);
        }
        
        if (empty($this->payment_data)) {
            if (Helpers_General::is_test_env()) {
                exit("Empty payment data.");
            }
            status_header(400);
            
            $this->log_error("Empty payment data.");
            
            exit($this->get_exit_text());
        }
        
        return $this->payment_data;
    }
    
    /**
     *
     * @return void
     */
    public function check_signature_md5(): void
    {
        $signature = Input::post("merchant_id");
        $signature .= Input::post("transaction_id");
        $secret_word_md5 = md5($this->payment_data['secret_word']);
        $signature .= strtoupper($secret_word_md5);
        $signature .= Input::post("mb_amount");
        $signature .= Input::post("mb_currency");
        $signature .= Input::post("status");

        $signature_md5_t = md5($signature);
        $signature_md5 = strtoupper($signature_md5_t);

        if (!(null !== Input::post("md5sig") &&
                $signature_md5 == Input::post("md5sig"))
        ) {
            if (Helpers_General::is_test_env()) {
                exit("Signature check failed.");
            }
            
            status_header(400);

            $this->log_error("Signature check failed.");

            exit($this->get_exit_text());
        }
    }

    /**
     *
     * @return \Model_Whitelabel_Transaction|null
     */
    public function get_transaction_based_on_token():? Model_Whitelabel_Transaction
    {
        $token = $this->get_token();
        
        if (empty($token)) {
            status_header(400);

            $this->log_error("Bad transaction.");

            exit(_("Bad request! Please contact us!"));
        }
        
        $transactions = Model_Whitelabel_Transaction::find([
            "where" => [
                "whitelabel_id" => $this->whitelabel['id'],
                "token" => $token
            ]
        ]);

        if ($transactions === null ||
            empty($transactions[0])
        ) {
            status_header(400);

            $this->log_error("Bad transaction.");

            exit(_("Bad request! Please contact us!"));
        };
        
        return $transactions[0];
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
     * @return int|null
     */
    public function get_token():? int
    {
        if (empty(Input::post("transaction_id"))) {
            status_header(400);

            $this->log_error("No transaction_id send.");
            
            return null;
        }
        
        $token = intval(substr(Input::post("transaction_id"), 3));
        
        return $token;
    }
    
    /**
     *
     * @return string|null
     */
    public function get_amount():? string
    {
        if (empty(Input::post("amount"))) {
            status_header(400);

            $this->log_error("No amount send.");
            
            return null;
        }
        
        return Input::post("amount");
    }
    
    /**
     *
     * @return string|null
     */
    public function get_currency():? string
    {
        if (empty(Input::post("currency"))) {
            status_header(400);

            $this->log_error("No currency send.");
            
            return null;
        }
        
        return Input::post("currency");
    }
    
    /**
     *
     * @return void
     */
    public function check_amount_and_currency(): void
    {
        $amount = $this->get_amount();
        $currency_code_from_input = $this->get_currency();
        $currency_code = $this->get_payment_currency($this->transaction->payment_currency_id);
        
        // TODO: currency change - I not quite sure if it is OK,
        // but I have done that
        // based on the settings for WL
        if (!((int)$this->transaction->whitelabel_id === (int)$this->whitelabel['id'] &&
            round($amount, 2) == $this->transaction->amount_payment &&
            (string)$currency_code_from_input === (string)$currency_code)
        ) {
            status_header(400);

            $this->log_error("Amount/currency/whitelabel checks failed.");

            exit(_("Bad request! Please contact us!"));
        }
    }
    
    /**
     * @return void
     */
    public function check_credentials(): void
    {
        if ((int)$this->transaction->whitelabel_id !== (int)$this->whitelabel['id'] ||
            (int)$this->transaction->whitelabel_user_id !== (int)$this->user['id']
        ) {
            status_header(400);
            
            $this->log_error("Bad request.");
            
            exit($this->get_exit_text());
        }
    }
    
    /**
     *
     * @param array $post
     * @param array $pdata
     */
    private function userdata(&$post, $pdata)
    {
        if (!empty($this->user['name'])) {
            $post['firstname'] = remove_accents(mb_substr($this->user['name'], 0, 20));
        }
        if (!empty($this->user['surname'])) {
            $post['lastname'] = remove_accents(mb_substr($this->user['surname'], 0, 50));
        }
        if (!empty($this->user['address_1'])) {
            $post['address'] = remove_accents(mb_substr($this->user['address_1'], 0, 100));
        }
        if (!empty($this->user['address_2'])) {
            $post['address2'] = remove_accents(mb_substr($this->user['address_2'], 0, 100));
        }
        if (!empty($this->user['phone']) && !empty($this->user['phone_country'])) {
            $post['phone_number'] = mb_substr(str_replace("+", "", $this->user['phone']), 0, 20);
        }
        if (!empty($this->user['zip'])) {
            $post['postal_code'] = mb_substr(str_replace(["-", "_", " "], "", $this->user['zip']), 0, 9);
        }
        if (!empty($this->user['city'])) {
            $post['city'] = remove_accents(mb_substr($this->user['city'], 0, 50));
        }
        if (!empty($this->user['state'])) {
            $post['state'] = remove_accents(mb_substr(Lotto_View::get_region_name($this->user['state'], false), 0, 50));
        }
        if (!empty($this->user['country'])) {
            $post['country'] = Lotto_Helper::get_3_letter_country_code($this->user['country']);
        }
        if (!empty($this->user['birthdate'])) {
            $bdt = new DateTime($this->user['birthdate'], new DateTimeZone("UTC"));
            $post['date_of_birth'] = $bdt->format('dmY');
        }
        if (!empty($pdata['merchant_description'])) {
            $post['recipient_description'] = $pdata['merchant_description'];
        }
        if (!empty($pdata['merchant_logourl'])) {
            $post['logo_url'] = $pdata['merchant_logourl'];
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
            Helpers_Payment_Method::SKRILL_URI . '/' .
            $whitelabel_payment_method_id . '/';
        
        return $confirmation_url;
    }
        
    /**
     *
     * @return void
     */
    public function process_form(): void
    {
        $this->check_credentials();
        
        $payment_params = unserialize($this->model_whitelabel_payment_method['data']);
        
        if (empty($payment_params['merchant_email'])) {
            $set = [
                'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
                'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
            ];
            $this->transaction->set($set);
            $this->transaction->save();
            
            $this->log_error("Empty merchant e-mail.");
            
            exit(_("Bad request! Please contact us!"));
        }

        $amount = $this->transaction->amount_payment;
        
        $transaction_text = $this->get_prefixed_transaction_token();

        $currency_code = $this->get_payment_currency($this->transaction->payment_currency_id);
        
        $language = str_replace(["en", "pl", "de"], ["EN", "PL", "DE"], $this->code);

        $return_url = lotto_platform_home_url(Helper_Route::ORDER_SUCCESS);
        $cancel_url = lotto_platform_home_url(Helper_Route::ORDER_FAILURE);
        
        $confirmation_url = $this->get_confirmation_url();
        
        $return_text = sprintf(_("Return to %s"), $this->whitelabel['name']);
        
        $target = 3; // _self, not _top
        
        $prepare_only = 1; // secure method
        
        $post = [
            "pay_to_email" => $payment_params['merchant_email'],
            "amount" => $amount,
            "currency" => $currency_code,
            "language" => $language,
            "transaction_id" => $transaction_text,
            "return_url" => $return_url,
            "return_url_text" => $return_text,
            "return_url_target" => $target,
            "cancel_url" => $cancel_url,
            "cancel_url_target" => $target,
            "prepare_only" => $prepare_only,
            "status_url" => $confirmation_url,
            "detail1_description" => _("Transaction ID"),
            "detail1_text" => $transaction_text,
            "payment_methods" => "",
            "pay_from_email" => $this->user['email'] // TODO: change to previously defined Skrill e-mail
        ];
        
        $this->userdata($post, $payment_params);

        $sid = Lotto_Helper::load_API_url("https://pay.skrill.com", $post);

        $set = [
            'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
            'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
        ];
        $this->transaction->set($set);
        $this->transaction->save();

        $post['sid'] = $sid;
        
        $this->log_success(
            "Redirecting to Skrill gateway.",
            $post
        );
        
        Response::redirect("https://pay.skrill.com?sid=" . $sid);
    }
    
    /**
     *
     */
    public function process_confirmation()
    {
        //
        //    91.208.28.0/2, skrill bug, i guess the mask should be 24
        //    93.191.174.0/24,
        //    193.105.47.0/24,
        //    195.69.173.0/24
        //
        
        try {
            $this->log_info(
                "Received confirmation.",
                Input::post()
            );

            $check_ip_range = false;
            foreach ($this->ips_permitted as $permitted) {
                $isIpWithoutMask = !isset($permitted['mask']);
                if ($isIpWithoutMask) {
                    $check_ip_range = $this->ip === $permitted['ip'];
                } else {
                    $check_ip_range = Lotto_Security::check_IP_range(
                        $this->ip,
                        $permitted['ip'],
                        $permitted['mask']
                    );
                }

                if ($check_ip_range) {
                    break;
                }
            }

            if (!$check_ip_range || empty(Input::post())) {
                status_header(400);

                $this->log_error(
                    "Bad IP or empty POST.",
                    [$this->ip]
                );

                exit(_("Bad request! Please contact us!"));
            }
            
            $this->transaction = $this->get_transaction_based_on_token();
            
            $this->check_amount_and_currency();
            
            if (!((int)$this->transaction->payment_method_type === Helpers_General::PAYMENT_TYPE_OTHER &&
                !empty($this->transaction->whitelabel_payment_method_id))
            ) {
                status_header(400);
                
                $this->log_error("Bad payment type.");
                
                exit($this->get_exit_text());
            }
            
            $this->get_payment_data();
            
            $this->check_signature_md5();
            
            $this->data['pay_to_email'] = Input::post("pay_to_email");
            $this->data['pay_from_email'] = Input::post("pay_from_email");
            
            $this->out_id = Input::post('mb_transaction_id');
            $this->data['mb_amount'] = Input::post("mb_amount");
            $this->data['mb_currency'] = Input::post("mb_currency");
            
            if (Input::post("customer_id") !== null &&
                !empty(Input::post("customer_id"))
            ) {
                $this->data['customer_id'] = Input::post("customer_id");
            }
            
            if (Input::post("merchant_id") !== null &&
                !empty(Input::post("merchant_id"))
            ) {
                $this->data['merchant_id'] = Input::post("merchant_id");
            }
            
            if (Input::post("failed_reason_code") !== null &&
                !empty(Input::post("failed_reason_code"))
            ) {
                $this->data['failed_reason_code'] = Input::post("failed_reason_code");
            }
            
            // I don't know what exactly mean status = 2 in that case!
            if (Input::post("status") == 2 &&
                ((int)$this->transaction->status === Helpers_General::STATUS_TRANSACTION_PENDING ||
                    (int)$this->transaction->status === Helpers_General::STATUS_TRANSACTION_ERROR)
            ) {
                $this->ok = true;
                
                $this->log_success("Confirmation successfully processed.");
            } elseif (Input::post("status") == -2 ||    // Same as above I don't
                Input::post("status") == -1             // know what exactly mean status = -2 or -1
            ) {
                $this->log_warning("Received status: " . Input::post("status"));
                
                $set = [
                    'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                    'transaction_out_id' => $this->out_id,
                    'additional_data' => serialize($this->data)
                ];
                
                $this->transaction->set($set);
                $this->transaction->save();
            } else {
                $this->log_warning("Transaction has incorrect status.");
            }
        } catch (\Exception $e) {
            $this->log_error(
                "Unknown error: " . $e->getMessage(),
                [$e]
            );
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
