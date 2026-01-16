<?php

use Fuel\Core\Validation;
use Fuel\Core\Response;

/**
 * Description of Forms_Wordpress_Payment_TruevoCC
 *
 */
final class Forms_Wordpress_Payment_TruevoCC extends Forms_Wordpress_Payment_Base implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method;
    
    const PRODUCTION_BASE_URL = "https://truevo.eu/v1";
    const TEST_BASE_URL = "https://test.truevo.eu/v1";
    
    /**
     *
     * @var int
     */
    protected $payment_method = Helpers_Payment_Method::TRUEVOCC;

    /**
     * Request data
     *
     * @var array
     */
    private $request_data = [];
    
    /**
     *
     * @var array|null
     */
    private $response = null;
    
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
        $this->whitelabel = $whitelabel;
        $this->user = $user;
        $this->transaction = $transaction;
        $this->model_whitelabel_payment_method = $model_whitelabel_payment_method;
    }
    
    /**
     *
     * @return array|null
     */
    public function get_response():? array
    {
        return $this->response;
    }

    /**
     *
     * @param array|null $response
     * @return \Forms_Wordpress_Payment_TruevoCC
     */
    public function set_response(
        array $response = null
    ): Forms_Wordpress_Payment_TruevoCC {
        $this->response = $response;
        
        return $this;
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
        
        $url_part = 'https://';
        if (Helpers_General::is_test_env()) {
            $url_part .= 'lottopark.loc';
        } else {
            $url_part = lotto_platform_home_url_without_language();
        }
        
        $result_url = $url_part . "/order/result/" .
            Helpers_Payment_Method::TRUEVOCC_URI .
            "/" .
            $whitelabel_payment_method_id .
            "/";
        
        return $result_url;
    }
    
    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge("truevocc");
        
        return $validation;
    }
    
    /**
     * @return void
     */
    protected function check_merchant_settings(): void
    {
        $this->get_payment_data();
        
        if (empty($this->payment_data['truevocc_authorization_bearer']) ||
            empty($this->payment_data['truevocc_entity_id']) ||
            empty($this->payment_data['truevocc_brands'])
        ) {
            if (Helpers_General::is_test_env()) {
                exit("Wrong credentials for Whitelabel.");
            }
            
            status_header(400);
            
            $this->log_error("Wrong credentials for Whitelabel. Empty Truevo CC settings data.");
            exit($this->get_exit_text());
        }
    }
    
    /**
     *
     * @return void
     */
    private function save_data_for_transaction(): void
    {
        $token = $this->get_token_from_response();
        
        $this->get_transaction_by_token($token);
        
        $response = $this->get_response();
        
        $additional_data = $response !== null ? serialize($response) : null;
        
        $set = [
            'additional_data' => $additional_data
        ];
        $this->transaction->set($set);
        $this->transaction->save();
    }
    
    /**
     *
     * @return string
     */
    public function get_authorization_bearer(): string
    {
        $authorization_bearer = "";
        
        if (!empty($this->payment_data['truevocc_authorization_bearer'])) {
            $authorization_bearer = $this->payment_data['truevocc_authorization_bearer'];
        }
        
        return $authorization_bearer;
    }
    
    /**
     *
     * @return string
     */
    public function get_entity_id(): string
    {
        $entity_id = "";
        
        if (!empty($this->payment_data['truevocc_entity_id'])) {
            $entity_id = $this->payment_data['truevocc_entity_id'];
        }
        
        return $entity_id;
    }
    
    /**
     *
     * @return string
     */
    public function get_brands(): string
    {
        $brands = "";
        
        if (!empty($this->payment_data['truevocc_brands'])) {
            $brands = $this->payment_data['truevocc_brands'];
        }
        
        return $brands;
    }
    
    /**
     *
     * @return string
     */
    public function get_main_url(): string
    {
        $main_url = "";
        
        if (isset($this->payment_data['truevocc_test']) &&
            (int)$this->payment_data['truevocc_test'] === 1
        ) {
            $main_url = self::TEST_BASE_URL;
        } else {
            $main_url = self::PRODUCTION_BASE_URL;
        }
        
        return $main_url;
    }
    
    /**
     *
     * @param string $resource_path
     * @return string
     */
    public function get_url(string $resource_path): string
    {
        $main_url = $this->get_main_url();
        
        $url = $main_url . $resource_path;
        
        return $url;
    }
    
    /**
     *
     * @return string
     */
    public function get_payment_type(): string
    {
        // DB (Debit), RV (Reversal), RF (Refund), PA (Pre-authorization)
        $payment_type = "DB";
        
        return $payment_type;
    }
    
    /**
     *
     * @return string
     */
    public function get_amount_payment(): string
    {
        $amount = number_format($this->transaction->amount_payment, 2, ".", "");
        
        return $amount;
    }
    
    /**
     *
     * @return string
     */
    public function get_payment_currency_id(): string
    {
        $payment_currency_id = $this->transaction->payment_currency_id;
        
        return $payment_currency_id;
    }

    /**
     *
     * @return string
     */
    public function get_customer_email(): string
    {
        $email = $this->user["email"];
        
        return $email;
    }
    
    /**
     *
     * @return array
     */
    public function get_request_data(): array
    {
        $entity_id = $this->get_entity_id();
        
        $amount = $this->get_amount_payment();

        $payment_currency_id = $this->get_payment_currency_id();
        
        $currency_code = $this->get_payment_currency($payment_currency_id);
        
        $payment_type = $this->get_payment_type();

        $token = $this->get_prefixed_transaction_token();

        $email = $this->get_customer_email();
        
        $this->request_data["entityId"] = $entity_id;
        $this->request_data["amount"] = $amount;
        $this->request_data["currency"] = $currency_code;
        $this->request_data["paymentType"] = $payment_type;
        $this->request_data["merchantTransactionId"] = $token;
        $this->request_data["customer.email"] = $email;
        
        if (isset($this->payment_data['truevocc_test']) &&
            (int)$this->payment_data['truevocc_test'] === 1
        ) {
            $this->request_data["testMode"] = "INTERNAL";
        }
        
        return $this->request_data;
    }
    
    /**
     *
     * @return array
     */
    public function get_authorization_headers(): array
    {
        $authorization_bearer = $this->get_authorization_bearer();
        
        $headers = [
            'Authorization: Bearer ' . $authorization_bearer
        ];
        
        return $headers;
    }
    
    /**
     * Make request to API
     *
     * @param string $url
     * @param array $post_data
     * @param string $method POST or GET
     * @param array $headers array of headers
     * @return array|null
     */
    public function make_request(
        string $url,
        ?array $post_data = null,
        string $method = 'POST',
        array $headers = []
    ):? array {
        $ssl_verifypeer = 2;
        if (Helpers_General::is_development_env()) {
            $ssl_verifypeer = 0;
        }
        
        $curl = curl_init();
        
        curl_setopt($curl, CURLOPT_URL, $url);
        
        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));
        } else {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        }
        
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $ssl_verifypeer);
        
        if (!empty($headers)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($curl);

        if ($errno = curl_errno($curl)) {
            $error_message = curl_strerror($errno);
            
            if (Helpers_General::is_test_env()) {
                exit('TruevoCC cURL error. ' . $errno . ': ' . $error_message);
            }
            
            $this->log_error('TruevoCC cURL error. ' . $errno . ': ' . $error_message);
            
            status_header(400);
            exit($this->get_exit_text());
        }

        curl_close($curl);

        if ($response === false) {
            if (Helpers_General::is_test_env()) {
                exit('TruevoCC cURL error ;' . curl_error($curl));
            }
            $this->log_error('TruevoCC cURL error; ' . curl_error($curl));
            
            status_header(400);
            exit($this->get_exit_text());
        }

        if (isset($response['error'])) {
            if (Helpers_General::is_test_env()) {
                exit($response['error']);
            }
            $this->log_error($response['error'], ['request' => $this->request_data]);
            
            status_header(400);
            exit($this->get_exit_text());
        }

        return json_decode($response, true);
    }
    
    /**
     *
     * @param string $code
     * @return int
     */
    public function check_reponse(string $code): int
    {
        $result = Forms_Wordpress_Payment_Base::STATUS_ERROR;
        
        $successfully_processed_transactions_codes = Forms_Wordpress_Payment_TruevoCCcodes::get_successfully_processed_transactions_codes_keys();
        
        if (in_array($code, $successfully_processed_transactions_codes)) {
            return Forms_Wordpress_Payment_Base::STATUS_SUCCESS;
        }
        
        $pending_transaction_codes = Forms_Wordpress_Payment_TruevoCCcodes::get_pending_transaction_codes_keys();
        
        if (in_array($code, $pending_transaction_codes)) {
            return Forms_Wordpress_Payment_Base::STATUS_PENDING;
        }
        
        $reject_by_bank_codes = Forms_Wordpress_Payment_TruevoCCcodes::get_reject_by_bank_codes_keys();
        
        if (in_array($code, $reject_by_bank_codes)) {
            return Forms_Wordpress_Payment_Base::STATUS_ERROR;
        }
        
        $reject_communication_codes = Forms_Wordpress_Payment_TruevoCCcodes::get_reject_communication_codes_keys();
        
        if (in_array($code, $reject_communication_codes)) {
            return Forms_Wordpress_Payment_Base::STATUS_ERROR;
        }
        
        $reject_system_codes = Forms_Wordpress_Payment_TruevoCCcodes::get_reject_system_codes_keys();
        
        if (in_array($code, $reject_system_codes)) {
            return Forms_Wordpress_Payment_Base::STATUS_ERROR;
        }
        
        return $result;
    }
    
    /**
     *
     * @return string
     */
    public function get_code_from_response(): string
    {
        $response = $this->get_response();
        
        if (empty($response)) {
            if (Helpers_General::is_test_env()) {
                exit("Empty response given from cURL.");
            }
            $this->log_error('Empty response given from cURL.');
            
            status_header(400);
            exit($this->get_exit_text());
        }
        
        $code = $response['result']['code'];
        
        return $code;
    }
    
    /**
     *
     * @return string
     */
    public function get_checkout_id_from_response(): string
    {
        $response = $this->get_response();
        
        if (empty($response)) {
            if (Helpers_General::is_test_env()) {
                exit("Empty response given from cURL.");
            }
            $this->log_error('Empty response given from cURL.');
            
            status_header(400);
            exit($this->get_exit_text());
        }
        
        $checkout_id = $response['id'];
        
        return $checkout_id;
    }
    
    /**
     *
     * @param string $checkout_id
     * @return void
     */
    public function show_form(string $checkout_id): void
    {
        $token = $this->get_prefixed_transaction_token();
        
        $shopper_result_url = $this->get_result_url();

        $brands = $this->get_brands();
        
        $resource_path = "/paymentWidgets.js?checkoutId=" . $checkout_id;
        $script_url = $this->get_url($resource_path);
        
        $this->log_info('Show form with URL: ' . $script_url);
        
        $amount = $this->get_amount_payment();
        $payment_currency_id = $this->get_payment_currency_id();
        
        $payment_currency_tab = Helpers_Currency::get_mtab_currency(
            false,
            "",
            $payment_currency_id
        );
        
        $total_formatted = Lotto_View::format_currency(
            $amount,
            $payment_currency_tab['code'],
            true
        );
        
        $post_data = [
            "token" => $token,
            "shopper_result_url" => $shopper_result_url,
            "script_url" => $script_url,
            "brands" => $brands,
            "total_formatted" => $total_formatted
        ];
        
        $truevocc_view = View::forge("wordpress/payment/truevocc");
        $truevocc_view->set("post_data", $post_data);
        $truevocc_view->set("user_data", $this->user);
        
        ob_clean();
        
        echo $truevocc_view;
    }
    
    /**
     *
     * @return void
     */
    private function set_transaction_to_session(): void
    {
        if (!empty($this->transaction)) {
            Session::set('truevocc_transaction', $this->transaction);
        }
    }
    
    /**
     *
     * @return void
     */
    public function process_form(): void
    {
        $this->set_transaction_to_session();
        
        $this->log_info('Begin payment process.', [], true);
        
        $this->check_credentials();
        
        $this->check_merchant_settings();
        
        $resource_path = "/checkouts";
        $url = $this->get_url($resource_path);
        
        $this->get_payment_data();
        
        $request_data = $this->get_request_data();
        
        $authrization_headers = $this->get_authorization_headers();
        
        $this->log_info(
            'TruevoCC request data at begin process',
            [
                'request' => $request_data,
                'headers' => $authrization_headers,
                'url' => $url
            ]
        );
        
        $response = $this->make_request(
            $url,
            $request_data,
            'POST',
            $authrization_headers
        );
        
        $this->set_response($response);
        
        $this->log_info(
            'Response from server (step 1).',
            ['response_data' => $response]
        );
        
        $code = $this->get_code_from_response();
        
        // At this point the only accepted value is STATUS PENDING
        // or STATUS_ERROR, but STATUS_SUCCESS should not happened
        $check_response = $this->check_reponse($code);
        
        if ($check_response === Forms_Wordpress_Payment_Base::STATUS_SUCCESS) {
            if (Helpers_General::is_test_env()) {
                exit("Some errors happened during the communication process.");
            }
            
            $this->log_error(
                'At this point of the communication success status should not happened.'
            );
            
            status_header(400);
            exit($this->get_exit_text());
        } elseif ($check_response === Forms_Wordpress_Payment_Base::STATUS_ERROR) {
            if (Helpers_General::is_test_env()) {
                exit("Some errors happened during the communication process.");
            }
            
            $this->log_error(
                'Some errors happened during the communication process.'
            );
            
            status_header(400);
            exit($this->get_exit_text());
        }
        
        $checkout_id = $this->get_checkout_id_from_response();
        
        $this->save_payment_method_id_for_transaction();
        
        $this->show_form($checkout_id);
    }
    
    /**
     *
     * @return string
     */
    public function get_request_url_for_payment_status(): string
    {
        $entity_id = $this->get_entity_id();
        
        return "?entityId=".$entity_id;
    }
    
    /**
     *
     * @return string
     */
    private function get_out_id(): string
    {
        $response = $this->get_response();
        
        if (empty($response)) {
            if (Helpers_General::is_test_env()) {
                exit("Empty response given from cURL.");
            }
            $this->log_error('Empty response given from cURL.');
            
            status_header(400);
            exit($this->get_exit_text());
        }
        
        $out_id = $response['id'];
        
        return $out_id;
    }
    
    /**
     *
     * @return string
     */
    private function get_token_from_response(): string
    {
        $response = $this->get_response();
        
        if (empty($response)) {
            if (Helpers_General::is_test_env()) {
                exit("Empty response given from cURL.");
            }
            $this->log_error('Empty response given from cURL.');
            
            status_header(400);
            exit($this->get_exit_text());
        }
        
        $prefix = $this->whitelabel['prefix'];
        
        $regex = "/(" . $prefix . "[P|D]{1}[1-9]{1}[0-9]{8})+/";
        
        if (empty($response['merchantTransactionId'])) {
            if (Helpers_General::is_test_env()) {
                exit("No merchantTransactionId set.");
            }
            $this->log_error('No merchantTransactionId set.', $response);
            
            status_header(400);
            exit($this->get_exit_text());
        }
        
        $descriptor = $response['merchantTransactionId'];
        
        $token = "";
        if (preg_match($regex, $descriptor, $match)) {
            $token = $match[0];
        } else {
            if (Helpers_General::is_test_env()) {
                exit("No token found.");
            }
            $this->log_error('No token found in response descriptor.', $response);
            
            status_header(400);
            exit($this->get_exit_text());
        }
        
        return $token;
    }
    
    /**
     *
     * @param string|null $token
     * @return \Model_Whitelabel_Transaction|null
     */
    protected function get_transaction_by_token(
        string $token = null
    ):? Model_Whitelabel_Transaction {
        $response = $this->get_response();
        
        if (empty($response)) {
            if (Helpers_General::is_test_env()) {
                exit("Empty response given from cURL.");
            }
            $this->log_error('Empty response given from cURL.');
            
            status_header(400);
            exit($this->get_exit_text());
        }
        
        if (empty($token)) {
            if (Helpers_General::is_test_env()) {
                exit("Lack of token given.");
            }
            $this->log_error('No token given.', $response);

            status_header(400);
            exit($this->get_exit_text());
        }
        
        $token_int = intval(substr($token, 3));
        
        $transaction = Model_Whitelabel_Transaction::find_one_by([
            "whitelabel_id" => (int)$this->whitelabel['id'],
            "token" => $token_int
        ]);
        
        if (empty($transaction)) {
            if (Helpers_General::is_test_env()) {
                exit("Lack of token given.");
            }
            $this->log_error('No transaction with given token.', $response);

            status_header(400);
            exit($this->get_exit_text());
        }
        
        $this->transaction = $transaction;
        
        return $this->transaction;
    }
    
    /**
     *
     * @param array $response
     * @return void
     */
    private function accept_transaction_data(array $response = null): void
    {
        $out_id = $this->get_out_id();
        
        $data = $this->get_response();
        
        $accept_transaction_result = Lotto_Helper::accept_transaction(
            $this->transaction,
            $out_id,
            $data,
            $this->whitelabel
        );
        
        // Now transaction returns result as INT value and
        // we can redirect user to fail page or success page
        // or simply inform system about that fact
        if ($accept_transaction_result === Forms_Transactions_Accept::RESULT_WITH_ERRORS) {
            $this->log_error('There are some errors happened durning accept transaction process.', $response);
            
            $this->save_data_for_transaction();
            
            status_header(400);
            exit($this->get_exit_text());
        }
    }
    
    /**
     *
     * @return bool
     */
    private function check_transaction_approved(): bool
    {
        if (empty($this->transaction)) {
            return false;
        }
        if ((int)$this->transaction->status === Helpers_General::STATUS_TRANSACTION_APPROVED) {
            return true;
        }
        return false;
    }

    private function redirect_to_success_page()
    {
        Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_SUCCESS));
    }
    
    /**
     *
     * @return void
     */
    public function final_check_and_redirect(): void
    {
        $response = $this->get_response();
        
        if (empty($response)) {
            if (Helpers_General::is_test_env()) {
                exit("Empty response given from cURL.");
            }
            $this->log_error('Empty response given from cURL.', $response);
            
            status_header(400);
            exit($this->get_exit_text());
        }
        
        $code = $this->get_code_from_response();
        
        $check_response = $this->check_reponse($code);

        $token = $this->get_token_from_response();
        
        $this->get_transaction_by_token($token);
        
        if ($this->check_transaction_approved()) {
            $this->log_info('Transaction has already been processed and approved (check previous logs).');
            $this->redirect_to_success_page();
        }
        
        switch ($check_response) {
            case Forms_Wordpress_Payment_Base::STATUS_SUCCESS:
                $this->accept_transaction_data($response);
        
                $this->log_success("Payment successful.", $response);
                
               $this->redirect_to_success_page();
                break;
            case Forms_Wordpress_Payment_Base::STATUS_PENDING:          // This should not happened
                if (Helpers_General::is_test_env()) {
                    exit("There is something wrong with communication.");
                }
                $this->log_error('This should not happened at this state of payment.', $response);

                $this->save_data_for_transaction();
                
                status_header(400);
                exit($this->get_exit_text());
                break;
            case Forms_Wordpress_Payment_Base::STATUS_ERROR:
                if (Helpers_General::is_test_env()) {
                    exit("There is something wrong with communication.");
                }
                $this->log_error('There are some errors happened on payment page.', $response);
                
                $this->save_data_for_transaction();
                
                Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
                break;
        }
    }
    
    /**
     *
     * @return void
     */
    public function log_transaction_is_cancelled(): void
    {
        if (!empty($this->transaction)) {
            $this->log_error('Transaction cancelled by user.');
        }
    }
    
    /**
     *
     * @return void
     */
    private function check_session_transaction(): void
    {
        if (!empty(Session::get('truevocc_transaction'))) {
            $truevocc_transaction = Session::get('truevocc_transaction');
            if (empty($this->transaction)) {
                $this->set_transaction($truevocc_transaction);
            }
            Session::delete('truevocc_transaction');
        }
    }
    
    /**
     *
     * @param array $get_method_data
     * @return void
     */
    public function process_checking(array $get_method_data = null): void
    {
        $this->check_session_transaction();
        
        $this->log_info('Begin of the process of checking payment.', [], true);
        
        if (empty($get_method_data)) {
            if (Helpers_General::is_test_env()) {
                exit("Empty get_method_data variable (GET parameters).");
            }
            $this->log_error('Empty get_method_data variable (GET parameters).');
            
            status_header(400);
            exit($this->get_exit_text());
        }
        
        $id_of_payment_full = $get_method_data['id'];
        
        if (empty($id_of_payment_full)) {
            if (Helpers_General::is_test_env()) {
                exit("No ID sent.");
            }
            $this->log_error('No ID sent.');
            
            status_header(400);
            exit($this->get_exit_text());
        }
        
        $checkout_id = $id_of_payment_full;
        
        $this->check_merchant_settings();
        
        $resource_path = '/checkouts/' . $checkout_id . '/payment';
        
        $url_query = $this->get_request_url_for_payment_status();
        
        $url = $this->get_url($resource_path . $url_query);
        
        $this->log_info('Last step URL (request URL): ' . $url);
        
        $this->log_info('Check of payment status.');
        
        $authrization_headers = $this->get_authorization_headers();
        $response = $this->make_request($url, null, 'GET', $authrization_headers);
        
        $this->set_response($response);
        
        $this->log_info('Response from server (step 3).', ['response_data' => $response]);
        
        $this->final_check_and_redirect();
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
        
        return $ok;
    }
}
