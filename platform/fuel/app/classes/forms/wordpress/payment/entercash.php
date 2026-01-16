<?php

use Fuel\Core\Response;
use Services\PaymentService;
use Services\Logs\FileLoggerService;

/**
 * Class for preparing Forms_Wordpress_Payment_Entercash form
 */
final class Forms_Wordpress_Payment_Entercash implements Forms_Wordpress_Payment_Process
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
    private $pay_data = [];
    
    /**
     *
     * @var string
     */
    private $failure_url = "";
    
    /**
     *
     * @var string
     */
    private $success_url = "";
    
    /**
     *
     * @var string
     */
    private $cli_notification_url = "";
    
    /**
     *
     * @var string
     */
    private $cli_result_url = "";
    
    /**
     *
     * @var array
     */
    private $ips_permitted = [
        "test" => [
            "185.36.237.142",
        ],
        "production" => [
            "185.36.237.132",
        ],
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
            Helpers_Payment_Method::ENTERCASH,
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
            Helpers_Payment_Method::ENTERCASH_URI . '/' .
            $whitelabel_payment_method_id . '/';
        
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
            Helpers_Payment_Method::ENTERCASH_URI .
            "/" .
            $whitelabel_payment_method_id .
            "/";
        
        return $result_url;
    }
    
    /**
     *
     * @return void
     */
    public function set_urls(): void
    {
        $this->cli_notification_url = $this->get_confirmation_url();
        
        $this->failure_url = lotto_platform_home_url(Helper_Route::ORDER_FAILURE);
        $this->success_url = lotto_platform_home_url(Helper_Route::ORDER_SUCCESS);
        
        $this->cli_result_url = $this->get_result_url();
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
     * @return \Forms_Wordpress_Payment_Entercash
     */
    public function set_model_whitelabel_payment_method(
        Model_Whitelabel_Payment_Method $model_whitelabel_payment_method = null
    ): Forms_Wordpress_Payment_Entercash {
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
        $val = Validation::forge("entercash");
        
        return $val;
    }

    /**
     *
     * @param array $post
     */
    private function userdata(&$post)
    {
        $name = "";
        if (!empty($this->user['name'])) {
            $name = $this->user['name'];
        }
        if (!empty($this->user['surname'])) {
            $name .= (empty($name) ? "" : " ") . $this->user['surname'];
        }
        if (!empty($name)) {
            $post['payer_name'] = $name;
        }

//        if (!empty($this->user['country'])) {
//            $post['clearing_house'] = $this->user['country'];
//        }

        if (!empty($this->user['phone']) && !empty($this->user['phone_country'])) {
            $post['payer_phone'] = mb_substr($this->user['phone'], 0, 32);
        }
        if (!empty($this->user['address_1'])) {
            $post['payer_street'] = mb_substr($this->user['address_1'], 0, 128);
        }
        if (!empty($this->user['zip'])) {
            $post['payer_zip'] = mb_substr($this->user['zip'], 0, 16);
        }
        if (!empty($this->user['city'])) {
            $post['payer_city'] = mb_substr($this->user['city'], 0, 64);
        }
        if (!empty($this->user['country'])) {
            $countries = Lotto_Helper::get_localized_country_list();
            $post['payer_country'] = $countries[$this->user['country']];
        }
    }
    
    /**
     *
     * @param array $pdata
     * @param array $post
     * @return string
     */
    private function connect_with_entercash($pdata, $post)
    {
        $endpoint = "https://api." . ($pdata['test'] == 1 ? 'test.' : '') .
            "ecdirect.net/v2/predeposit/" . $pdata['api_id'] . '/';
        
        $ssl_verifypeer = 2;
        $ssl_verifyhost = 2;
        if (Helpers_General::is_development_env()) {
            $ssl_verifypeer = 0;
            $ssl_verifyhost = 0;
        }
        
        $curl = curl_init();
        
        curl_setopt($curl, CURLOPT_URL, $endpoint);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $ssl_verifypeer);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $ssl_verifyhost);

        $response = curl_exec($curl);

        if ($response === false || json_decode($response) === false) {
            $this->transaction->set([
                'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
                'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
            ]);
            $this->transaction->save();
            
            $this->log_error(
                "CURL Error.",
                [curl_error($curl)]
            );
            
            curl_close($curl);
            
            exit(_("Bad request! Please contact us!"));
        }

        curl_close($curl);
        
        $response = json_decode($response);
        
        return $response;
    }
    
    /**
     *
     */
    public function process_form()
    {
        if ((int)$this->transaction->whitelabel_id !== (int)$this->whitelabel['id'] ||
           (int)$this->transaction->whitelabel_user_id !== (int)$this->user['id']
        ) {
            $this->log_error("Bad request.");
            
            exit(_("Bad request! Please contact us!"));
        }
        
        $payment_params = unserialize($this->model_whitelabel_payment_method['data']);
        
        if (empty($payment_params['api_id']) || empty($payment_params['private_key'])) {
            $this->transaction->set([
                'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
                'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
            ]);
            $this->transaction->save();
            
            $this->log_error("Empty API ID or Private Key.");
            
            exit(_("Bad request! Please contact us!"));
        }

        $this->set_urls();
        
        $amount = $this->transaction->amount_payment;
        
        $transaction_text = $this->get_prefixed_transaction_token();

        $currency_code = $this->get_payment_currency($this->transaction->payment_currency_id);
        
        $user_text = $this->get_prefixed_user_token($this->user);
        
        $post = [
            'amount' => $amount,
            'currency' => $currency_code,
            'cli_tx_id' => $transaction_text,
            'cli_notification_url' => $this->cli_notification_url,
            'cli_result_url' => $this->cli_result_url,
            'payer_id' => $user_text,
            'payer_email' => $this->user['email']
        ];
        
        $this->userdata($post);

        ksort($post);

        $tosign = implode("&", $post) . '&';
        
        $private_key = openssl_get_privatekey($payment_params['private_key']);
        $signature = null;

        openssl_sign($tosign, $signature, $private_key, OPENSSL_ALGO_SHA1);

        openssl_free_key($private_key);

        $signature = base64_encode($signature);

        $post['signature'] = $signature;

        $response = $this->connect_with_entercash($payment_params, $post);
        
        if (isset($response->error_message) ||
            !isset($response->pre_deposit_id) ||
            !isset($response->deposit_url)
        ) {
            $this->transaction->set([
                'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
                'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
            ]);
            $this->transaction->save();
            
            $this->log_error(
                "Entercash error.",
                [$response]
            );
            
            Response::redirect($this->failure_url);
        }

        $this->transaction->set([
            "additional_data" => serialize([
                "pre_deposit_id" => $response->pre_deposit_id
            ]),
            'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
            'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
        ]);
        $this->transaction->save();
        
        $this->log_success("Redirecting to Entercash.");
        
        Response::redirect($response->deposit_url);
    }
    
    /**
     *
     * @return string
     */
    private function get_pkey()
    {
        // In my opinion such keys should be saved into file
        // and should be pulled from there
        $pkey = "";
        if ($this->pay_data['test']) {
            $pkey = "-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAsL/PqIs4uAlTc/KxkWRm
2oOt6q3xVRBa6E1SlhxpYH+njLOg4ovXkkeSQQT+ZGkA/0FbFyXFGlJEq3yHtTVJ
QLSq7WNfnQiVXtV6shetblVlZs1X7l2TymRMH4227jOtvcxRDApLR3w51Ciuvr3L
iML+XAwOCHXaMOs51cftlHB/tJh3rqxYFL22qO0H7H0v9n0SSRh0L+JefPRSWwN5
Na6tTLsAmET7WFJfOEvkDcpbKl7otYbrYTk1gPoTKPO0lNIEJJgDar1lV6ir8sSh
4aOzFL4AqnZ/Wn3TBVU42aGGQDgay14BG1SJRMUWhanrLYByRzpIRnpdXdnkLcDi
GwIDAQAB
-----END PUBLIC KEY-----";
        } else {
            $pkey = "-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA1GulHSMbUlgK4ripVBLO
u43ICutZzuGKb3+4Yb8gs63kiowu1tI8492Sn4E0TxjyJONhghQOonlZsajmSMYx
l8pj7kDPNw9svl2Ps6vauRebksnjzCpnGxWDT55O6VWCLi7gYXrZye8ghCjGc742
07r+X6sFUt5MnRSsilcN+va4TP3RrhZRg8VPNlLiK2IqTS3f+awA7/hyRNtmbz21
fX78ZEqgn3G3urLezo7fAVqg9Tu3feUJpVuWCPEYbD4VCS1Ax8l3kNbLKiwolJ3i
aBjIWAqY6DKce9Y58o4MDCA7l1ZSNHoetELJbi7/db7g5Sgrb9oJeg5FL0+XD07T
SwIDAQAB
-----END PUBLIC KEY-----";
        }
        
        return $pkey;
    }
    
    /**
     *
     * @param string $transaction_token
     * @param string $redirect_url Default is empty so it will be exited in the case of no transaction found
     * @return array
     */
    private function get_transaction_by_token($transaction_token, $redirect_url = "")
    {
        $transaction_temp = Model_Whitelabel_Transaction::find([
            "where" => [
                "whitelabel_id" => $this->whitelabel['id'],
                "token" => $transaction_token
            ]
        ]);

        if ($transaction_temp === null || count($transaction_temp) == 0) {
            status_header(400);
            
            $this->log_error("Couldn't find transaction.");
            
            if (empty($redirect_url)) {
                exit(_("Bad request! Please contact us!"));
            } else {
                Response::redirect($redirect_url);
            }
        }
        
        return $transaction_temp;
    }
    
    /**
     *
     * @param string $redirect_url Default is empty so it will be exited in the case of no payment_method found
     */
    private function get_payment_type($redirect_url = "")
    {
        if (!((int)$this->transaction->payment_method_type === Helpers_General::PAYMENT_TYPE_OTHER &&
            !empty($this->transaction->whitelabel_payment_method_id))
        ) {
            status_header(400);
            
            $this->log_error("Bad payment type.");
            
            if (empty($redirect_url)) {
                exit(_("Bad request! Please contact us!"));
            } else {
                Response::redirect($redirect_url);
            }
        }
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
            (int)$this->model_whitelabel_payment_method->payment_method_id === Helpers_Payment_Method::ENTERCASH)
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
     * @param string $status
     * @param array $data
     * @param array $paydata
     */
    private function entercash_response($status, $data, $paydata)
    {
        $response = [];
        $response['result'] = [
            "data" => [
                "status" => $status
            ],
            "method" => $data['method'],
            "uuid" => $data['uuid'],
        ];
        $response['version'] = "1.1";

        $sig = $data['method'] . $data['uuid'] . "status" . $status;

        $rsig = null;
        $private_key = openssl_get_privatekey($paydata['private_key']);
        openssl_sign($sig, $rsig, $private_key, OPENSSL_ALGO_SHA1);
        openssl_free_key($private_key);
        $rsig = base64_encode($rsig);
        $response['result']['signature'] = $rsig;

        echo json_encode($response);
    }
    
    /**
     *
     */
    public function process_confirmation()
    {
        try {
            $notification = file_get_contents('php://input');
            
            $this->log_info(
                "Received Entercash confirmation.",
                [$notification]
            );
            
            $input = json_decode($notification, true);

            if (!($input !== false &&
                !empty($input['params']) &&
                !empty($input['params']['data']) &&
                !empty($input['params']['data']['messageid']))
            ) {
                status_header(400);
                
                $this->log_error(
                    "Empty data messageid.",
                    [$input]
                );
                
                exit(_("Bad request! Please contact us!"));
            }
            
            $transaction_token = intval(substr($input['params']['data']['messageid'], 3));
            $transaction_temp = $this->get_transaction_by_token($transaction_token);
            
            $this->transaction = $transaction_temp[0];
            
            $this->get_payment_type();
            
            $this->get_model_whitelabel_payment_method();
            
            $this->pay_data = unserialize($this->model_whitelabel_payment_method->data);

            if (!(!empty($this->pay_data) &&
                !empty($this->pay_data['api_id']) &&
                !empty($this->pay_data['private_key']))
            ) {
                status_header(400);
                
                $this->log_error(
                    "Empty payment data.",
                    [$this->pay_data]
                );
                
                exit(_("Bad request! Please contact us!"));
            }
            
            $test = $this->pay_data['test'];
            
            if (($test && !in_array($this->ip, $this->ips_permitted['test'])) ||
                (!$test && !in_array($this->ip, $this->ips_permitted['production']))
            ) {
                status_header(400);
                
                $this->log_error(
                    "Bad IP.",
                    [$this->ip]
                );
                
                exit(_("Bad request! Please contact us!"));
            }

            $this->data = unserialize($this->transaction->additional_data);
            
            $sigverify = $input['params']['data'];
            Lotto_Helper::ksort_recursive($sigverify);

            $sigverify_flatten = "";

            foreach ($sigverify as $key => $value) {
                if (!is_array($value)) {
                    $sigverify_flatten .= $key . $value;
                } else {
                    // attributes level
                    $sigverify_flatten .= $key;
                    foreach ($value as $key2 => $value2) {
                        if (!is_array($value2)) {
                            $sigverify_flatten .= $key2 . $value2;
                        } else {
                            // user details level
                            $sigverify_flatten .= $key2;
                            foreach ($value2 as $key3 => $value3) {
                                if (!is_array($value3)) {
                                    $sigverify_flatten .= $key3 . $value3;
                                } else {
                                    status_header(400);
                                    $this->log_error(
                                        "Unsupported flatten level.",
                                        [$sigverify]
                                    );
                                    
                                    $this->entercash_response("FAILURE", $this->data, $this->pay_data);
                                }
                            }
                        }
                    }
                }
            }

            $sigverify_flatten = $input['method'] . $input['params']['uuid'] . $sigverify_flatten;

            $pkey = $this->get_pkey();

            $pubkey = openssl_get_publickey($pkey);

            $signature_decoded = base64_decode($input['params']['signature']);
            $signature_verified = openssl_verify($sigverify_flatten, $signature_decoded, $pubkey);
            
            if ($signature_verified !== 1) {
                openssl_free_key($pubkey);
                status_header(400);
                
                $this->log_error(
                    "Bad signature.",
                    [$sigverify, $input['params']['signature']]
                );
                
                $this->entercash_response("FAILURE", $this->data, $this->pay_data);
            }

            openssl_free_key($pubkey);

            $this->data['method'] = $input['method'];
            $this->data['uuid'] = $input['params']['uuid'];
            $idata = $input['params']['data'];

            if (isset($idata['amount'])) {
                $this->data['amount'] = $idata['amount'];
            }
            if (isset($idata['currency'])) {
                $this->data['currency'] = $idata['currency'];
            }
            $this->out_id = $idata['orderid'];

            $this->data['enduser_id'] = $idata['enduserid'];
            $this->data['notification_id'] = $idata['notificationid'];
            $this->data['timestamp'] = $idata['timestamp'];
            $this->data['attributes'] = $idata['attributes'];

            switch ($this->data['method']) {
                case 'debit':
                case 'credit':
                    $this->ok = true;
                    
                    $this->log_success(
                        "Confirmation successfully processed with status: " .
                        $this->data['method'] .
                        "."
                    );
                    
                    break;
                case 'expired':
                case 'executed':
                case 'approval':
                case 'closed':
                    $this->transaction->set([
                        'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                        'transaction_out_id' => $this->out_id,
                        'additional_data' => serialize($this->data)
                    ]);
                    $this->transaction->save();
                    
                    $this->log_error(
                        "Received confirmation with failure status.",
                        [$this->data['method']]
                    );
                    
                    break;
                default:
                    break;
            }

            $this->entercash_response("OK", $this->data, $this->pay_data);
        } catch (Exception $e) {
            status_header(400);
            
            $this->log_error("Unknown error: " . $e->getMessage());
            
            // Here I left that instruction, because I am not quite sure if it should be commented
            // but I suppose there could be situation that $this->data or $this->paydata could be empty
            // or not fully prepared by needed data!!!
            $this->entercash_response("FAILURE", $this->data, $this->pay_data);
        }
    }

    /**
     *
     * @return null
     */
    public function process_checking()
    {
        $get = Input::get();

        $this->log_info(
            "Returned from Entercash payment.",
            [$get]
        );
            
        $prefixes_tab = [
            $this->whitelabel['prefix'].'D',
            $this->whitelabel['prefix'].'P'
        ];

        $transaction_token = str_replace($prefixes_tab, '', Input::get("clitxid"));
        $transaction_temp = $this->get_transaction_by_token($transaction_token, $this->failure_url);

        $this->transaction = $transaction_temp[0];

        $this->get_payment_type($this->failure_url);

        $this->get_model_whitelabel_payment_method($this->failure_url);
        
        if (!isset($get['signature'])) {
            status_header(400);
            
            $this->log_error("No signature.");
            
            Response::redirect($this->failure_url);
        }
        
        $this->pay_data = unserialize($this->model_whitelabel_payment_method->data);
        $pkey = $this->get_pkey();
        
        $sig_to_check = str_replace(' ', '+', $get['signature']);

        unset($get['signature']);

        ksort($get);

        $tocheck = implode('&', $get) . '&';

        $pubkey = openssl_get_publickey($pkey);

        if (openssl_verify($tocheck, base64_decode($sig_to_check), $pubkey) !== 1) {
            openssl_free_key($pubkey);
            status_header(400);
            
            $this->log_error(
                "Bad signature.",
                [$sig_to_check]
            );
            
            Response::redirect($this->failure_url);
        }

        openssl_free_key($pubkey);

        $data = unserialize($this->transaction->additional_data);
        $out_id = null;
        if (isset($get['orderid'])) {
            $out_id = $get['orderid'];
        }
        $data['order_status'] = $get['orderstatus'];
        if (isset($get['orderstatusdetails'])) {
            $data['order_status_details'] = $get['orderstatusdetails'];
        }
        $data['amount'] = $get['amount'];
        $data['currency'] = $get['currency'];
        if (isset($get['static_id'])) {
            $data['static_id'] = $get['static_id'];
        }
        if (isset($get['clearinghouse'])) {
            $data['clearing_house'] = $get['clearinghouse'];
        }

        $this->transaction->set(
            [
                "transaction_out_id" => $out_id,
                "additional_data" => serialize($data)
            ]
        );
        $this->transaction->save();

        if ($get['orderstatus'] == "OK" || $get['orderstatus'] == "PENDING") {
            Response::redirect($this->success_url);
        } else {
            Response::redirect($this->failure_url);
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
