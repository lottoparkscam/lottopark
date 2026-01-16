<?php

use Fuel\Core\Validation;
use Fuel\Core\Response;

/**
 * Description of Forms_Wordpress_Payment_Astropaycard
 */
final class Forms_Wordpress_Payment_Astropaycard extends Forms_Wordpress_Payment_Base implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method;
    
    /**
     *
     * @var int
     */
    protected $payment_method = Helpers_Payment_Method::ASTRO_PAY_CARD;
    
    /**
     *
     * @var string
     */
    private $astropaycard_api_url = '';
    
    /**
     *
     * @var string
     */
    private $transtatus_url = '';
    
    /**
     *
     * @var string
     */
    private $validator_url = '';
    
    /**
     *
     * @var Validation
     */
    private $user_validation = null;
    
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
        $this->user_validation = $user_validation;
    }

    /**
     * @return Validation object
     */
    public function get_prepared_user_form(): Validation
    {
        $validation = Validation::forge('astropaycard');
        
        $validation->add("astropaycard.number", _("Card number"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule(["Lotto_Helper", "strip_spaces"])
            ->add_rule("required")
            ->add_rule('min_length', 12)
            ->add_rule('max_length', 19)
            ->add_rule('valid_string', ['numeric']);

        $validation->add("astropaycard.expmonth", _("Expiration date"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule("required")
            ->add_rule('exact_length', 2)
            ->add_rule('valid_string', ['numeric']);

        $validation->add("astropaycard.expyear", _("Expiration date"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule("required")
            ->add_rule('exact_length', 2)
            ->add_rule('valid_string', ['numeric']);

        $validation->add("astropaycard.cvv", _("CVV code"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule("required")
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 4)
            ->add_rule('valid_string', ['numeric']);

        if (!$validation->run()) {
            $this->errors = Lotto_Helper::generate_errors($validation->error());
        } else {
            if (!Lotto_Helper::is_valid_card(
                $validation->validated("astropaycard.expmonth"),
                $validation->validated("astropaycard.expyear")
            )
            ) {
                $this->errors = [
                    "astropaycard.expmonth" => _("This card looks expired, please check the expiration date.")
                ];
            }
        }
            
        return $validation;
    }
    
    /**
     *
     * @return array
     */
    public function get_general_communication_settings():? array
    {
        $this->get_payment_data();
        
        //General settings
        $general_settings = [
            "x_version" => "2.0",           //AstroPay API version (default "2.0")
            "x_delim_char" => "|",          //Field delimit character, the character that separates the fields (default "|")
            "x_test_request" => "N",        //Change to N for production
            "x_duplicate_window" => 120,    //Time window of a transaction with the sames values is taken as duplicated (default 120)
            "x_method" => "CC",
            "x_response_format" => "json"   //Response format: "string", "json", "xml" (default: string) (recommended: json)
        ];
        
        if ((int)$this->payment_data["astropaycard_test"] === 1) {
            $general_settings["x_test_request"] = "Y";
        }
        
        return $general_settings;
    }
    
    /**
     *
     * @return string
     */
    public function get_api_url(): string
    {
        $this->get_payment_data();
        
        // Sandbox API url
        if ((int)$this->payment_data["astropaycard_test"] === 1) {
            $this->astropaycard_api_url = 'https://sandbox-api.astropaycard.com/';
        } else {    // Live API URL
            $this->astropaycard_api_url = 'https://api.astropaycard.com/';
        }
        
        return $this->astropaycard_api_url;
    }
    
    /**
     *
     * @return string
     */
    public function get_transtatus_url(): string
    {
        $transtatus_url = "verif/transtatus";
        
        $api_url = $this->get_api_url();
        
        $this->transtatus_url = $api_url . $transtatus_url;
        
        return $this->transtatus_url;
    }

    /**
     *
     * @return string
     */
    public function get_validator_url(): string
    {
        $validator_url = "verif/validator";
        
        $api_url = $this->get_api_url();
        
        $this->validator_url = $api_url . $validator_url;
        
        return $this->validator_url;
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
            $this->log_error('Missing payment credentials');
            Session::set("message", ["error", _("Please select another payment method.")]);
            
            return null;
        }
        
        return $this->payment_data;
    }
    
    /**
     *
     * @return void
     */
    protected function check_merchant_settings(): void
    {
        $this->get_payment_data();
        
        if (empty($this->payment_data)) {
            if (Helpers_General::is_test_env()) {
                exit("Empty payment data.");
            }
            status_header(400);
            $this->log_error("Empty payment data.");
            exit($this->get_exit_text());
        }
        
        if (empty($this->payment_data['astropaycard_x_login']) ||
            empty($this->payment_data['astropaycard_x_trans_key']) ||
            empty($this->payment_data['astropaycard_secret_key'])
        ) {
            if (Helpers_General::is_test_env()) {
                exit("Wrong credentials for Whitelabel.");
            }
            
            status_header(400);
            
            $this->log_error("Wrong credentials for Whitelabel. Empty AstroPayCard settings data.");
            exit($this->get_exit_text());
        }
    }
    
    /**
     * Authorizes a transaction
     * @param string $x_card_num AstroPay Card number (16 digits)
     * @param string $x_card_code AstroPay Card security code (CVV)
     * @param string $x_exp_date AstroPay Card expiration date
     * @param double $x_amount Amount of the transaction
     * @param string $x_currency Currency of the transaction
     * @param string $x_unique_id Unique user ID of the merchant
     * @param string $x_invoice_num Merchant transaction identificator, i.e. the order number
     * @param array $additional_params Array of additional info that you would send to AstroPay for reference purpose.
     * @return array Array of params returned by AstroPay capture API. Please see section 3.1.3 "Response" of AstroPay Card integration manual for more info
     */
    public function auth_transaction(
        string $x_card_num,
        string $x_card_code,
        string $x_exp_date,
        float $x_amount,
        string $x_currency,
        string $x_unique_id,
        string $x_invoice_num,
        array $additional_params = null
    ):? array {
        $this->get_payment_data();
        
        $general_settings = $this->get_general_communication_settings();
        
        if (is_null($general_settings)) {
            return null;
        }
        
        $data = [];
        
        $data['x_login'] = $this->payment_data['astropaycard_x_login'];
        $data['x_tran_key'] = $this->payment_data['astropaycard_x_trans_key'];
        $data['x_type'] = "AUTH_CAPTURE";
        
        $data['x_card_num'] = $x_card_num;
        $data['x_card_code'] = $x_card_code;
        $data['x_exp_date'] = $x_exp_date;
        $data['x_amount'] = $x_amount;
        $data['x_currency'] = $x_currency;
        $data['x_unique_id'] = $x_unique_id;
        $data['x_invoice_num'] = $x_invoice_num;
        
        $data['x_version'] = $general_settings["x_version"];
        $data['x_test_request'] = $general_settings["x_test_request"];
        $data['x_duplicate_window'] = $general_settings["x_duplicate_window"];
        $data['x_method'] = $general_settings["x_method"];
        $data['x_delim_char'] = $general_settings["x_delim_char"];
        $data['x_response_format'] = $general_settings["x_response_format"];
        
        //Optional: Additional params
        if (is_array($additional_params)) {
            foreach ($additional_params as $key => $value) {
                $data[$key] = $value;
            }
        }

        $this->log_info('Request data array.', $data);
        
        $validator_url = $this->get_validator_url();
        
        $prepared_data = $this->prepare_data($data);
        $response = $this->make_request($validator_url, $prepared_data);

        return $response;
    }
    
    /**
     *
     * @param array $data
     * @return array
     */
    public function prepare_data(array $data): string
    {
        $result_data = '';
        $first = true;
        foreach ($data as $key => $value) {
            if (!$first) {
                $result_data .= '&';
            }
            $result_data .= "$key=$value";
            $first = false;
        }
        
        return $result_data;
    }
    
    /**
     *
     * @param string $url
     * @param string $post_data
     * @param string $method
     * @param array $headers
     * @return array
     */
    public function make_request(
        string $url,
        string $post_data = null,
        string $method = 'POST',
        array $headers = null
    ):? array {
        $ssl_verifypeer = 2;
        $ssl_verifyhost = 2;
        if (Helpers_General::is_development_env()) {
            $ssl_verifypeer = 0;
            $ssl_verifyhost = 0;
        }
        
        $curl = curl_init();
        
        curl_setopt($curl, CURLOPT_URL, $url);
        
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        } else {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        }
        
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $ssl_verifypeer);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $ssl_verifyhost);

        if (empty($headers)) {
            curl_setopt($curl, CURLOPT_HEADER, 0);
        } else {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        
        $response = curl_exec($curl);
        
        if ($errno = curl_errno($curl)) {
            $error_message = curl_strerror($errno);
            if (Helpers_General::is_test_env()) {
                exit('AstroPayCard cURL error. ' . $errno . ': ' . $error_message);
            }
            status_header(400);
            
            $this->log_error('AstroPayCard cURL error. ' . $errno . ': ' . $error_message);
            
            exit($this->get_exit_text());
        }

        curl_close($curl);

        if ($response === false) {
            if (Helpers_General::is_test_env()) {
                exit('AstroPayCard cURL error ;' . curl_error($curl));
            }
            status_header(400);
            
            $this->log_error('AstroPayCard cURL error ;' . curl_error($curl));
            exit($this->get_exit_text());
        }

        if (isset($response['error'])) {
            if (Helpers_General::is_test_env()) {
                exit($response['error']);
            }
            status_header(400);
            
            $this->log_error($response['error'], ['request' => $this->request]);
            exit($this->get_exit_text());
        }
        
        return json_decode($response, true);
    }
    
    /**
     *
     * @param Validation $form_values
     * @return string
     */
    public function get_expiration_date(Validation $form_values): string
    {
        $result = $form_values->validated('astropaycard.expmonth');
        $result .= '/20';
        $result .= $form_values->validated('astropaycard.expyear');
        
        return $result;
    }
    
    /**
     *
     * @param array $response
     * @return string
     */
    private function check_reponse(array $response = null): int
    {
        if (empty($response)) {
            if (Helpers_General::is_test_env()) {
                exit("Empty respose given from cURL.");
            }
            $this->log_error('Empty respose given from cURL.');
            
            status_header(400);
            exit($this->get_exit_text());
        }
        
        if ((int)$response['response_code'] !== 1) {
            return self::RESULT_WITH_ERRORS;
        }
        
        return self::RESULT_OK;
    }
    
    /**
     *
     * @param array $response
     * @return string
     */
    private function get_out_id(array $response = null): string
    {
        $out_id = $response['TransactionID'];
        
        return $out_id;
    }
    
    /**
     *
     * @param array $response
     * @return void
     */
    private function accept_transaction_data(array $response = null): void
    {
        $out_id = $this->get_out_id($response);
        
        $data = $response;
        
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
            $failure_url = $this->get_failure_url();
            
            $this->log_error('There are some errors happened in the process of accept transaction.', $response);
                
            Response::redirect($failure_url);
        }
    }
    
    /**
     *
     * @return string
     */
    public function get_success_url(): string
    {
        return lotto_platform_home_url(Helper_Route::ORDER_SUCCESS);
    }
    
    /**
     *
     * @return string
     */
    public function get_failure_url(): string
    {
        return lotto_platform_home_url(Helper_Route::ORDER_FAILURE);
    }
    
    /**
     *
     * @param array $response
     * @return void
     */
    private function final_check_and_redirect(array $response = null): void
    {
        if (empty($response)) {
            if (Helpers_General::is_test_env()) {
                exit("Empty respose given from cURL.");
            }
            $this->log_error('Empty respose given from cURL.', $response);
            
            status_header(400);
            exit($this->get_exit_text());
        }
        
        $check_response = $this->check_reponse($response);
        
        $success_url = $this->get_success_url();
        $failure_url = $this->get_failure_url();
        
        switch ($check_response) {
            case self::RESULT_OK:
                $this->accept_transaction_data($response);
        
                $this->log_success("Payment successful.", $response);
                
                Response::redirect($success_url);
                break;
            case self::RESULT_WITH_ERRORS:
                if (Helpers_General::is_test_env()) {
                    exit("There is something wrong with communication.");
                }
                $this->log_error('There are some errors happened on payment page.', $response);
                
                Response::redirect($failure_url);
                break;
        }
    }
    
    /**
     *
     * @param Validation $form_values
     * @return void
     */
    public function process_form(Validation $form_values): void
    {
        $this->log_info('Begin payment process.', [], true);
        
        $this->check_credentials();
        
        $this->check_merchant_settings();
        
        $card_number = $form_values->validated('astropaycard.number');
        
        $card_CVV_code = $form_values->validated('astropaycard.cvv');
        
        $expiration_date = $this->get_expiration_date($form_values);
        
        $amount = $this->transaction->amount_payment;
        
        $currency_code = $this->get_payment_currency($this->transaction->payment_currency_id);
        
        $user_token = $this->get_prefixed_user_token($this->user);
        
        $transaction_token = $this->get_prefixed_transaction_token();
        
        $this->save_payment_method_id_for_transaction();
        
        $response_auth_transaction = $this->auth_transaction(
            $card_number,
            $card_CVV_code,
            $expiration_date,
            $amount,
            $currency_code,
            $user_token,
            $transaction_token
        );
        
        $this->log_info('Response auth transaction from server.', ['response_auth_transaction' => $response_auth_transaction]);
        
        $this->final_check_and_redirect($response_auth_transaction);
    }

    /**
     *
     * @return void
     */
    public function create_payment(): void
    {
        $this->process_form($this->user_validation);
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
