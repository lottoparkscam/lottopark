<?php

use Fuel\Core\Validation;
use Fuel\Core\Response;
use Services\PaymentService;
use Services\Logs\FileLoggerService;

final class Forms_Wordpress_Payment_Bitbaypay extends Forms_Main implements Forms_Wordpress_Payment_Process
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
     *
     * @var string
     */
    private $bitbaypay_api_url = '';
    
    /**
     * Payment credentials
     * @var array
     */
    private $payment_data = [];
    
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
     * @var string
     */
    private $confirmation_url = "";
    
    /**
     *
     * @var int
     */
    private $current_timestamp = 0;
    
    /**
     * This data is JSON encoded array of prepared data
     *
     * @var null|string
     */
    private $post_data = null;
    
    /**
     *
     * @var bool
     */
    private $is_notification_request = false;
    
    /**
     *
     * @var string
     */
    private $bitbay_payment_id = '';
    
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
     * @return string
     */
    public function get_main_url_part(): string
    {
        $url_part = 'https://';
        if (\Fuel::$env === \Fuel::TEST) {
            $url_part .= 'lottopark.loc';
        } else {
            /** @var PaymentService $paymentService */
            $paymentService = Container::get(PaymentService::class);
            $url_part = $paymentService->getPaymentConfirmationBaseUrl();
        }
        
        return $url_part;
    }
    
    /**
     *
     * @return void
     */
    public function set_success_url(): void
    {
        $this->success_url = lotto_platform_home_url(Helper_Route::ORDER_SUCCESS);
    }
    
    /**
     *
     * @return void
     */
    public function set_failure_url(): void
    {
        $this->failure_url = lotto_platform_home_url(Helper_Route::ORDER_FAILURE);
    }
    
    /**
     *
     * @return void
     */
    public function set_confirmation_url(): void
    {
        $url_part = $this->get_main_url_part();
        
        $whitelabel_payment_method_id = $this->get_whitelabel_payment_method_id();
        if (empty($whitelabel_payment_method_id)) {
            $this->log_error("Lack of whitelabel_payment_method_id!");
            exit(_("Bad request! Please contact us!"));
        }
        
        $this->confirmation_url = $url_part . Helper_Route::ORDER_CONFIRM .
            Helpers_Payment_Method::BITBAYPAY_URI . "/" .
            $whitelabel_payment_method_id . '/';
    }
    
    /**
     *
     * @return void
     */
    public function set_current_timestamp(): void
    {
        $this->current_timestamp = time();
    }
    
    /**
     *
     * @param array $whitelabel
     * @return \Forms_Wordpress_Payment_Bitbaypay
     */
    public function set_whitelabel(array $whitelabel): Forms_Wordpress_Payment_Bitbaypay
    {
        $this->whitelabel = $whitelabel;
        return $this;
    }

    /**
     * Set Transaction
     *
     * @param Model_Whitelabel_Transaction $transaction
     */
    public function set_transaction(
        Model_Whitelabel_Transaction $transaction = null
    ): Forms_Wordpress_Payment_Bitbaypay {
        if (empty($transaction)) {
            status_header(400);
            $this->log_error("Bad request.");
            exit($this->get_exit_text());
        }
        
        $this->transaction = $transaction;
        
        return $this;
    }
    
    /**
     *
     * @return array
     */
    public function get_whitelabel(): array
    {
        return $this->whitelabel;
    }

    /**
     *
     * @return bool
     */
    public function get_is_notification_request(): bool
    {
        return $this->is_notification_request;
    }

    /**
     *
     * @param bool $is_notification_request
     * @return \Forms_Wordpress_Payment_Bitbaypay
     */
    public function set_is_notification_request(
        bool $is_notification_request
    ): Forms_Wordpress_Payment_Bitbaypay {
        $this->is_notification_request = $is_notification_request;
        
        return $this;
    }

    /**
     *
     * @return string
     */
    public function get_bitbay_payment_id(): string
    {
        return $this->bitbay_payment_id;
    }

    /**
     *
     * @param string $bitbay_payment_id
     * @return \Forms_Wordpress_Payment_Bitbaypay
     */
    public function set_bitbay_payment_id(
        string $bitbay_payment_id
    ): Forms_Wordpress_Payment_Bitbaypay {
        $this->bitbay_payment_id = $bitbay_payment_id;
        
        return $this;
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
            Helpers_Payment_Method::BITBAYPAY,
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
     * Logs Bitbay API error response
     *
     * @param string $message
     * @param array $data
     * @return void
     */
    private function log_error_bitbay(string $message, array $data = []): void
    {
        $this->log('BitBay error response: ' . $message, Helpers_General::TYPE_ERROR, $data);
    }
    
    /**
    * Set Payment Params
    *
    *
    * @param Model_Whitelabel_Payment_Method $model_whitelabel_payment_method
    * @return \Forms_Wordpress_Payment_Bitbaypay
    */
    public function set_model_whitelabel_payment_method(
        Model_Whitelabel_Payment_Method $model_whitelabel_payment_method = null
    ): Forms_Wordpress_Payment_Bitbaypay {
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
        return $this->model_whitelabel_payment_method;
    }
    
    /**
     * Get Payment Params
     *
     * @param Model_Whitelabel_Transaction $transaction
     * @return Model_Whitelabel_Payment_Method
     */
    public function set_model_whitelabel_payment_method_by_transaction(
        Model_Whitelabel_Transaction $transaction
    ):? Forms_Wordpress_Payment_Bitbaypay {
        $this->model_whitelabel_payment_method = Model_Whitelabel_Payment_Method::find_by_pk(
            $transaction->whitelabel_payment_method_id
        );
        
        return $this;
    }
    
    /**
     *
     * @return string
     */
    public function get_api_url(): string
    {
        $this->bitbaypay_api_url = 'https://api.bitbaypay.com/rest/bitbaypay/payments';
        
        // For notification $api_url is different
        $is_notification_request = $this->get_is_notification_request();
        if ($is_notification_request) {
            $bitbay_payment_id = $this->get_bitbay_payment_id();
            $this->bitbaypay_api_url .= '/' . $bitbay_payment_id;
        }
        
        return $this->bitbaypay_api_url;
    }
    
    /**
     *
     * @return null|array
     */
    public function get_payment_data():? array
    {
        $model_whitelabel_payment_method = $this->get_model_whitelabel_payment_method();
        
        $payment_data = unserialize($model_whitelabel_payment_method['data']);
        
        if (empty($payment_data['marchant_bitbaypay_public_api_key']) ||
            empty($payment_data['marchant_bitbaypay_private_api_key'])
        ) {
            $this->log_error('Missing payment credentials');
            Session::set("message", ["error", _("Please select another payment method.")]);
            
            return null;
        }
        
        $this->payment_data = [
            'public_api_key' => $payment_data['marchant_bitbaypay_public_api_key'],
            'private_api_key' => $payment_data['marchant_bitbaypay_private_api_key'],
        ];
        
        return $this->payment_data;
    }
    
    /**
     *
     * @return string
     */
    public function get_success_url(): string
    {
        return $this->success_url;
    }
    
    /**
     *
     * @return string
     */
    public function get_failure_url(): string
    {
        return $this->failure_url;
    }
    
    /**
     *
     * @return string
     */
    public function get_confirmation_url(): string
    {
        return $this->confirmation_url;
    }
    
    /**
     *
     * @return int
     */
    public function get_current_timestamp(): int
    {
        return $this->current_timestamp;
    }
    
    /**
     *
     * @param string $token_full
     * @return int
     */
    public function get_token(string $token_full): int
    {
        $token_int = intval(substr($token_full, 3));
        
        return $token_int;
    }
    
    /**
     * Get transaction and check if exist
     *
     * @param int $token_int
     * @return null|array
     */
    public function get_transaction(int $token_int):? Model_Whitelabel_Transaction
    {
        $transaction = Model_Whitelabel_Transaction::find([
            "where" => [
                "whitelabel_id" => $this->whitelabel['id'],
                "token" => $token_int
            ]
        ]);

        if (!isset($transaction[0]['id'])) {
            $message = 'Transaction with token ' . $token_int . ' does not exist';
            $this->log_error($message, ['post_json' => Input::json()]);
            return null;
        }
        
        $this->transaction = $transaction[0];
        
        return $this->transaction;
    }
    
    /**
     *
     * @return array
     */
    public function get_transaction_data_to_send():? string
    {
        if (is_null($this->post_data)) {
            $currency_code = $this->get_payment_currency($this->transaction->payment_currency_id);
            $amount = (float)$this->transaction->amount_payment;
            $order_id = $this->get_prefixed_transaction_token();

            $success_url = $this->get_success_url();
            $failure_url = $this->get_failure_url();
            $notifications_url = $this->get_confirmation_url();

            $transaction_data_to_send = [
                'destinationCurrency' => $currency_code,
                'price' => $amount,
                'orderId' => $order_id,
                'successCallbackUrl' => $success_url,
                'failureCallbackUrl' => $failure_url,
                'notificationsUrl' => $notifications_url
            ];

            $this->post_data = json_encode($transaction_data_to_send);
        }
        
        return $this->post_data;
    }
    
    /**
     *
     * @return string
     */
    public function get_hash_hmac():? string
    {
        $credentials_set = $this->get_payment_data();
        
        if (is_null($credentials_set)) {
            return null;
        }
        
        $public_key = $credentials_set['public_api_key'];
        $private_key = $credentials_set['private_api_key'];
        
        $current_timestamp = $this->get_current_timestamp();
        
        $transaction_data_to_send_json = '';
        $is_notification_request = $this->get_is_notification_request();
        if (!$is_notification_request) {
            $transaction_data_to_send_json = $this->get_transaction_data_to_send();
        }
                
        $data_merged = $public_key . $current_timestamp . $transaction_data_to_send_json;
        
        $hash_hmac = hash_hmac('sha512', $data_merged, $private_key);
        
        return $hash_hmac;
    }
    
    /**
     *
     * @return string
     */
    public function get_UUID_v4(): string
    {
        $uuid = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
        
        return $uuid;
    }
    
    /**
     *
     * @param array $request_headers
     * @return void
     */
    private function save_transaction_data(array $request_headers): void
    {
        $additional_data = [];
        $additional_data['requestHeaders'] = $request_headers;
        $additional_data['requestData'] = $this->post_data;
        $additional_data['paymentParams'] = $this->model_whitelabel_payment_method->to_array();
        
        $set = [
            'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
            'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id'],
            'additional_data' => serialize($additional_data),
        ];
        
        $this->transaction->set($set);
        $this->transaction->save();
    }
    
    /**
     *
     * @return array
     */
    public function get_request_headers():? array
    {
        $request_headers = [];
        
        $credentials_set = $this->get_payment_data();
        
        if (is_null($credentials_set)) {
            return null;
        }
        
        $public_key = $credentials_set['public_api_key'];
        
        $api_hash = $this->get_hash_hmac();
        
        $operation_id = $this->get_UUID_v4();
        
        $request_timestamp = $this->get_current_timestamp();
        
        $content_type = "application/json";
        
        $request_headers[] = 'API-Key: ' . $public_key;
        $request_headers[] = 'API-Hash: ' . $api_hash;
        $request_headers[] = 'operation-id: ' . $operation_id;
        $request_headers[] = 'Request-Timestamp: ' . $request_timestamp;
        $request_headers[] = 'Content-Type: ' . $content_type;
        
        $this->log_info('Header request content. ', $request_headers);
        
        return $request_headers;
    }
    
    /**
     *
     * @return mixed
     */
    public function get_make_curl_request()
    {
        $api_url = $this->get_api_url();
        
        $request_headers = $this->get_request_headers();
        
        if (is_null($request_headers)) {
            return null;
        }
        
        $post_data = '';
        $is_notification_request = $this->get_is_notification_request();
        if (!$is_notification_request) {
            $post_data = $this->get_transaction_data_to_send();
        
            // Save transaction data before call of the CURL
            $this->save_transaction_data($request_headers);
        }
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $api_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);
        
        if (!$is_notification_request) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        } else {
            curl_setopt($curl, CURLOPT_POST, false);
        }
        
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        
        if ($errno = curl_errno($curl)) {
            $error_message = curl_strerror($errno);
            $this->log_error('BitBay cURL error. ' . $errno . ': ' . $error_message);
        }
        
        curl_close($curl);
        
        if ($response === false) {
            $this->log_error('BitBay cURL error ;' . curl_error($curl));
        }
        
        if (isset($response['error'])) {
            $this->log_error_bitbay($response['error'], ['request' => $post_data]);
        }
        
        return json_decode($response, true);
    }
    
    /**
     *
     * @return void
     */
    public function process_form(): void
    {
        $this->set_success_url();
        
        $this->set_failure_url();
        
        $this->set_confirmation_url();
        
        $this->set_current_timestamp();
        
        $this->set_is_notification_request(false);
        
        $response = $this->get_make_curl_request();
        
        if (is_null($response)) {
            Response::redirect(lotto_platform_home_url('/order/'));
        }
        
        $result_status = "OK";
        
        if (!empty($response['status'])) {
            $result_status = strtoupper($response['status']);
        } else {
            $this->log_error(
                "An error occured during the attemt of " .
                "communication with API of BitBayPay page."
            );
            
            Session::set("message", ["error", _('Security error! Please contact us!')]);
            
            Response::redirect(lotto_platform_home_url('/order/'));
        }
        
        switch ($result_status) {
            case 'FAIL':
                $errors = [];
                if (isset($response['errors']) && !empty($response['errors'])) {
                    $errors = ['reasons' => $response['errors']];
                }
                $this->log_error(
                    "An error occured during the attemt of " .
                    "communication with API of BitBayPay page.",
                    $errors
                );
                Session::set("message", ["error", _('Security error! Please contact us!')]);
                
                Response::redirect(lotto_platform_home_url('/order/'));
                break;
            case 'OK':
                $post_data = $this->get_transaction_data_to_send();
                
                $this->log(
                    'Redirecting to BitBayPay',
                    Helpers_General::TYPE_INFO,
                    [
                        'request' => $post_data,
                        'paymentId' => $response['data']['paymentId']
                    ]
                );
                
                if (isset($response['data']['url'])) {
                    Response::redirect($response['data']['url']);
                } else {
                    Response::redirect(lotto_platform_home_url('/order/'));
                }
                break;
        }
    }
    
    /**
     *
     * @param array $data
     * @return int
     */
    private function check_payment_data(array $data): int
    {
        $currency_code_from_transaction = $this->get_payment_currency($this->transaction->payment_currency_id);
        
        $currency_code_from_bitbaypay = (string)$data['destinationCurrency'];
        
        if ($currency_code_from_transaction !== $currency_code_from_bitbaypay) {
            $message = "BitBayPay returns different currency code than in transaction. ";
            $message .= "Transaction currency code: " . $currency_code_from_transaction . ". ";
            $message .= "BitBayPay currency code: " . $currency_code_from_bitbaypay . ". ";
            $this->log_error($message);
            return Helpers_General::STATUS_TRANSACTION_ERROR;
        }
        
//        $amount_payment_from_transaction = $this->transaction->amount_payment;
        
//        $amount_from_bitbaypay = number_format((string)$data['amountInDestinationCurrency'], 2, ".", "");
        
        /*
         * Comment by @Tom: this is removed because BitBayPay allows greater and lesser values.
        if (bccomp($amount_payment_from_transaction, $amount_from_bitbaypay, 2) === 1) {
            $message = "BitBayPay returns lower payment amount than on transaction. ";
            $message .= "Transaction payment amount: " . $amount_payment_from_transaction . ". ";
            $message .= "BitBayPay amount: " . $amount_from_bitbaypay . ". ";
            $this->log_error($message);
            return Helpers_General::STATUS_TRANSACTION_ERROR;
        }
        */
        
        $status = strtoupper($data['status']);
        
        switch ($status) {
            case 'PENDING':
                return Helpers_General::STATUS_TRANSACTION_PENDING;
            case 'PAID':
            case 'COMPLETED':
                return Helpers_General::STATUS_TRANSACTION_APPROVED;
            default:
                return Helpers_General::STATUS_TRANSACTION_ERROR;
        }
    }
    
    /**
     *
     * @return int
     */
    private function get_data_from_payment_details_on_bitbaypay(): int
    {
        $this->set_is_notification_request(true);
        
        $response = $this->get_make_curl_request();

        if (is_null($response)) {
            $this->log_error(
                "BitBayPay - null response from server while check status after PAID.",
                Helpers_General::TYPE_ERROR
            );
            return Helpers_General::STATUS_TRANSACTION_ERROR;
        }
        
        $result_status = "OK";
        
        if (!empty($response['status'])) {
            $result_status = strtoupper($response['status']);
        } else {
            $this->log_error(
                "BitBayPay - empty status field from response while check status after PAID.",
                Helpers_General::TYPE_ERROR
            );
            return Helpers_General::STATUS_TRANSACTION_ERROR;
        }

        switch ($result_status) {
            case "OK":
                return $this->check_payment_data($response['data']);
            case "FAIL":
            default:
                $this->log_error(
                    "BitBayPay - got FAIL result from response while check status after PAID.",
                    Helpers_General::TYPE_ERROR
                );
                return Helpers_General::STATUS_TRANSACTION_ERROR;
        }
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
        $input_data = Input::json();
        
        if (empty($input_data)) {
            $this->log_error('Empty input.');
            return false;
        }
        
        if (empty($input_data['orderId'])) {
            $this->log_error('Empty orderId in input data.');
            return false;
        }
        
        $token_full = $input_data['orderId'];
        
        $this->log(
            'BitBay notify result, token: ' . $token_full,
            Helpers_General::TYPE_INFO,
            ['post_json' => $input_data]
        );
        
        if (empty($input_data['status'])) {
            $this->log_error('Empty status in input data.');
            return false;
        }
        
        $bitbay_status = $input_data['status'];
        
        if (empty($input_data['paymentId'])) {
            $this->log_error('Empty paymentId in input data.');
            return false;
        }
        
        $bitbay_payment_id = $input_data['paymentId'];
        
        $this->set_bitbay_payment_id($bitbay_payment_id);
        
        $token_int = $this->get_token($token_full);
        $transaction = $this->get_transaction($token_int);

        if (is_null($transaction)) {
            $this->log(
                'BitBay notify result for transaction which does not exist, token: ' . $token_full,
                Helpers_General::TYPE_INFO,
                ['post_json' => $input_data]
            );
            
            return false;
        }

        if ((int)$transaction->status === Helpers_General::STATUS_TRANSACTION_APPROVED) {
            // this is required because BitBay performs additional notify sending
            $this->log(
                'BitBay notify result for transaction which is already approved, token: ' . $token_full,
                Helpers_General::TYPE_INFO,
                ['post' => $input_data]
            );
            
            return true;
        }

        $this->set_current_timestamp();
        
        $this->set_model_whitelabel_payment_method_by_transaction($transaction);
        
        $transaction_additional_data = unserialize($transaction->additional_data);
        
        if ((string)$bitbay_status === 'COMPLETED') {
            // SUCCESS
            $this->log_success('BitBayPay transaction succeeded');
            
            return [
                'transaction' => $transaction,
                'out_id' => $bitbay_payment_id,
                'data' => $transaction_additional_data + ['result' => Input::json(), 'server' => $_SERVER]
            ];
        } elseif ((string)$bitbay_status === 'PENDING' ||
            (string)$bitbay_status === 'PAID'
        ) {
            $this->log(
                "BitBayPay payment result with " . $bitbay_status . " status",
                Helpers_General::TYPE_INFO
            );
            
            // Additional check
            $payment_status = $this->get_data_from_payment_details_on_bitbaypay();

            if ($payment_status === Helpers_General::STATUS_TRANSACTION_APPROVED) {
                // SUCCESS
                $this->log_success('BitBayPay transaction succeeded');
                
                return [
                    'transaction' => $transaction,
                    'out_id' => $bitbay_payment_id,
                    'data' => $transaction_additional_data + ['result' => Input::json(), 'server' => $_SERVER]
                ];
            }

            // PENDING or ERROR
            $transaction->set([
                'status' => $payment_status,
                'transaction_out_id' => $bitbay_payment_id
            ]);
            $transaction->save();
            
            return false;
        } else {
            $this->log_error("BitBayPay payment result with unknown status: " . $bitbay_status);
            
            $transaction->set([
                'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                'transaction_out_id' => $bitbay_payment_id
            ]);
            $transaction->save();
            
            return false;
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
