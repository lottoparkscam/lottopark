<?php

use Fuel\Core\Session;
use Fuel\Core\Validation;
use Fuel\Core\Response;
use Helpers\UrlHelper;

/**
 * Description of Forms_Wordpress_Payment_VisaNet
 *
 */
final class Forms_Wordpress_Payment_VisaNet extends Forms_Wordpress_Payment_Base implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method;
    
    const PRODUCTION_BASE_URL = "https://apiProd.vnforapps.com";
    const TEST_BASE_URL = "https://apitestenv.vnforapps.com";

    const PRODUCTION_LIBRERJS_URL = "https://static-content.vnforapps.com/v2/js/checkout.js";
    const TEST_LIBRERJS_URL = "https://static-content-qas.vnforapps.com/v2/js/checkout.js?qa=true";
    
    /**
     *
     * @var int
     */
    protected $payment_method = Helpers_Payment_Method::VISANET;

    /**
     *
     * @var array
     */
    private $final_response = [];
    
    /**
     *
     * @var array
     */
    private $final_response_data = [];
    
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
     * @param array $response
     * @return \Forms_Wordpress_Payment_VisaNet
     */
    public function set_final_response(
        array $response = null
    ): Forms_Wordpress_Payment_VisaNet {
        if (!empty($response)) {
            $this->final_response = $response;
        }
        
        return $this;
    }
        
    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge("visanet");
        
        return $validation;
    }
    
    /**
     * @return void
     */
    protected function check_merchant_settings(): void
    {
        $this->get_payment_data();
        
        if (empty($this->payment_data['visanet_user']) ||
            empty($this->payment_data['visanet_password']) ||
            empty($this->payment_data['visanet_merchantid'])
        ) {
            if (Helpers_General::is_test_env()) {
                exit("Some or all credentials data are empty!");
            }
            
            status_header(400);
            
            $this->log_error("Some or all credentials data are empty!");
            exit($this->get_exit_text());
        }
    }
    
    /**
     *
     * @return string
     */
    public function get_main_url(): string
    {
        $main_url = "";
        
        if (isset($this->payment_data['visanet_test']) &&
            (int)$this->payment_data['visanet_test'] === 1
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
    public function get_authorization_credentials(): string
    {
        $this->get_payment_data();
        
        if (empty($this->payment_data['visanet_user']) ||
            empty($this->payment_data['visanet_password'])
        ) {
            if (Helpers_General::is_test_env()) {
                exit("Some or all credentials data are empty!");
            }
            status_header(400);
            
            $this->log_error("Some or all credentials data are empty!");
            exit($this->get_exit_text());
        }
        
        $user = $this->payment_data['visanet_user'];
        $password = $this->payment_data['visanet_password'];
        $authorization_credentials = base64_encode($user . ':' .$password);
        
        return $authorization_credentials;
    }
    
    /**
     *
     * @param array $get_method_data
     * @return string|null
     */
    private function get_token(array $get_method_data = null):? string
    {
        $token = null;
                
        if (!empty($get_method_data['token'])) {
            $token = $get_method_data['token'];
        }
        
        // In fact transaction means here transactionID
        if (empty($token) &&
            Session::get("transaction") != null &&
            is_numeric(Session::get("transaction"))
        ) {
            $this->get_transation_by_id_from_session(false);
            
            if (!empty($this->transaction)) {
                $token = $this->get_prefixed_transaction_token();
            }
        }
        
        if (empty($token)) {
            $transaction = Lotto_Settings::getInstance()->get('transaction');
            
            if (!empty($transaction)) {
                $this->transaction = $transaction;
                $token = $this->get_prefixed_transaction_token();
            }
        }
        
        return $token;
    }
    
    /**
     *
     * @return string
     */
    public function get_merchant_id(): string
    {
        $this->get_payment_data();
        
        if (empty($this->payment_data['visanet_merchantid'])) {
            if (Helpers_General::is_test_env()) {
                exit("Some or all credentials data are empty!");
            }
            status_header(400);
            
            $this->log_error("No merchantid set!");
            exit($this->get_exit_text());
        }
        
        return $this->payment_data['visanet_merchantid'];
    }
    
    /**
     *
     * @return string
     */
    public function get_client_ip(): string
    {
        $client_ip = Lotto_Security::get_IP();
        return $client_ip;
    }

    /**
     *
     * @return string
     */
    public function get_payment_amount(): string
    {
        if (empty($this->transaction)) {
            if (Helpers_General::is_test_env()) {
                exit("Transaction is empty!");
            }
            status_header(400);
            
            $this->log_error("Transaction is empty!");
            exit($this->get_exit_text());
        }
        
        $amount_payment = $this->transaction->amount_payment;
        
        return $amount_payment;
    }
    
    /**
     *
     * @return string
     */
    public function get_channel(): string
    {
        // Could be web, callcenter, recurrent
        $channel = "web";
        return $channel;
    }
    
    /**
     *
     * @return string
     */
    public function get_integration_text(): string
    {
        $integration_text = "Integraciones VisaNet";
        return $integration_text;
    }
    
    /**
     *
     * @return string
     */
    private function get_reqister_email(): string
    {
        $register_email = "";
        
        if (!empty($this->user) && !empty($this->user['email'])) {
            $register_email = $this->user['email'];
        }
        
        return $register_email;
    }
    
    /**
     *
     * @return string
     */
    private function is_email_confirmed(): string
    {
        $is_email_confirmed = 'NO';
        
        if (!empty($this->user)) {
            if ((int)$this->user['is_confirmed'] === 1) {
                $is_email_confirmed = 'SI';
            } else {
                $is_email_confirmed = 'NO';
            }
        }
        
        return $is_email_confirmed;
    }
    
    /**
     *
     * @return string
     */
    private function get_type_of_customer_register(): string
    {
        // I really don't know if it is OK,
        // other value is 'Invitado' - 'Invitation'
        // I left that as it is
        $type_of_customer_register = "Registrado";
        return $type_of_customer_register;
    }
    
    /**
     *
     * @return int
     */
    private function get_days_since_customer_registration(): int
    {
        $days_since_customer_registration = 0;
        
        if (!empty($this->user) && !empty($this->user['date_register'])) {
            $current_date = new DateTime("now", new DateTimeZone("UTC"));
            $register_date = $this->user['date_register'];
            
            $register_date_time = new DateTime($register_date, new DateTimeZone("UTC"));
        
            $interval = $current_date->diff($register_date_time);
            
            $days_since_customer_registration = (int)$interval->format('%a');
        }
        
        return $days_since_customer_registration;
    }
    
    /**
     *
     * @return string
     */
    private function get_frequency_text(): string
    {
        // I am not quite sure if this is OK or NOT
        // but according to informations from VisaNet
        // I entered that value as I got as example
        $frequency_text = "Frecuente";
        return $frequency_text;
    }
    
    /**
     *
     * @return string
     */
    private function get_user_phone(): string
    {
        $user_phone = "";
        
        if (!empty($this->user) &&
            !empty($this->user['phone']) &&
            !empty($this->user['phone_country'])
        ) {
            $user_phone = mb_substr($this->user['phone'], 0, 32);
        }
        
        return $user_phone;
    }
    
    /**
     * could be user email
     * @return string
     */
    private function get_client_id(): string
    {
        $client_id = "";
        
        $user_email = $this->get_reqister_email();
        
        if (!empty($user_email)) {
            $client_id = $user_email;
        }
        
        return $client_id;
    }
    
    /**
     *
     * @return string
     */
    private function get_document_type(): string
    {
        // I realy don't know what type of document should be entered here
        // All possible values are DNI|RUC|CE
        $document_type = "DNI";
        
        return $document_type;
    }
    
    /**
     *
     * @return string
     */
    private function get_document_number(): string
    {
        // As abouve I don't know what exactly number should be entered here
        // At this moment I left that empty
        $document_number = "";
        
        return $document_number;
    }
    
    /**
     *
     * @return string
     */
    private function get_customers_age(): string
    {
        $customers_age = "";
        
        if (!empty($this->user) && !empty($this->user['birthdate'])) {
            $current_date = new DateTime("now", new DateTimeZone("UTC"));
            
            $birth_date = $this->user['birthdate'];
            $birth_date_time = new DateTime($birth_date, new DateTimeZone("UTC"));
        
            $interval = $current_date->diff($birth_date_time);
            
            $customers_age = (int)$interval->format('%y');
        }
        
        return $customers_age;
    }
    
    /**
     * In last 6 months
     * @return int
     */
    private function get_number_of_customer_puchases(): int
    {
        $number_of_customer_puchases = 0;
        
        if (!empty($this->whitelabel) && !empty($this->user)) {
            $whitelabel_id = (int)$this->whitelabel['id'];
            $user_id = (int)$this->user['id'];
            $type = Helpers_General::TYPE_TRANSACTION_PURCHASE;
            
            $number_of_customer_puchases = Model_Whitelabel_Transaction::get_count_for_user_by_type(
                $whitelabel_id,
                $user_id,
                $type
            );
        }
        
        return $number_of_customer_puchases;
    }
    
    /**
     *
     * @return int
     */
    private function get_quantity_in_days_from_first_purchase(): int
    {
        $quantity_in_days_from_first_purchase = 0;
        
        if (!empty($this->user) && !empty($this->user['first_purchase'])) {
            $current_date = new DateTime("now", new DateTimeZone("UTC"));
            
            $first_purchase = $this->user['first_purchase'];
            $first_purchase_time = new DateTime($first_purchase, new DateTimeZone("UTC"));
        
            $interval = $current_date->diff($first_purchase_time);
            
            $quantity_in_days_from_first_purchase = (int)$interval->format('%a');
        }
        
        return $quantity_in_days_from_first_purchase;
    }
    
    /**
     *
     * @return int
     */
    private function get_quantity_in_days_since_last_purchase(): int
    {
        $quantity_in_days_since_last_purchase = 0;
        
        if (!empty($this->whitelabel) && !empty($this->user)) {
            $whitelabel_id = (int)$this->whitelabel['id'];
            $user_id = (int)$this->user['id'];
            $result = Model_Whitelabel_Transaction::get_last_purchase_date(
                $whitelabel_id,
                $user_id
            );
            
            if (!empty($result) && !empty($result[0])) {
                $current_date = new DateTime("now", new DateTimeZone("UTC"));
                $last_purchse_date = $result[0]['date'];
                $last_purchse_date_time = new DateTime($last_purchse_date, new DateTimeZone("UTC"));
        
                $interval = $current_date->diff($last_purchse_date_time);

                $quantity_in_days_since_last_purchase = (int)$interval->format('%a');
            }
        }
        
        return $quantity_in_days_since_last_purchase;
    }
    
    /**
     *
     * @return array|null
     */
    private function get_merchant_define_data():? array
    {
        $merchant_id = $this->get_merchant_id();
        $integration_text = $this->get_integration_text();
        $channel = $this->get_channel();
        
        $register_email = $this->get_reqister_email();
        $is_email_confirmed = $this->is_email_confirmed();
        $type_of_customer_register = $this->get_type_of_customer_register();
        $days_since_customer_registration = $this->get_days_since_customer_registration();
        
        $merchant_define_data = [
            "MDD1" => $merchant_id,                     // required
            "MDD2" => $integration_text,                // required
            "MDD3" => $channel,                         // required
            "MDD4" => $register_email,                  // required
            "MDD70" => $is_email_confirmed,             // required
            "MDD75" => $type_of_customer_register,      // required
            "MDD77" => $days_since_customer_registration,   // required
        ];
        
        $frequency_text = $this->get_frequency_text();
        if (!empty($frequency_text)) {
            $merchant_define_data["MDD21"] = $frequency_text;
        }
        
        $user_phone = $this->get_user_phone();
        if (!empty($user_phone)) {
            $merchant_define_data["MDD31"] = $user_phone;
        }
        
        // could be user email
        $client_id = $this->get_client_id();
        if (!empty($client_id)) {
            $merchant_define_data["MDD32"] = $client_id;
        }
        
        // I don't understand that value
        $document_type = $this->get_document_type();
        if (!empty($document_type)) {
            $merchant_define_data["MDD33"] = $document_type;
        }
        
        // I don't know what number should it be here
        $document_number = $this->get_document_number();
        if (!empty($document_number)) {
            $merchant_define_data["MDD34"] = $document_number;
        }
        
        $customers_age = $this->get_customers_age();
        if (!empty($customers_age)) {
            $merchant_define_data["MDD76"] = $customers_age;
        }
        
        // In last 6 months
        $number_of_customer_puchases = $this->get_number_of_customer_puchases();
        if (!empty($number_of_customer_puchases)) {
            $merchant_define_data["MDD86"] = $number_of_customer_puchases;
        }
        
        $quantity_in_days_from_first_purchase = $this->get_quantity_in_days_from_first_purchase();
        if (!empty($quantity_in_days_from_first_purchase)) {
            $merchant_define_data["MDD87"] = $quantity_in_days_from_first_purchase;
        }
        
        $quantity_in_days_since_last_purchase = $this->get_quantity_in_days_since_last_purchase();
        if (!empty($quantity_in_days_since_last_purchase)) {
            $merchant_define_data["MDD88"] = $quantity_in_days_since_last_purchase;
        }
        
        return $merchant_define_data;
    }
    
    /**
     *
     * @return string
     */
    public function get_communication_session_data(): string
    {
        $amount = $this->get_payment_amount();
        $client_ip = $this->get_client_ip();
        $channel = $this->get_channel();
        
        $merchant_define_data = $this->get_merchant_define_data();
        
        $data = [
            "amount" => $amount,
            "antifraud" => [
                "clientIp" => $client_ip,
                "merchantDefineData" => $merchant_define_data
            ],
            "channel" => $channel
        ];
        
        return json_encode($data);
    }
    
    /**
     *
     * @return array
     */
    public function get_authorization_headers(): array
    {
        $authorization_credentials = $this->get_authorization_credentials();
        
        $authorization_headers = [
            'Content-Type: application/json',
            'Authorization: Basic ' . $authorization_credentials
        ];
        
        return $authorization_headers;
    }
    
    /**
     *
     * @return array
     */
    public function get_authorization_headers_for_communication_session(
        ?string $secure_token = ""
    ): array {
        if (empty($secure_token)) {
            if (Helpers_General::is_test_env()) {
                exit("TokenSeguridad is empty!");
            }
            status_header(400);
            
            $this->log_error("Transaction is empty!");
            exit($this->get_exit_text());
        }
                
        $authorization_headers = [
            'Content-Type: application/json',
            'Authorization: ' . $secure_token
        ];
        
        return $authorization_headers;
    }
    
    /**
     * Make request to API.
     *
     * @param string $url
     * @param string $post_data
     * @param string $method
     * @param array $headers
     * @return string
     */
    public function make_request(
        string $url,
        string $post_data = null,
        string $method = 'POST',
        array $headers = null
    ): string {
        $ssl_verifypeer = 2;
        if (Helpers_General::is_development_env()) {
            $ssl_verifypeer = 0;
        }
        
        $curl = curl_init();
        
        curl_setopt($curl, CURLOPT_URL, $url);
        
        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
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
                exit('VisaNet cURL error. ' . $errno . ': ' . $error_message);
            }
            status_header(400);
            
            $this->log_error('VisaNet cURL error. ' . $errno . ': ' . $error_message);
            
            exit($this->get_exit_text());
        }

        curl_close($curl);

        if ($response === false) {
            if (Helpers_General::is_test_env()) {
                exit('VisaNet cURL error ;' . curl_error($curl));
            }
            status_header(400);
            
            $this->log_error('VisaNet cURL error ;' . curl_error($curl));
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

        return $response;
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
            Helpers_Payment_Method::VISANET_URI .
            "/" .
            $whitelabel_payment_method_id .
            "/";
        
        $token = $this->get_prefixed_transaction_token();
        
        $result_url .= "?token=" . $token;
        
        return $result_url;
    }
    
    /**
     *
     * @return string
     */
    public function get_source_url(): string
    {
        $source_url = "";
        
        if (isset($this->payment_data['visanet_test']) &&
            (int)$this->payment_data['visanet_test'] === 1
        ) {
            $source_url = self::TEST_LIBRERJS_URL;
        } else {
            $source_url = self::PRODUCTION_LIBRERJS_URL;
        }
        
        return $source_url;
    }
    
    /**
     *
     * @param array $response
     * @return string
     */
    public function get_session_token(array $response): string
    {
        if (empty($response)) {
            if (Helpers_General::is_test_env()) {
                exit("Empty respose given from cURL.");
            }
            $this->log_error('Empty respose given from cURL.');
            
            status_header(400);
            exit($this->get_exit_text());
        }
        
        if (empty($response['sessionKey'])) {
            if (Helpers_General::is_test_env()) {
                exit("No sessionKey given from cURL.");
            }
            $this->log_error('No sessionKey given from cURL.');
            
            status_header(400);
            exit($this->get_exit_text());
        }
        
        $session_token = $response['sessionKey'];
        
        return $session_token;
    }
    
    /**
     *
     * @return string
     */
    public function get_merchant_name(): string
    {
        // The maximum of the length of the name should be 25 characters
        $merchant_name = trim(substr($this->whitelabel['name'], 0, 25));
        
        return $merchant_name;
    }
    
    /**
     *
     * @return int|null
     */
    public function get_purchase_number():? int
    {
        if (empty($this->transaction->token)) {
            return null;
        }
        
        return $this->transaction->token;
    }
    
    /**
     *
     * @param array $response
     * @return int
     */
    public function get_expiration_minutes(array $response): int
    {
        if (empty($response)) {
            if (Helpers_General::is_test_env()) {
                exit("Empty respose given from cURL.");
            }
            $this->log_error('Empty respose given from cURL.');
            
            status_header(400);
            exit($this->get_exit_text());
        }
        
        if (empty($response['expirationTime'])) {
            if (Helpers_General::is_test_env()) {
                exit("No expirationTime given from cURL.");
            }
            $this->log_error('No expirationTime given from cURL.');
            
            status_header(400);
            exit($this->get_exit_text());
        }
        
        $expiration_time = $response['expirationTime'];
        
        $now = microtime(true) * 1000;

        $difference_of_time = (int)($expiration_time - $now);

        $expiration_minutes = (int)($difference_of_time / 1000 / 60);
        
        $data = [
            'expiration_time_from_visanet' => $expiration_time,
            'now_time' => $now,
            'difference_of_time' => $difference_of_time,
            'expiration_minutes' => $expiration_minutes,
        ];
        
        $this->log_info('Extra log (Expiration time and now and difference).', $data, false);
        
        // Hardcoded amount of time to expire
        $expiration_minutes = 15;
        
        return $expiration_minutes;
    }
    
    /**
     *
     * @param int $type
     * @return array|null
     */
    private function set_final_response_data(
        int $type = Forms_Wordpress_Payment_Base::STATUS_SUCCESS
    ):? array {
        $this->final_response_data = [];
        
        if (empty($this->final_response)) {
            return $this->final_response_data;
        }
        
        $final_response_data = null;
        
        switch ($type) {
            case Forms_Wordpress_Payment_Base::STATUS_SUCCESS:
                if (!empty($this->final_response['dataMap'])) {
                    $final_response_data = $this->final_response['dataMap'];
                }
                break;
            case Forms_Wordpress_Payment_Base::STATUS_COMMUNICATION_ERROR:
                if (!empty($this->final_response['data'])) {
                    $final_response_data = $this->final_response['data'];
                }
                break;
        }
        
        if (empty($final_response_data)) {
            return $this->final_response_data;
        }
        
        $final_response_data_encoded = json_encode($final_response_data);
        $this->final_response_data = json_decode($final_response_data_encoded, true);
        
        return $this->final_response_data;
    }
    
    /**
     *
     * @return string
     */
    private function get_masked_card_number(): string
    {
        $masked_card_number = '';
        if (empty($this->final_response_data['CARD'])) {
            return $masked_card_number;
        }
        
        $masked_card_number_input = $this->final_response_data['CARD'];
        
        if (strlen($masked_card_number_input) == 16) {
            $masked_card_number = substr($masked_card_number_input, 0, 4) . '-';
            $masked_card_number .= substr($masked_card_number_input, 4, 4) . '-';
            $masked_card_number .= substr($masked_card_number_input, 8, 4) . '-';
            $masked_card_number .= substr($masked_card_number_input, 12, 4);
        }
        
        return $masked_card_number;
    }
    
    /**
     *
     * @return string
     */
    private function get_brand_card_name(): string
    {
        if (empty($this->final_response_data['BRAND'])) {
            return '';
        }
        
        return $this->final_response_data['BRAND'];
    }
    
    /**
     *
     * @return string
     */
    private function get_date_time_of_order(): string
    {
        if (empty($this->final_response_data['TRANSACTION_DATE'])) {
            return '';
        }

        $date_time_of_order_input = $this->final_response_data['TRANSACTION_DATE'];
        
        $date_time_of_order = '';
        
        if (!empty($date_time_of_order_input) &&
            strlen($date_time_of_order_input) === 12
        ) {
            $date_time_of_order .= substr($date_time_of_order_input, 4, 2) . '/';
            $date_time_of_order .= substr($date_time_of_order_input, 2, 2) . '/';
            $date_time_of_order .= substr($date_time_of_order_input, 0, 2) . ' ';
            $date_time_of_order .= substr($date_time_of_order_input, 6, 2) . ':';
            $date_time_of_order .= substr($date_time_of_order_input, 8, 2) . ':';
            $date_time_of_order .= substr($date_time_of_order_input, 10, 2);
        }
        
        return $date_time_of_order;
    }
    
    /**
     *
     * @return string
     */
    private function get_refusal_reason(): string
    {
        if (empty($this->final_response_data['ACTION_DESCRIPTION'])) {
            return '';
        }

        return $this->final_response_data['ACTION_DESCRIPTION'];
    }
    
    /**
    *
    * @return string
    */
    private function get_order_number_text(): string
    {
        $purchase_number_text = '';
        
        $purchase_number = $this->get_purchase_number();
        
        if (!empty($purchase_number)) {
            $purchase_number_full = $this->get_prefixed_transaction_token();
            $purchase_number_text .= $purchase_number;
            $purchase_number_text .= " (" . $purchase_number_full . ")";
        }
        
        return $purchase_number_text;
    }
    
    /**
     *
     * @return string
     */
    private function get_masked_card_number_text(): string
    {
        $masked_card_number_text = '';
        
        $masked_card_number = $this->get_masked_card_number();
        
        if (!empty($masked_card_number)) {
            $masked_card_number_text = $masked_card_number;
        }
        
        return $masked_card_number_text;
    }
    
    /**
     *
     * @return string
     */
    private function get_brand_card_name_text(): string
    {
        $brand_card_name_text = '';
            
        $brand_card_name = $this->get_brand_card_name();
        
        if (!empty($brand_card_name)) {
            $brand_card_name_text = $brand_card_name;
        }
        
        return $brand_card_name_text;
    }
    
    /**
     *
     * @return string
     */
    private function get_date_time_of_order_text(): string
    {
        $date_time_of_order_text = '';
        
        $date_time_of_order = $this->get_date_time_of_order();
        
        if (!empty($date_time_of_order)) {
            $date_time_of_order_text = $date_time_of_order;
        }
        
        return $date_time_of_order_text;
    }
    
    /**
     *
     * @return string
     */
    private function get_refusal_reason_text(): string
    {
        $refusal_reason_text = '';
        
        $refusal_reason = $this->get_refusal_reason();
        
        if (!empty($refusal_reason)) {
            $refusal_reason_text = $refusal_reason;
        }
        
        return $refusal_reason_text;
    }
    
    /**
     *
     * @param int $type 0 - additional_text for success
     *                  2 - additional_text for error (failure)
     *
     * @return void
     */
    protected function set_additional_text_on_failure_success_page(
        int $type = Forms_Wordpress_Payment_Base::STATUS_SUCCESS
    ): void {
        $additional_text = [];
        
        $this->set_final_response_data($type);
        
        switch ($type) {
            case Forms_Wordpress_Payment_Base::STATUS_SUCCESS:
                $additional_text['order_number'] = $this->get_order_number_text();
                $additional_text['masked_card_number'] = $this->get_masked_card_number_text();
                $additional_text['brand_card_name'] = $this->get_brand_card_name_text();
                $additional_text['date_time_of_order'] = $this->get_date_time_of_order_text();
                break;
            case Forms_Wordpress_Payment_Base::STATUS_ERROR:
                $additional_text['order_number'] = $this->get_order_number_text();
                break;
            case Forms_Wordpress_Payment_Base::STATUS_COMMUNICATION_ERROR:
                $additional_text['order_number'] = $this->get_order_number_text();
                $additional_text['masked_card_number'] = $this->get_masked_card_number_text();
                $additional_text['date_time_of_order'] = $this->get_date_time_of_order_text();
                $additional_text['refusal_reason'] = $this->get_refusal_reason_text();
                break;
        }
        
        Session::set('additional_text_on_failure_success_page', $additional_text);
    }
    
    /**
     *
     * @return string
     */
    public function get_timeout_url(): string
    {
        $url_part = 'https://';
        if (Helpers_General::is_test_env()) {
            $url_part .= 'lottopark.loc';
        } else {
            $url_part = lotto_platform_home_url();
        }

        $url_part = UrlHelper::changeAbsoluteUrlToCasinoUrl($url_part);
        
        $timeout_url = $url_part . Helper_Route::ORDER_FAILURE;
        
        return $timeout_url;
    }
    
    /**
     *
     * @return void
     */
    public function show_form(array $response): void
    {
        $token = $this->get_prefixed_transaction_token();
        
        $return_url = $this->get_result_url();
        
        $source_url = $this->get_source_url();
        
        $session_token = $this->get_session_token($response);
        
        $merchant_id = $this->get_merchant_id();
        
        $channel = $this->get_channel();
        
        $marchant_name = $this->get_merchant_name();
        
        $payment_amount = $this->get_payment_amount();
        
        $purchase_number = $this->get_purchase_number();
        if (is_null($purchase_number)) {
            $purchase_number = '';
        }
        
        $expiration_minutes = $this->get_expiration_minutes($response);
        
        $timeout_url = $this->get_timeout_url();
        
        $user_token = $this->get_prefixed_user_token($this->user);
        
        $this->save_payment_method_id_for_transaction();
        
        $post_data = [
            "token" => $token,
            "return_url" => $return_url,
            "source_url" => $source_url,
            "session_token" => $session_token,
            "merchant_id" => $merchant_id,
            "channel" => $channel,
            "marchant_name" => $marchant_name,
            "amount" => $payment_amount,
            "purchase_number" => $purchase_number,
            "expiration_minutes" => $expiration_minutes,
            "timeout_url" => $timeout_url,
            "user_token" => $user_token
        ];
        
        $visanet_view = View::forge("wordpress/payment/visanet");
        $visanet_view->set("post_data", $post_data);
        
        $this->log(
            "Redirecting to payment page.",
            Helpers_General::TYPE_INFO,
            $post_data
        );
        ob_clean();
        
        echo $visanet_view;
    }
    
    /**
     *
     * @return void
     */
    public function process_form(): void
    {
        $this->log_info('Begin payment process.', [], true);
        
        $this->check_credentials();
        
        $this->check_merchant_settings();
        
        $resource = "/api.security/v1/security";
        $url = $this->get_url($resource);
        
        $authorization_headers = $this->get_authorization_headers();
                
        $secure_token = $this->make_request(
            $url,
            null,
            'POST',
            $authorization_headers
        );
        
        $this->log_info(
            'Authentication secure token received.',
            ['secure_token' => $secure_token],
            true
        );
        
        $merchan_id = $this->get_merchant_id();
        $resource_for_communication_session = "/api.ecommerce/v2/ecommerce/token/session/" . $merchan_id;
        $url_for_communication_session = $this->get_url($resource_for_communication_session);
        
        $authorization_headers_for_communication_session = $this->get_authorization_headers_for_communication_session($secure_token);
        
        $post_data = $this->get_communication_session_data();
        
        $response_for_communication_session = $this->make_request(
            $url_for_communication_session,
            $post_data,
            'POST',
            $authorization_headers_for_communication_session
        );
        
        $this->log_info(
            'Response for communication session received.',
            [
                'post_data' => $post_data,
                'response_for_communication_session' => $response_for_communication_session
            ],
            true
        );
        
        $response = (array)json_decode($response_for_communication_session);
        
        Session::set('visanet_secure_token', $secure_token);
         
        $this->show_form($response);
    }
    
    /**
     *
     * @param array $response
     * @return int
     */
    private function check_reponse(array $response = null): int
    {
        $result = self::RESULT_OK;
        
        if (isset($response['errorCode'])) {
            $result = self::RESULT_WITH_ERRORS;
        }
        
        return $result;
    }
    
    /**
     *
     * @param array $response
     * @param string $token
     * @return void
     */
    private function save_data_for_transaction(
        array $response = null,
        string $token = ""
    ): void {
        if (empty($this->transaction)) {
            $this->get_transaction_by_token($token);
        }
        
        $additional_data = $response !== null ? serialize($response) : null;
        
        $set = [
            'additional_data' => $additional_data
        ];
        $this->transaction->set($set);
        $this->transaction->save();
    }
    
    /**
     *
     * @param array $response
     * @return string
     */
    private function get_out_id(array $response = null): string
    {
        if (empty($response)) {
            if (Helpers_General::is_test_env()) {
                exit("Empty respose given from cURL.");
            }
            $this->log_error('Empty respose given from cURL.');
            
            status_header(400);
            exit($this->get_exit_text());
        }
        
        $order = $response['order'];
        $out_id = $order->transactionId;
        
        return $out_id;
    }
    
    /**
     *
     * @param array $response
     * @param string $token
     * @return void
     */
    private function accept_transaction_data(
        array $response = null,
        string $token = ""
    ): void {
        if (empty($this->transaction)) {
            $this->get_transaction_by_token($token);
        }
        
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
            $this->log_error('There are some errors happened durning accept transaction process.', $response);
                
            $this->save_data_for_transaction($response, $token);

            $this->set_additional_text_on_failure_success_page(Forms_Wordpress_Payment_Base::STATUS_COMMUNICATION_ERROR);

            Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
        }
    }
    
    /**
     *
     * @param array $response
     * @param string $token
     * @return void
     */
    private function final_check_and_redirect(
        array $response = null,
        string $token = ""
    ): void {
        if (empty($response)) {
            if (Helpers_General::is_test_env()) {
                exit("Empty respose given from cURL.");
            }
            $this->log_error('Empty respose given from cURL.', $response);
            
            $this->set_additional_text_on_failure_success_page(Forms_Wordpress_Payment_Base::STATUS_ERROR);
            
            Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
        }
        
        $check_response = $this->check_reponse($response);
        
        switch ($check_response) {
            case Forms_Wordpress_Payment_Base::STATUS_SUCCESS:
                $this->accept_transaction_data($response, $token);
                
                $this->log_success("Payment successful.", $response);
                
                $this->set_additional_text_on_failure_success_page(Forms_Wordpress_Payment_Base::STATUS_SUCCESS);
                
                Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_SUCCESS));
                break;
            case Forms_Wordpress_Payment_Base::STATUS_ERROR:
                if (Helpers_General::is_test_env()) {
                    exit("There is something wrong with communication.");
                }
                $this->log_error('There are some errors happened on payment page.', $response);
                
                $this->save_data_for_transaction($response, $token);
                
                $this->set_additional_text_on_failure_success_page(Forms_Wordpress_Payment_Base::STATUS_COMMUNICATION_ERROR);
                
                Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
                break;
        }
    }
    
    /**
     *
     * @param string $visanet_transaction_token
     * @param string $token
     * @return string
     */
    private function get_data_for_final_confirmation(
        string $visanet_transaction_token = "",
        string $token = ""
    ): string {
        $channel = $this->get_channel();
        
        $capture_type = "manual";
        
        $countable = "true";
        
        if (empty($this->transaction)) {
            $this->get_transaction_by_token($token);
        }
        
        $amount_payment = $this->get_payment_amount();
        
        $currency_code = $this->get_payment_currency($this->transaction->payment_currency_id);
        
        $purchase_number = $this->get_purchase_number();
        if (is_null($purchase_number)) {
            $purchase_number = '';
        }
        
        $data = [
            "channel" => $channel,
            "captureType" => $capture_type,
            "countable" => $countable,
            "order" => [
                "tokenId" => $visanet_transaction_token,
                "amount" => $amount_payment,
                "currency" => $currency_code,
                "purchaseNumber" => $purchase_number
            ]
        ];
        
        return json_encode($data);
    }
    
    /**
     * This is extra function.
     *
     * @param string $response
     * @param string $token
     * @return void
     */
    private function check_gateway_timeout(
        string $response = null,
        string $token = ""
    ): void {
        $gateway_error = '/504 Gateway Time-out/';
        
        if (preg_match($gateway_error, $response)) {
            if (Helpers_General::is_test_env()) {
                exit("Gateway Time-out.");
            }
            
            $response_to_save = [
                'response_of_gateway' => $response
            ];
            
            $this->save_data_for_transaction($response_to_save, $token);
            
            $this->log_error('504 Gateway Time-out.', $response);
            
            $this->set_additional_text_on_failure_success_page(Forms_Wordpress_Payment_Base::STATUS_ERROR);
            
            Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
        }
    }
    
    /**
     *
     * @param bool $redirect_from_here if `true` (default) system will redirect
     *                                  user to failure page
     *                                 if `false` system will return null and
     *                                  does not redirect user from that function
     * @return \Model_Whitelabel_Transaction|null
     */
    private function get_transation_by_id_from_session(
        bool $redirect_from_here = true
    ):? Model_Whitelabel_Transaction {
        // Transaction ID
        if (empty(Session::get("transaction"))) {
            if (!$redirect_from_here) {
                return null;
            }
            if (Helpers_General::is_test_env()) {
                exit("Empty transaction ID in session.");
            }
            $this->log_error('Empty transaction ID in session.');
            
            $this->set_additional_text_on_failure_success_page(Forms_Wordpress_Payment_Base::STATUS_ERROR);
            
            Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
        }
        
        $transaction_id = Session::get("transaction");
        $transaction = Model_Whitelabel_Transaction::find_by_pk($transaction_id);
        
        if (empty($transaction)) {
            if (!$redirect_from_here) {
                return null;
            }
            if (Helpers_General::is_test_env()) {
                exit("No transaction found for transaction ID: " . $transaction_id);
            }
            $this->log_error('No transaction found for transaction ID: ' . $transaction_id);
            
            $this->set_additional_text_on_failure_success_page(Forms_Wordpress_Payment_Base::STATUS_ERROR);
            
            Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
        }
        
        $this->transaction = $transaction;
        
        return $transaction;
    }
    
    /**
     *
     * @return void
     */
    private function save_data_for_timeout(): void
    {
        $this->get_transation_by_id_from_session();
        
        $time_out_error = [
            'errorMessage' => 'Info: user clicked log-out or time-out happened.'
        ];
        
        $additional_data = serialize($time_out_error);
        
        $set = [
            'additional_data' => $additional_data
        ];
        
        $this->transaction->set($set);
        $this->transaction->save();
    }
    
    /**
     *
     * @param array $get_method_data
     * @param array $transaction_data
     * @param bool $timeout_happened
     * @return void
     */
    public function process_checking(
        array $get_method_data = null,
        array $transaction_data = null,
        bool $timeout_happened = false
    ): void {
        $this->log_info('Begin of the process of checking payment.', [], true);
        
        if ($timeout_happened) {
            $this->log_error('Timeout happened.');
            
            $this->save_data_for_timeout();
            
            $this->set_additional_text_on_failure_success_page(Forms_Wordpress_Payment_Base::STATUS_ERROR);
            
            Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
        }
        
        if (empty($get_method_data)) {
            if (Helpers_General::is_test_env()) {
                exit("Empty get_method_data variable (GET parameters).");
            }
            $this->log_error('Empty get_method_data variable (GET parameters).');
            
            $this->set_additional_text_on_failure_success_page(Forms_Wordpress_Payment_Base::STATUS_ERROR);
            
            Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
        }
        
        if (empty($transaction_data)) {
            if (Helpers_General::is_test_env()) {
                exit("Empty POST data returned in the response from the form.");
            }
            $this->log_error('Empty POST data returned in the response from the form.');
            
            $this->set_additional_text_on_failure_success_page(Forms_Wordpress_Payment_Base::STATUS_ERROR);
            
            Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
        }
        
        if (empty($transaction_data['transactionToken'])) {
            if (Helpers_General::is_test_env()) {
                exit("Empty transactionToken in the POST data returned in the response from the form.");
            }
            $this->log_error('Empty transactionToken in the POST data returned in the response from the form.');
            
            $this->set_additional_text_on_failure_success_page(Forms_Wordpress_Payment_Base::STATUS_ERROR);
            
            Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
        }
        
        $visanet_transaction_token = $transaction_data['transactionToken'];
        
        $token = $this->get_token($get_method_data);
        
        if (empty($token)) {
            if (Helpers_General::is_test_env()) {
                exit("No token sent.");
            }
            $this->log_error('No token sent or could be found.');
            
            $this->set_additional_text_on_failure_success_page(Forms_Wordpress_Payment_Base::STATUS_ERROR);
            
            Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
        }

        $secure_token = null;
        if (!empty(Session::get('visanet_secure_token'))) {
            $secure_token = Session::get('visanet_secure_token');
            Session::delete('visanet_secure_token');
        }

        $merchant_id = $this->get_merchant_id();
        $resource = "/api.authorization/v3/authorization/ecommerce/" . $merchant_id;
        $url_for_transaction_authorization = $this->get_url($resource);
        
        $authorization_headers_for_communication_session = $this->get_authorization_headers_for_communication_session($secure_token);
        
        $post_data = $this->get_data_for_final_confirmation($visanet_transaction_token, $token);
        
        $response_for_transaction_authorization = $this->make_request(
            $url_for_transaction_authorization,
            $post_data,
            'POST',
            $authorization_headers_for_communication_session
        );
        
        $this->log_info(
            'Response for transaction authorization received.',
            [
                'url_for_transaction_authorization' => $url_for_transaction_authorization,
                'post_data' => $post_data,
                'response_for_transaction_authorization' => $response_for_transaction_authorization,
                'authorization_headers_for_communication_session' => serialize($authorization_headers_for_communication_session)
            ],
            true
        );
        
        $this->check_gateway_timeout($response_for_transaction_authorization, $token);
        
        $response = (array)json_decode($response_for_transaction_authorization);
        
        $this->set_final_response($response);
        
        $this->final_check_and_redirect($response, $token);
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
