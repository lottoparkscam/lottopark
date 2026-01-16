<?php

use Fuel\Core\Response;
use Services\Logs\FileLoggerService;

final class Forms_Wordpress_Payment_FlutterwaveAfrica extends Forms_Main implements Forms_Wordpress_Payment_Process
{
    // TODO: this class is the copy of flutterwave.php - it should be optimized after flutterwave.php optimization or system should be changed to allow two te same payments with different api credentials.
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
     * Request array
     * @var array
     */
    private $request = [];

    /**
     * Payment credentials
     * @var array
     */
    private $payment_data = [];

    /**
     *
     * @var bool
     */
    private $should_test = false;

    /**
     *
     * @var string
     */
    private $api_url = 'https://api.ravepay.co/flwv3-pug/getpaidx/api/v2/hosted/pay';

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
     * @return null|\Forms_Wordpress_Payment_FlutterwaveAfrica
     */
    public function set_payment_data():? Forms_Wordpress_Payment_FlutterwaveAfrica
    {
        if (empty($this->model_whitelabel_payment_method)) {
            $this->log_error("No model_whitelabel_payment_method set.");
            exit(_("Bad request! Please contact us!"));
        }
        
        $this->payment_data = unserialize($this->model_whitelabel_payment_method['data']);
        
        return $this;
    }
    
    /**
     * Set Payment Params
     *
     *
     * @param Model_Whitelabel_Payment_Method $model_whitelabel_payment_method
     * @return null|\Forms_Wordpress_Payment_FlutterwaveAfrica
     */
    public function set_model_whitelabel_payment_method(
        Model_Whitelabel_Payment_Method $model_whitelabel_payment_method = null
    ):? Forms_Wordpress_Payment_FlutterwaveAfrica {
        if (empty($model_whitelabel_payment_method)) {
            $this->log_error("No model_whitelabel_payment_method set.");
            exit(_("Bad request! Please contact us!"));
        }
        
        $this->model_whitelabel_payment_method = $model_whitelabel_payment_method;

        return $this;
    }

    /**
     *
     * @return array|null
     */
    public function get_whitelabel_from_whitelabel_payment_method():? array
    {
        if (empty($this->model_whitelabel_payment_method)) {
            $this->log_to_error_file("Empty model_whitelabel_payment_method.");
            
            $error_message = "No payment method of ID: " .
                Helpers_Payment_Method::FLUTTERWAVE_AFRICA;
            $this->log_error($error_message);
            exit($this->get_exit_text());
        }
        
        $whitelabel_id = (int)$this->model_whitelabel_payment_method->whitelabel_id;
        
        $this->whitelabel = Model_Whitelabel::get_single_by_id($whitelabel_id);
        
        if (empty($this->whitelabel)) {
            $this->log_to_error_file("No whitelabel data found.");
            
            $error_message = "No whitelabel data found. Payment method of ID: " .
                Helpers_Payment_Method::FLUTTERWAVE_AFRICA;
            $this->log_error($error_message);
            exit($this->get_exit_text());
        }
        
        return $this->whitelabel;
    }
    
    /**
     * Set Whitelabel
     *
     * @param array $whitelabel
     * @return \Forms_Wordpress_Payment_FlutterwaveAfrica
     */
    public function set_whitelabel(
        array $whitelabel
    ): Forms_Wordpress_Payment_FlutterwaveAfrica {
        $this->whitelabel = $whitelabel;

        return $this;
    }

    /**
     * Get Payment Params
     *
     * @param Model_Whitelabel_Transaction $transaction
     * @return Model_Whitelabel_Payment_Method
     */
    public function get_model_whitelabel_payment_method(
        Model_Whitelabel_Transaction $transaction
    ):? Model_Whitelabel_Payment_Method {
        $model_whitelabel_payment_method = Model_Whitelabel_Payment_Method::find_by_pk(
            $transaction->whitelabel_payment_method_id
        );
        return $model_whitelabel_payment_method;
    }

    /**
     * Set Transaction
     *
     * @param Model_Whitelabel_Transaction $transaction
     * @return \Forms_Wordpress_Payment_FlutterwaveAfrica
     */
    public function set_transaction(
        Model_Whitelabel_Transaction $transaction
    ): Forms_Wordpress_Payment_FlutterwaveAfrica {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     *
     * @param array $user
     * @return \Forms_Wordpress_Payment_FlutterwaveAfrica
     */
    public function set_user(array $user): Forms_Wordpress_Payment_FlutterwaveAfrica
    {
        $this->user = $user;

        return $this;
    }

    /**
     *
     * @return array
     */
    public function get_request()
    {
        return $this->request;
    }

    /**
     *
     * @return null|array
     */
    public function get_whitelabel():? array
    {
        return $this->whitelabel;
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
            Helpers_Payment_Method::FLUTTERWAVE_AFRICA,
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
     * Logs Flutterwave API error response
     *
     * @param string $message
     * @param array $data
     * @return void
     */
    private function log_error_flutterwave(string $message, array $data = []): void
    {
        $this->log('Flutterwave error response: ' . $message, Helpers_General::TYPE_ERROR, $data);
    }

    /**
     * Checks if payment params exists
     *
     * @return void
     */
    private function check_payment_form(): void
    {
        if (empty($this->payment_data['flutterwave_africa_public_key']) ||
            empty($this->payment_data['flutterwave_africa_secret_key'])
        ) {
            $this->log_error('Missing payment credentials');
            Session::set("message", ["error", _("Please select another payment method.")]);
            
            Response::redirect(lotto_platform_home_url('/order/'));
        }
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
        
        // NOTICE! Here is 'flutterwave' URI, because all return
        $result_url = lotto_platform_home_url_without_language() .
            "/order/result/" .
            Helpers_Payment_Method::FLUTTERWAVE_URI .
            "/" .
            $whitelabel_payment_method_id .
            "/";
        
        return $result_url;
    }
    
    /**
     *
     * @return void
     */
    public function process_form(): void
    {
        $this->set_payment_data();
        
        $this->check_payment_form();

        $amount = $this->transaction->amount_payment;

        $token = $this->get_prefixed_transaction_token();

        $currency_code = $this->get_payment_currency($this->transaction->payment_currency_id);

        $redirect_url = $this->get_result_url();

        switch ($currency_code) {
            case 'KES':
                $country = 'KE';
                break;
            case 'GHS':
                $country = 'GH';
                break;
            case 'ZAR':
                $country = 'ZA';
                break;
            case 'TZS':
                $country = 'TZ';
                break;
            default:
                $country = 'NG';
                break;
        }

        $this->request['amount'] = $amount;
        $this->request['customer_email'] = $this->user['email'];
        $this->request['currency'] = $currency_code;
        $this->request['country'] = $country;
        $this->request['txref'] = $token;
        $this->request['PBFPubKey'] = $this->payment_data['flutterwave_africa_public_key'];
        $this->request['redirect_url'] = $redirect_url;
        $this->request['payment_options'] = !empty($this->payment_data['flutterwave_africa_payment_options']) ? $this->payment_data['flutterwave_africa_payment_options'] : "card";
        $this->request['network'] = !empty($this->payment_data['flutterwave_africa_network']) ? $this->payment_data['flutterwave_africa_network'] : "MTN,VODAFONE,TIGO";
//        $this->request['phonenumber'] = '500500500'; // TODO: update this

        $additional_data = [];
        $additional_data['request'] = $this->request;
        $additional_data['paymentParams'] = $this->model_whitelabel_payment_method->to_array();

        $this->transaction->set([
            'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
            'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id'],
            "additional_data" => serialize($additional_data),
        ]);
        $this->transaction->save();

        $response = $this->make_request($this->api_url, json_encode($this->request));

        if (empty($response)) {
            $this->log_error("An error occured during the attempt of " .
                "communication with API of Flutterwave page.");

            Session::set("message", ["error", _('Security error! Please contact us!')]);

            Response::redirect(lotto_platform_home_url('/order/'));
        }


        if (isset($response['status']) &&
            (string)$response['status'] === 'success' &&
            isset($response['data']['link'])
        ) {
            $this->log(
                'Redirecting to Flutterwave',
                Helpers_General::TYPE_INFO,
                [
                    'request' => $this->request,
                    'url' => $response['data']['link']
                ]
            );
            Response::redirect($response['data']['link']);
        }

        Session::set("message", ["error", _('Security error! Please contact us!')]);

        Response::redirect(lotto_platform_home_url('/order/'));
    }

    /**
     * Make request to API
     *
     * @param string $url API url
     * @param string $post_data request data encoded in JSON
     *
     * @return array
     */
    private function make_request(string $url, string $post_data)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($curl);

        if ($errno = curl_errno($curl)) {
            $error_message = curl_strerror($errno);
            $this->log_error('Flutteewave cURL error. ' . $errno . ': ' . $error_message);
        }

        curl_close($curl);

        if ($response === false) {
            $this->log_error('Flutteewave cURL error ;' . curl_error($curl));
        }

        if (isset($response['error'])) {
            $this->log_error_flutterwave($response['error'], ['request' => $this->request]);
        }

        return json_decode($response, true);
    }

    /**
     * Get transaction and check if exist
     *
     * @param string $token
     * @return mixed
     */
    private function get_transaction(string $token)
    {
        $token_int = intval(substr($token, 3));
        $transaction = Model_Whitelabel_Transaction::find([
            "where" => [
                "whitelabel_id" => $this->whitelabel['id'],
                "token" => $token_int
            ]
        ]);

        if (!isset($transaction[0]['id'])) {
            $this->log_error('Transaction with token ' . $token . ' does not exist', ['post' => Input::post()]);
            return false;
        }

        return $transaction[0];
    }

    /**
     *
     * @param string $token
     * @return void
     */
    public function set_whitelabel_by_token(string $token): void
    {
        $transaction_prefix = substr($token, 0, 2);
        
        $whitelabels = Model_Whitelabel::find([
            'where' => [
                'prefix' => $transaction_prefix,
            ]
        ]);

        if (!isset($whitelabels[0])) {
            $error_message = "No whitelabel row found for given token: " .
                $token;
            $this->log_error($error_message);
            exit($this->get_exit_text());
        }

        $whitelabel = $whitelabels[0]->to_array();

        $this->whitelabel = $whitelabel;
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
        $body = @file_get_contents("php://input");

        $signature = (isset($_SERVER['HTTP_VERIF_HASH']) ? $_SERVER['HTTP_VERIF_HASH'] : '');

        if (!$signature) {
            $error_message = "No HTTP_VERIF_HASH value set in SERVER array. " .
                "ID of payment method: " . Helpers_Payment_Method::FLUTTERWAVE_AFRICA;
            $this->log_error($error_message);
            exit($this->get_exit_text());
        }

        $response = json_decode($body, true);

        $token = $response['txRef'] ?? null;

        if (empty($token)) {
            $this->log_error('Empty token!');
            exit($this->get_exit_text());
        }

        $status = $response['status'] ?? null;
        if (empty($status)) {
            $this->log_error('Empty status!');
            exit($this->get_exit_text());
        }

        $transaction_out_id = $response['flwRef'] ?? null;

        $this->set_whitelabel_by_token($token);
        
        $transaction = $this->get_transaction($token);
        if ($transaction === false) {
            $this->log(
                'Flutterwave notify result for transaction which does not exist, token: ' . $token,
                Helpers_General::TYPE_INFO,
                ['response' => $response]
            );

            return false;
        }
        $this->set_transaction($transaction);

        $this->log(
            'Flutterwave notify result, token: ' . $token,
            Helpers_General::TYPE_INFO,
            [
                'response' => $response,
                'server' => $signature
            ]
        );

        $model_whitelabel_payment_method = $this->get_model_whitelabel_payment_method($transaction);
        
        $this->set_model_whitelabel_payment_method($model_whitelabel_payment_method);
        
        $this->set_payment_data();

        $local_signature = $this->payment_data['flutterwave_africa_secret_webhook_key'];

        if ($signature !== $local_signature) {
            $error_message = "Differences in signature sent and set in settings. " .
                "Signature sent: " . $signature .
                " Signature local: " . $local_signature;
            $this->log_error($error_message);
            exit($this->get_exit_text());
        }

        if ((int)$transaction->status === 1) {
            $this->log(
                'Flutterwave notify result for transaction which is already approved, token: ' . $token,
                Helpers_General::TYPE_INFO,
                ['response' => $response]
            );

            return true;
        }

        if ((string)$status === 'successful') {
            $transaction_additional_data = unserialize($transaction->additional_data);

            $amount = $this->transaction->amount_payment ?? null;
            $currency = $this->get_payment_currency($this->transaction->payment_currency_id);

            $charge_amount = $response['charged_amount'] ?? null;
            $charge_currency = $response['currency'] ?? null;

            if ($charge_amount < $amount || $charge_currency != $currency) {
                $transaction->set([
                    'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                    'transaction_out_id' => $transaction_out_id
                ]);
                $transaction->save();

                $this->log(
                    "Flutterwave notify result validation amount or currency don't match " .
                    "Charged amount: {$charge_amount} / {$amount}" .
                    "Charged currency: {$charge_currency} / {$currency}",
                    Helpers_General::TYPE_ERROR,
                    ['response' => $response]
                );

                return false;
            }

            $this->log_success('Flutterwave transaction succeeded');
            http_response_code(200);
            return [
                'transaction' => $transaction,
                'out_id' => $transaction_out_id,
                'data' => $transaction_additional_data + ['result' => $response, 'server' => $_SERVER],
                'whitelabel' => $this->whitelabel
            ];
        } else {
            $this->log_error("Flutterwave payment result with status: " . $status);

            $transaction->set([
                'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                'transaction_out_id' => $transaction_out_id
            ]);
            $transaction->save();

            return false;
        }
    }

    /**
     *
     * @param int $whitelabel_payment_method_id
     * @return void
     */
    public function prepare_settings_for_confirmation_all_whitelabels(
        int $whitelabel_payment_method_id
    ): void {
        $this->log_to_error_file("whitelabel_payment_method_id: " . $whitelabel_payment_method_id);

        $this->set_settings_by_whitelabel_payment_method_id($whitelabel_payment_method_id);
        
        $this->set_payment_data();
        
        $this->get_whitelabel_from_whitelabel_payment_method();
        
//        $this->log("Flutterwave confirmation process begin.");

        $this->check_merchant_settings(true);
    }

    /**
     * Check settings for merchant needed for communication with Flutterwave
     *
     * @param bool $is_notification
     * @return void
     */
    private function check_merchant_settings(bool $is_notification = false): void
    {
        $payment_data = $this->payment_data;

        if (empty($payment_data['flutterwave_africa_public_key']) ||
            empty($payment_data['flutterwave_africa_secret_key']) ||
            empty($payment_data['flutterwave_africa_secret_webhook_key'])
        ) {
            $this->log_to_error_file("Wrong credentials for Whitelabel.");

            if ($is_notification) {
                status_header(400);
            } else {
                $set = [
                    'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                    'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
                    'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
                ];
                $this->transaction->set($set);
                $this->transaction->save();
            }

            $this->log_error("Empty Public Key, Secret Key or Webhook Secret key");

            exit($this->get_exit_text());
        }
    }

    /**
     * Set whitelabel by pull from whitelabel_payment_methods table
     * by given value of $whitelabel_payment_method_id
     * So this is needed when we want to confirm payment
     * for all whitelabels
     *
     * @param int $whitelabel_payment_method_id
     * @return \Forms_Wordpress_Payment_FlutterwaveAfrica
     */
    private function set_settings_by_whitelabel_payment_method_id(
        int $whitelabel_payment_method_id = null
    ): Forms_Wordpress_Payment_FlutterwaveAfrica {
        if (empty($whitelabel_payment_method_id)) {
            status_header(400);

            $this->log_error("Empty whitelabel_payment_method_id given.");
            exit($this->get_exit_text());
        }

        // Here model of whitelabel payment method will be
        // pulled based on $whitelabel_payment_method_id
        // which is given from webhook on Flutterwave page
        // and in fact is set as only ONE ID of the payment
        // amongs other (rest of V1 should have the same credentials)
        // and these credentials are used for the rest of process
        $this->model_whitelabel_payment_method = Model_Whitelabel_Payment_Method::find_by_pk(
            $whitelabel_payment_method_id
        );

        if (empty($this->model_whitelabel_payment_method)) {
            status_header(400);

            $this->log_to_error_file("Empty model_whitelabel_payment_method.");

            $error_message = "No payment method of ID: " .
                Helpers_Payment_Method::FLUTTERWAVE_AFRICA;
            $this->log_error($error_message);

            exit($this->get_exit_text());
        }

        return $this;
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
