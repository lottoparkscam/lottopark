<?php

use Fuel\Core\Response;
use Fuel\Core\Validation;
use Services\Logs\FileLoggerService;

final class Forms_Wordpress_Payment_Neteller extends Forms_Wordpress_Payment_Base implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method;
    private FileLoggerService $fileLoggerService;

    const TYPE_CHECK_CLIENT = 1;
    const TYPE_SAVE_ORDERS = 2;
    const TYPE_GET_CUSTOMER = 3;
    const TYPE_GET_PAYMENTS = 4;
    
    /**
     *
     * @var int
     */
    protected $payment_method = Helpers_Payment_Method::NETELLER;
    
    /**
     *
     * @var null|array
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
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $validation = Validation::forge("neteller");
        
        return $validation;
    }
    
    /**
     * @return void
     */
    protected function check_merchant_settings(): void
    {
        $this->get_payment_data();
        
        if (empty($this->payment_data['app_client_id']) ||
            empty($this->payment_data['app_client_secret'])
        ) {
            $this->save_transaction_error_status();
            
            if (Helpers_General::is_test_env()) {
                exit("Wrong credentials for Whitelabel.");
            }
            
            status_header(400);
            
            $this->log_error("Empty Neteller app_client_id or app_client_secret.");
            exit($this->get_exit_text());
        }
    }
    
    /**
     *
     * @return null|array
     */
    public function get_data():? array
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
     * @return void
     */
    public function save_transaction_error_status(): void
    {
        $this->transaction->set([
            'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
            'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
            'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
        ]);
        
        $this->transaction->save();
    }
    
    /**
     *
     * @param int $type_of_request
     * @param string $url
     * @param array $header_table
     * @param bool $is_transaction Default false
     * @param string $data Default empty string
     * @param string $post_data Default empty string
     * @param bool $should_exit Default true, if false it returns null
     * @return mixed
     */
    public function process_curl(
        int $type_of_request,
        string $url,
        array $header_table,
        bool $is_transaction = false,
        string $data = "",
        string $post_data = "",
        bool $should_exit = true
    ) {
        $ssl_verifypeer = 2;
        $ssl_verifyhost = 2;
        if (Helpers_General::is_development_env()) {
            $ssl_verifypeer = 0;
            $ssl_verifyhost = 0;
        }
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        
        if ($type_of_request === self::TYPE_CHECK_CLIENT) {
            curl_setopt($ch, CURLOPT_USERPWD, $data);
        }
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        
        if ($type_of_request === self::TYPE_CHECK_CLIENT ||
            $type_of_request === self::TYPE_SAVE_ORDERS
        ) {
            curl_setopt($ch, CURLOPT_POST, true);
        }
        
        if ($type_of_request === self::TYPE_SAVE_ORDERS) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }
        
        if ($type_of_request === self::TYPE_CHECK_CLIENT) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header_table);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $ssl_verifypeer);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $ssl_verifyhost);

        $response = curl_exec($ch);
        
        if ($response === false) {
            if ($is_transaction) {
                $this->save_transaction_error_status();
            }
            
            if ($type_of_request === self::TYPE_GET_CUSTOMER) {
                status_header(400);
            }
            
            $this->log_error(
                "CURL Error.",
                [curl_error($ch)]
            );
            
            curl_close($ch);
            
            if ($should_exit) {
                exit(_("Bad request! Please contact us!"));
            }
            
            return null;
        }
        
        curl_close($ch);
        
        $response_data = json_decode($response);
        
        return $response_data;
    }
    
    /**
     *
     * @param array $payment_data
     * @param bool $is_transaction
     * @param bool $should_exit Default true
     * @return type
     */
    private function check_client_on_neteller(
        array $payment_data,
        bool $is_transaction,
        bool $should_exit = true
    ) {
        $client_credentials_url = "https://";
        if (!empty($payment_data['test'])) {
            $client_credentials_url .= 'test.';
        }
        $client_credentials_url .= "api.neteller.com/v1/oauth2/token?grant_type=client_credentials";
        
        $client_credentials_data = $payment_data['app_client_id'] . ':' . $payment_data['app_client_secret'];
        
        $header_table = [
            "Content-Type: application/json",
            "Cache-Control: no-cache"
        ];
        
        $data = $this->process_curl(
            self::TYPE_CHECK_CLIENT,
            $client_credentials_url,
            $header_table,
            $is_transaction,
            $client_credentials_data,
            "",
            "",
            $should_exit
        );
        
        return $data;
    }

    /**
     *
     * @param array $payment_data
     * @param array $post
     * @param string $token
     * @return array
     */
    private function save_orders_on_neteller(
        array $payment_data,
        array $post,
        string $token
    ) {
        $orders_url = "https://" .
            (!empty($payment_data['test']) ? 'test.' : '') .
            "api.neteller.com/v1/orders";
        
        $header_table = [
            "Content-Type: application/json",
            "Authorization: Bearer " . $token
        ];
        
        $post_data = json_encode($post);
        
        $data = $this->process_curl(
            self::TYPE_SAVE_ORDERS,
            $orders_url,
            $header_table,
            true,
            "",
            $post_data
        );

        return $data;
    }
    
    /**
     *
     * @param mixed $json
     * @param string $token
     * @return mixed
     */
    private function get_customer_from_neteller($json, string $token)
    {
        $payment_link = $json->links[0]->url;
        $payment_link .= '?expand=customer';
        
        $header_table = [
            "Content-Type: application/json",
            "Authorization: Bearer " . $token
        ];
        
        $data = $this->process_curl(
            self::TYPE_GET_CUSTOMER,
            $payment_link,
            $header_table
        );
        
        return $data;
    }
    
    /**
     *
     * @param array $payment_data
     * @param string $full_token
     * @param string $token
     * @return mixed
     */
    private function get_payments(
        array $payment_data,
        string $full_token,
        string $token
    ) {
        $payment_link = "https://" .
            (!empty($payment_data['test']) ? 'test.' : '') .
            "api.neteller.com/v1/payments/" .
            $full_token .
            "?refType=merchantRefId&expand=customer";
        
        $header_table = [
            "Content-Type: application/json",
            "Authorization: Bearer " . $token
        ];
        
        $data = $this->process_curl(
            self::TYPE_GET_PAYMENTS,
            $payment_link,
            $header_table,
            false,
            "",
            "",
            false
        );
        
        return $data;
    }
    
    /**
     *
     * @return array
     */
    private function get_supported_languages_codes()
    {
        $shortcodes = [
            "en" => "en_US",
            "pl" => "pl_PL",
            "de" => "de_DE",
            "da" => "da_DK",
            "el" => "el_GR",
            "es" => "es_ES",
            "fr" => "fr_FR",
            "it" => "it_IT",
            "ja" => "ja_JP",
            "ko" => "ko_KR",
            "no" => "no_NO",
            "pt" => "pt_PT",
            "ru" => "ru_RU",
            "sv" => "sv_SE",
            "tr" => "tr_TR"
        ];
        
        return $shortcodes;
    }
    
    /**
     *
     * @param array $billing_details
     */
    private function userdata(array &$billing_details)
    {
        $billing_details['email'] = mb_substr($this->user['email'], 0, 100);

        if (!empty($this->user['name'])) {
            $billing_details['firstName'] = mb_substr($this->user['name'], 0, 25);
        }
        if (!empty($this->user['surname'])) {
            $billing_details['lastName'] = mb_substr($this->user['surname'], 0, 25);
        }
        if (!empty($this->user['address_1'])) {
            $billing_details['address1'] = mb_substr($this->user['address_1'], 0, 35);
        }
        if (!empty($this->user['address_2'])) {
            $billing_details['address2'] = mb_substr($this->user['address_2'], 0, 35);
        }
        if (!empty($this->user['city'])) {
            $billing_details['city'] = mb_substr($this->user['city'], 0, 50);
        }
        if (!empty($this->user['state'])) {
            $state = explode('-', $this->user['state']);
            $billing_details['countrySubdivisionCode'] = $state[1];
        }
        if (!empty($this->user['country'])) {
            $billing_details['country'] = $this->user['country'];
        }
        if (!empty($this->user['zip'])) {
            $billing_details['postCode'] = mb_substr($this->user['zip'], 0, 10);
        }
    }
    
    /**
     *
     */
    public function process_form()
    {
        $this->check_credentials();
        
        $this->check_merchant_settings();

        $success_url = lotto_platform_home_url(Helper_Route::ORDER_SUCCESS);
        $failure_url = lotto_platform_home_url(Helper_Route::ORDER_FAILURE);
        
        $payment_data = $this->payment_data;
        
        $data = $this->check_client_on_neteller($payment_data, true);
        
        if (isset($data->error) && !empty($data->error)) {
            $this->save_transaction_error_status();
            
            $this->log_error(
                "Could not connect to Neteller gateway.",
                ["data" => $data]
            );
            
            Response::redirect($failure_url);
        }
        
        if (isset($data->accessToken) && !empty($data->accessToken)) {
            $token = $data->accessToken;
            $items = [];
            
            if ((int)$this->transaction->type === Helpers_General::TYPE_TRANSACTION_PURCHASE) {
                $transaction_id = null;
                if (!empty($this->transaction->id)) {
                    $transaction_id = $this->transaction->id;
                }
                $tickets = Model_Whitelabel_User_Ticket::get_full_data_with_counted_lines($transaction_id);
                
                foreach ($tickets as $ticket) {
                    $lottery = $this->lotteries['__by_id'][$ticket['lottery_id']];
                    
                    $name = sprintf(
                        _("%s ticket"),
                        _($lottery['name'])
                    );

                    $amount = round($ticket["line_payment_amount"] * 100, 2);
                    
                    $single_item = [
                        "quantity" => $ticket['count'],
                        "name" => $name,
                        "description" => $name,
                        "amount" => $amount
                    ];
                    
                    $items[] = $single_item;
                }
            } else {
                $amount = round($this->transaction->amount_payment * 100, 2);
                
                $items[] = [
                    "quantity" => 1,
                    "name" => _("Deposit"),
                    "description" => _("Deposit"),
                    "amount" => $amount
                ];
            }

            $billing_details = [];
            $billing_detail = [];
            $this->userdata($billing_detail);

            $supported_shortcodes = array_keys($this->get_supported_languages_codes());
            $supported_languages = array_values($this->get_supported_languages_codes());

            $replaced_code = str_replace(
                $supported_shortcodes,
                $supported_languages,
                $this->code
            );
                
            $billing_detail['lang'] = $replaced_code;
            if (!in_array($billing_detail['lang'], $supported_languages)) {
                $billing_detail['lang'] = 'en_US';
            }
            $billing_details[0] = $billing_detail;

            $language = $replaced_code;
            if (!in_array($language, $supported_languages)) {
                $language = "en_US";
            }
            
            $total_amount_multi = round($this->transaction->amount_payment * 100, 0);
            $total_amount = intval($total_amount_multi);
            
            $merchant_ref_id = $this->get_prefixed_transaction_token();

            $currency_code = $this->get_payment_currency($this->transaction->payment_currency_id);
            
            $post = [
                "order" => [
                    "merchantRefId" => $merchant_ref_id,
                    "totalAmount" => $total_amount,
                    "currency" => $currency_code,
                    "lang" => $language,
                    "items" => $items,
                    //"customerIp" => Lotto_Security::get_IP(), // does not work with IPv6
                    "redirects" => [
                        [
                            "rel" => "on_success",
                            "uri" => $success_url
                        ],
                        [
                            "rel" => "on_cancel",
                            "uri" => $failure_url
                        ]
                    ],
                /* "paymentMethods" => array(
                  array(
                  "type" => "neteller",
                  "value" => "NULL"
                  ),
                  array(
                  "type" => "onlinebanking",
                  "value" => "giropay"
                  )
                  ) */
                ],
                "billingDetails" => $billing_details
            ];

            $data = $this->save_orders_on_neteller(
                $payment_data,
                $post,
                $token
            );
            
            if (!empty($data->orderId) &&
                !empty($data->merchantRefId) &&
                !empty($data->links[0]->url)
            ) {
                $this->log_success(
                    "Created Neteller order. Redirecting to Neteller GO!",
                    ["post" => $post, "data" => $data]
                );
               
                $this->transaction->set([
                    //"transaction_out_id" => $data->merchantRefId, // do not set it to merchant ref id, as the transaction is not yet visible in the neteller admin
                    "additional_data" => serialize(["order_id" => $data->orderId]),
                    'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
                    'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
                ]);
                $this->transaction->save();
                
                Response::redirect($data->links[0]->url);
            } else {
                $this->save_transaction_error_status();
                
                $this->log_error(
                    "Error while creating order.",
                    ["post" => $post, "data" => $data]
                );
                
                Response::redirect($failure_url);
            }
        }
    }
    
    /**
     *
     * @param array $data
     * @param mixed $json
     * @param array $customer_data
     * @param bool $from_confirmation
     * @return mi
     */
    private function prepare_data(
        $data,
        $json,
        $customer_data,
        bool $from_confirmation = true
    ) {
        if ($from_confirmation && !empty($json)) {
            $data['event_id'] = $json->id;
            $data['event_date'] = $json->eventDate;
            $data['event_type'] = $json->eventType;
            $data['attempt_number'] = $json->attemptNumber;
        }
        
        $data['amount'] = $customer_data->transaction->amount;
        $data['currency'] = $customer_data->transaction->currency;
        $data['create_date'] = $customer_data->transaction->createDate;
        $data['update_date'] = $customer_data->transaction->updateDate;

        if (isset($customer_data->transaction->errorCode)) {
            $data['error_code'] = $customer_data->transaction->errorCode;
        }
        
        if (isset($customer_data->transaction->errorMessage)) {
            $data['error_message'] = $customer_data->transaction->errorMessage;
        }

        $data['status'] = $customer_data->transaction->status;
        $data['transaction_type'] = $customer_data->transaction->transactionType;

        if (isset($customer_data->transaction->fees)) {
            $data['fees'] = $customer_data->transaction->fees;
        }
        
        if (isset($customer_data->billingDetail)) {
            $data['billing_detail'] = $customer_data->billingDetail;
        }
        
        if (isset($customer_data->transaction->description)) {
            $data['description'] = $customer_data->transaction->description;
        }
        
        if (isset($customer_data->transaction->customer)) {
            $data['customer'] = $customer_data->transaction->customer;
        }
        
        return $data;
    }
    
    /**
     * I am not quite sure if it is still working and needed.
     * And probably it is wrong, because I can't get ID for whitelabel_payment_method
     * which was used to create transaction
     */
    public function process_confirmation()
    {
        try {
            $json_data = file_get_contents('php://input');
            
            $this->log_info(
                "Received confirmation.",
                [$json_data]
            );

            $json = json_decode($json_data);
            
            if (empty($json) ||
                $json === false ||
                empty($json->id) ||
                empty($json->eventDate) || empty($json->eventType) ||
                empty($json->key)
            ) {
                status_header(400);
                
                $this->log_error(
                    "Bad event data.",
                    [$json]
                );
                
                exit(_("Bad request! Please contact us!"));
            }

            // Here should be look for whitelabel_payment_methods by given ID
            // which I don't have:(
            // So in my opinion it is wrong code,
            // but as far as I can I updated the code
            $whitelabel_payment_methods = Model_Whitelabel_Payment_Method::find([
                "where" => [
                    "whitelabel_id" => $this->whitelabel['id'],
                    "payment_method_id" => Helpers_Payment_Method::NETELLER
                ]
            ]);
            
            if ($whitelabel_payment_methods === null ||
                count($whitelabel_payment_methods) == 0
            ) {
                status_header(400);
                
                $this->log_error("Couldn't find Neteller payment method.");
                
                exit(_("Bad request! Please contact us!"));
            }

            $whitelabel_payment_method = $whitelabel_payment_methods[0];
            $payment_data = unserialize($whitelabel_payment_method['data']);

            if ($json->key != $payment_data['webhook_secret_key']) {
                status_header(400);
                
                $this->log_error("Bad webhook secret key.");
                
                exit(_("Bad request! Please contact us!"));
            }
            
            $ncheck_client = $this->check_client_on_neteller($payment_data, false);
            
            if (isset($ncheck_client->error) &&
                !empty($ncheck_client->error)
            ) {
                status_header(400);
                
                $this->log_error(
                    "Neteller error.",
                    [$ncheck_client]
                );
                
                exit(_("Bad request! Please contact us!"));
            }
            
            if (!isset($ncheck_client->accessToken) ||
                empty($ncheck_client->accessToken)
            ) {
                status_header(400);
                
                $this->log_error(
                    "Missing neteller accessToken.",
                    [$ncheck_client]
                );
                
                exit(_("Bad request! Please contact us!"));
            }
            
            $token = $ncheck_client->accessToken;
            
            $customer_data = $this->get_customer_from_neteller($json, $token);
            
            if (isset($customer_data->error) &&
                !empty($customer_data->error)
            ) {
                status_header(400);
                
                $this->log_error(
                    "Neteller error while fetching payment.",
                    [$customer_data]
                );
                
                exit(_("Bad request! Please contact us!"));
            }
            
            if (empty($customer_data->transaction->merchantRefId) ||
                empty($customer_data->transaction->id)
            ) {
                status_header(400);
                
                $this->log_error(
                    "Missing transaction data.",
                    [$customer_data]
                );
                    
                exit(_("Bad request! Please contact us!"));
            }

            $transaction_token = intval(substr($customer_data->transaction->merchantRefId, 3));
            
            $transactions = Model_Whitelabel_Transaction::find([
                "where" => [
                    "whitelabel_id" => $this->whitelabel['id'],
                    "token" => $transaction_token
                ]
            ]);

            if ($transactions === null || count($transactions) == 0) {
                status_header(400);
                
                $this->log_error(
                    "Cannot find transaction.",
                    [$customer_data]
                );
                
                exit(_("Bad request! Please contact us!"));
            }
            
            $this->transaction = $transactions[0];
            
            if (!((int)$this->transaction->payment_method_type === Helpers_General::PAYMENT_TYPE_OTHER &&
                !empty($this->transaction->whitelabel_payment_method_id))
            ) {
                status_header(400);
                
                $this->log_error(
                    "Bad payment type.",
                    [$customer_data]
                );

                exit(_("Bad request! Please contact us!"));
            }

            $this->log_info(
                "Downloaded payment/customer data.",
                [$customer_data]
            );
            
            $additional_data = unserialize($this->transaction->additional_data);
            
            $this->data = $this->prepare_data(
                $additional_data,
                $json,
                $customer_data
            );

            $this->out_id = $customer_data->transaction->id;
            
            $event_array = ["payment_cancelled", "payment_declined", "payment_failed"];
            
            if (in_array($json->eventType, $event_array)) {
                $this->transaction->set([
                    'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                    "transaction_out_id" => $this->out_id,
                    'additional_data' => serialize($this->data)
                ]);
                $this->transaction->save();
                
                $this->log_warning("Status: " . $json->eventType);
            } elseif ($json->eventType == "payment_succeeded") {
                $this->ok = true;
                
                $this->log_success("Confirmation received successfully.");
            }
        } catch (Exception $e) {
            status_header(400);
            
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
    public function confirm_all(): void
    {
        // let's check for unfinished Neteller transactions from within 24h
        $transactions = Model_Whitelabel_Transaction::get_unfinished_or_with_error(
            null,
            Helpers_General::PAYMENT_TYPE_OTHER,
            Helpers_Payment_Method::NETELLER
        );
        
        foreach ($transactions as $transaction) {
            try {
                $this->set_whitelabel_id((int)$transaction['whitelabel_id']);
                $this->set_transaction_id((int)$transaction['id']);
                
                if (!empty($transaction['whitelabel_payment_method_id'])) {
                    $whitelabel_payment_method_id = (int)$transaction['whitelabel_payment_method_id'];
                    
                    $this->set_whitelabel_payment_method_id($whitelabel_payment_method_id);
                    
                    $message = "TEST - just set whitelabel_payment_method_id: " .
                        $whitelabel_payment_method_id;
                    
                    $this->log_info($message);
                } else {
                    $message = "No whitelabel_payment_method_id set within transaction";
                    $this->log_error($message);
                    continue;
                }
                
                $full_token = $transaction['prefix'];
                if ((int)$transaction['type'] === 0) {
                    $full_token .= 'P';
                } else {
                    $full_token .= 'D';
                }
                $full_token .= $transaction['token'];
                
                $payment_data = unserialize($transaction['data']);
                
                $check_client = $this->check_client_on_neteller(
                    $payment_data,
                    false,
                    false
                );
                
                if (is_null($check_client)) {
                    $message = "Empty check client?";
                    $this->log_error(
                        $message
                    );
                    
                    continue;
                }
                
                if (isset($check_client->error) &&
                    !empty($check_client->error)
                ) {
                    $message = "Could not connect to Neteller gateway.";
                    $this->log_error(
                        $message,
                        [$check_client]
                    );
                    
                    continue;
                }
                
                if (isset($check_client->accessToken) &&
                    !empty($check_client->accessToken)
                ) {
                    $token = $check_client->accessToken;
                    
                    $customer_payment_data = $this->get_payments(
                        $payment_data,
                        $full_token,
                        $token
                    );
                    
                    if (isset($customer_payment_data->error) &&
                        !empty($customer_payment_data->error)
                    ) {
                        $this->log_error(
                            "Neteller error while fetching payment.",
                            [$customer_payment_data]
                        );
                        continue;
                    }
                    
                    if (empty($customer_payment_data->transaction->merchantRefId) ||
                        empty($customer_payment_data->transaction->id)
                    ) {
                        $this->log_error(
                            "Missing transaction data.",
                            [$customer_payment_data]
                        );
                        continue;
                    }
                    
                    $this->log_info(
                        "Downloaded payment/customer data.",
                        [$customer_payment_data]
                    );
                    
                    $transaction_additional_data = unserialize($transaction['additional_data']);
                    
                    $additional_data = $this->prepare_data(
                        $transaction_additional_data,
                        null,
                        $customer_payment_data,
                        false
                    );
                    
                    $this->out_id = $customer_payment_data->transaction->id;
                    
                    switch ($customer_payment_data->transaction->status) {
                        case "cancelled":
                        case "declined":
                        case "failed":
                            $transaction_model = Model_Whitelabel_Transaction::find_by_pk($transaction['id']);
                            $transaction_model->set([
                                'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                                "transaction_out_id" => $this->out_id,
                                'additional_data' => serialize($additional_data)
                            ]);
                            $transaction_model->save();
                            
                            $error_message = "Status: " .
                                $customer_payment_data->transaction->status;
                            $this->log_error($error_message);
                            
                            break;
                        case "accepted":
                            $whitelabel = Model_Whitelabel::find_by_pk($transaction['whitelabel_id'])->to_array();
                            $transaction = Model_Whitelabel_Transaction::find_by_pk($transaction['id']);
                            
                            $accept_transaction_result = Lotto_Helper::accept_transaction(
                                $transaction,
                                $this->out_id,
                                $additional_data,
                                $whitelabel
                            );

                            // Now transaction returns result as INT value and
                            // we can redirect user to fail page or success page
                            // or simply inform system about that fact
                            if ($accept_transaction_result === Forms_Transactions_Accept::RESULT_WITH_ERRORS) {
                                $message = "An error happened during the process of accept_transaction.";
                                $this->log_error($message);
                                break;
                            }
                            
                            $message = "Payment confirmed successfully.";
                            $this->log_success($message);
                            break;
                        default:
                            break;
                    }
                }
            } catch (\Exception $e) {
                $message = "Unknown error: " . $e->getMessage();
                $this->log_error($message);
                $this->fileLoggerService->error(
                    $message . $e->getTraceAsString()
                );
            }
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
        // NOT QUITE SURE IF IT IS OK
        $this->process_confirmation();
        
        $transaction = $this->get_transaction();
        $data = $this->get_data();
        $out_id = $this->get_out_id();
        $ok = $this->get_ok();
                
        return $ok;
    }
}
