<?php

use Fuel\Core\Validation;
use Fuel\Core\Response;
use Services\PaymentService;

/**
 * Description of Forms_Wordpress_Payment_Sepa
 */
final class Forms_Wordpress_Payment_Sepa extends Forms_Wordpress_Payment_Base implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method;
    
    const PRODUCTION_BASE_URL = "https://pp.sepa-cyber.com/";
    const TEST_BASE_URL = "https://tp.sepa-cyber.com/";
    
    /**
     *
     * @var int
     */
    protected $payment_method = Helpers_Payment_Method::SEPA;

    /**
     *
     * @var null|stdClass
     */
    private $data = null;
    
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
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge("sepa");
        
        return $validation;
    }
    
    /**
     *
     * @param \stdClass $data
     * @return void
     */
    public function set_data(\stdClass $data): void
    {
        if (!empty($data)) {
            $this->data = $data;
        }
    }
    
    /**
     *
     * @return string|null
     */
    public function get_status_data():? string
    {
        $status = null;
        
        if (!empty($this->data->transactionStatus)) {
            $status = $this->data->transactionStatus;
        }
        
        return $status;
    }
    
    /**
     *
     * @return string|null
     */
    public function get_payment_id():? string
    {
        $payment_id = null;
        
        if (!empty($this->data->paymentId)) {
            $payment_id = $this->data->paymentId;
        }
        
        return $payment_id;
    }
    
    /**
     *
     * @return string|null
     */
    public function get_amount_from_data():? string
    {
        $amount = null;
        
        if (!empty($this->data->amount)) {
            $amount = $this->data->amount;
        }
        
        return $amount;
    }
    
    /**
     *
     * @return string|null
     */
    public function get_currency():? string
    {
        $currency = null;
        
        if (!empty($this->data->currency)) {
            $currency = $this->data->currency;
        }
        
        return $currency;
    }
    
    /**
     *
     * @return string|null
     */
    public function get_code():? string
    {
        $code = null;
        
        if (!empty($this->data->result->code)) {
            $code = $this->data->result->code;
        }
        
        return $code;
    }
        
    /**
     *
     * @return string|null
     */
    public function get_token_from_data():? string
    {
        $token = null;
        
        if (!empty($this->data->merchantTransactionId)) {
            $token = $this->data->merchantTransactionId;
        }
        
        return $token;
    }
    
    /**
     *
     * @return string|null
     */
    public function get_MD5_checksum_from_data_send():? string
    {
        $md5_checksum = null;
        
        if (!empty($this->data->checksum)) {
            $md5_checksum = $this->data->checksum;
        }
        
        return $md5_checksum;
    }
    
    /**
     *
     * @return array|null
     */
    public function get_payment_data():? array
    {
        if (!empty($this->model_whitelabel_payment_method['data']) &&
            !empty(unserialize($this->model_whitelabel_payment_method['data']))
        ) {
            $this->payment_data = unserialize($this->model_whitelabel_payment_method['data']);
        } else {
            $this->log_error("Empty payment data.");
        }
        
        return $this->payment_data;
    }
    
    /**
     *
     * @return string
     */
    public function get_payment_page_url(): string
    {
        $main_url = "";
        
        if (isset($this->payment_data['sepa_test']) &&
            (int)$this->payment_data['sepa_test'] === 1
        ) {
            $main_url = self::TEST_BASE_URL;
        } else {
            $main_url = self::PRODUCTION_BASE_URL;
        }
        
        return $main_url;
    }
    
    /**
     *
     * @return string
     */
    public function get_member_id(): string
    {
        if (empty($this->payment_data['sepa_member_id'])) {
            $this->save_payment_method_id_for_transaction(Helpers_General::STATUS_TRANSACTION_ERROR);

            if (Helpers_General::is_test_env()) {
                exit("Empty member ID.");
            }
            
            $this->log_error("Empty member ID.");
            exit($this->get_exit_text());
        }
        
        $memeber_id = $this->payment_data['sepa_member_id'];
        
        return $memeber_id;
    }
    
    /**
     *
     * @return string
     */
    public function get_to_type(): string
    {
        if (empty($this->payment_data['sepa_to_type'])) {
            $this->save_payment_method_id_for_transaction(Helpers_General::STATUS_TRANSACTION_ERROR);

            if (Helpers_General::is_test_env()) {
                exit("Empty To Type.");
            }
            
            $this->log_error("Empty To Type.");
            exit($this->get_exit_text());
        }
        
        $to_type = $this->payment_data['sepa_to_type'];
        
        return $to_type;
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
        
        return $this->transaction->amount_payment;
    }
    
    /**
     *
     * @return string
     */
    public function get_redirect_url(): string
    {
        $whitelabel_payment_method_id = $this->get_whitelabel_payment_method_id();
        if (empty($whitelabel_payment_method_id)) {
            $this->log_error("Lack of whitelabel_payment_method_id!");
            exit(_("Bad request! Please contact us!"));
        }
        
        // Here apcopay uri is used
        // because of differences between defined
        // URL and real URL
        $redirect_url = lotto_platform_home_url_without_language() .
            "/order/result/sepa/" .
            $whitelabel_payment_method_id .
            "/";
        
        return $redirect_url;
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
            Helpers_Payment_Method::SEPA_URI . '/' .
            $whitelabel_payment_method_id . '/';
        
        return $confirmation_url;
    }
    
    /**
     *
     * @return string
     */
    public function get_secure_key(): string
    {
        if (empty($this->payment_data['sepa_secure_key'])) {
            $this->save_payment_method_id_for_transaction(Helpers_General::STATUS_TRANSACTION_ERROR);

            if (Helpers_General::is_test_env()) {
                exit("Empty Secure Key.");
            }
            
            $this->log_error("Empty Secure Key.");
            exit($this->get_exit_text());
        }
        
        $secure_key = $this->payment_data['sepa_secure_key'];
        
        return $secure_key;
    }
    
    /**
     *
     * @return string
     */
    public function get_generated_MD5_checksum(): string
    {
        $member_id = $this->get_member_id();
        $to_type = $this->get_to_type();
        $amount = $this->get_payment_amount();
        $merchant_transaction_id = $this->get_prefixed_transaction_token();
        $merchant_redirect_url = $this->get_redirect_url();
        $secure_key = $this->get_secure_key();

        $values = $member_id . "|" .
            $to_type . "|" .
            $amount . "|" .
            $merchant_transaction_id . "|" .
            $merchant_redirect_url . "|" .
            $secure_key;
         
        $generated_check_sum = md5($values);
        
        return $generated_check_sum;
    }

    /**
     *
     * @return string
     */
    public function get_generated_MD5_checksum_from_data_send(): string
    {
        $payment_id = $this->get_payment_id();
        $merchant_transaction_id = $this->get_prefixed_transaction_token();
        $amount = $this->get_amount_from_data();
        $status = $this->get_status_data();
        $secure_key = $this->get_secure_key();
        
        $values = $payment_id . "|" .
            $merchant_transaction_id . "|" .
            $amount . "|" .
            $status . "|" .
            $secure_key;
        
        $generated_check_sum = md5($values);
        
        return $generated_check_sum;
    }
    
    /**
     * Get Payment Params
     *
     * @return null|Model_Whitelabel_Payment_Method
     */
    public function get_model_whitelabel_payment_method():? Model_Whitelabel_Payment_Method
    {
        $model_whitelabel_payment_method = Model_Whitelabel_Payment_Method::find_by_pk(
            $this->transaction->whitelabel_payment_method_id
        );
        return $model_whitelabel_payment_method;
    }
    
    /**
     * @return void
     */
    protected function check_merchant_settings(): void
    {
        $this->get_payment_data();
        
        if (empty($this->payment_data['sepa_member_id']) ||
            empty($this->payment_data['sepa_secure_key']) ||
            empty($this->payment_data['sepa_to_type']) ||
            is_null($this->payment_data['sepa_test'])
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
    public function get_user_country(): string
    {
        $user_country = "";
        if (!empty($this->user['country'])) {
            $user_country = (string) $this->user['country'];
        }
        
        return $user_country;
    }
    
    /**
     *
     * @return string
     */
    public function get_user_city(): string
    {
        $user_city = "";
        if (!empty($this->user['city'])) {
            $user_city = (string) mb_substr($this->user['city'], 0, 64);
        }
        
        return $user_city;
    }
    
    /**
     *
     * @return string
     */
    public function get_user_state(): string
    {
        $user_state = "";
        if (!empty($this->user['state'])) {
            $user_state = (string) $this->user['state'];
        }
        
        return $user_state;
    }
    
    /**
     *
     * @return string
     */
    public function get_user_postcode(): string
    {
        $user_postcode = "";
        if (!empty($this->user['zip'])) {
            $user_postcode = (string) mb_substr($this->user['zip'], 0, 16);
        }
        
        return $user_postcode;
    }
    
    /**
     *
     * @return string
     */
    public function get_user_street(): string
    {
        $user_street = "";
        if (!empty($this->user['address_1'])) {
            $user_street = mb_substr($this->user['address_1'], 0, 128);
        }
        
        return $user_street;
    }
    
    /**
     *
     * @return string
     */
    public function get_user_telnocc(): string
    {
        $user_telnocc = "";
        if (!empty($this->user['phone']) &&
            !empty($this->user['phone_country'])
        ) {
            $user_telnocc = "";
        }
        
        return $user_telnocc;
    }
    
    /**
     *
     * @return string
     */
    public function get_user_phone(): string
    {
        $user_phone = "";
        if (!empty($this->user['phone']) &&
            !empty($this->user['phone_country'])
        ) {
            $user_phone = Lotto_View::format_phone(
                $this->user['phone'],
                $this->user['phone_country'],
                true,
                \libphonenumber\PhoneNumberFormat::E164
            );
        }
        
        return $user_phone;
    }
    
    /**
     *
     * @return string
     */
    public function get_user_email(): string
    {
        $user_email = "";
        if (!empty($this->user['email'])) {
            $user_email = (string) $this->user['email'];
        }
        
        return $user_email;
    }
    
    /**
     *
     * @return string
     */
    public function get_user_ip(): string
    {
        $user_ip = Lotto_Security::get_IP();
        
        return $user_ip;
    }
    
    /**
     *
     * @return void
     */
    public function main_process(): void
    {
        $token = $this->get_prefixed_transaction_token();
        
        $action_url = $this->get_payment_page_url();
        $action_url .= "transaction/Checkout";
        
        $member_id = $this->get_member_id();
        $to_type = $this->get_to_type();
        $amount = $this->get_payment_amount();
        $checksum = $this->get_generated_MD5_checksum();
        $merchant_redirect_url = $this->get_redirect_url();
        $notification_url = $this->get_confirmation_url();
        
        $currency_code = $this->get_payment_currency($this->transaction->payment_currency_id);
        
        $sepa = View::forge("wordpress/payment/sepa");

        $country = $this->get_user_country();
        $city = $this->get_user_city();
        $state = $this->get_user_state();
        $postcode = $this->get_user_postcode();
        $street = $this->get_user_street();
        $telnocc = $this->get_user_telnocc();
        $phone = $this->get_user_phone();
        $email = $this->get_user_email();
        $ip = $this->get_user_ip();
        
        $set = [
            "token" => $token,
            "action_url" => $action_url,
            "member_id" => $member_id,
            "to_type" => $to_type,
            "amount" => $amount,
            "merchant_transaction_id" => $token,
            "checksum" => $checksum,
            "merchant_redirect_url" => $merchant_redirect_url,
            "notification_url" => $notification_url,
            "currency" => $currency_code,
            "country" => $country,
            "city" => $city,
            "state" => $state,
            "postcode" => $postcode,
            "street" => $street,
            "telnocc" => $telnocc,
            "phone" => $phone,
            "email" => $email,
            "ip" => $ip
        ];
        
        $sepa->set($set);
        
        $this->log_success("Redirecting to SEPA", $set);
        
        ob_clean();
        echo $sepa;
    }
    
    /**
     *
     */
    public function process_form(): void
    {
        $this->log_info('Begin payment process.', [], true);
        
        $this->check_credentials();
        
        $this->check_merchant_settings();
        
        $this->save_payment_method_id_for_transaction();
        
        $this->main_process();
    }
    
    /**
     *
     * @param array $transaction_data Could be null
     * @return void
     */
    public function process_checking(
        array $transaction_data = null
    ): void {
        $this->log_info('Begin of the process of checking payment.', [], true);
        
        if (empty($transaction_data)) {
            if (Helpers_General::is_test_env()) {
                exit("Empty POST data returned in the response from the form.");
            }
            $this->log_error("Empty POST data returned in the response from the form.");
            
            Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
        }
        
        $status = (string) $transaction_data["status"];
        
        if ($status === "N") {
            $this->log_error(
                "An error happened during the process of payment.",
                $transaction_data
            );
            
            Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
        } elseif ($status === 'C') {
            $this->log_error(
                "User cancelled payment.",
                $transaction_data
            );
            
            Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
        } elseif (in_array($status, ['P', '3D'])) {
            $this->log_error(
                "Payment is in Pending status.",
                $transaction_data
            );
            
            Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
        }
        
        $this->log_success(
            "Transaction returns from SEPA with success flag"
        );
        
        Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_SUCCESS));
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
        try {
            $this->log("Received Sepa confirmation.");

            $data_input = @file_get_contents("php://input");
            
            if (empty($data_input)) {
                status_header(400);
                $this->log_error(
                    "Empty data!",
                    ['post' => $data_input]
                );
                exit(_("Bad request! Please contact us!"));
            }
            
            $data = json_decode($data_input);
            $this->set_data($data);
            
            $this->log_info(
                "Data received.",
                ['post' => $data_input]
            );
            
            $full_token = $this->get_token_from_data();
            $this->get_transaction_by_token($full_token);
            
            // CHECK IF TRANSACTION EXIST
            if ($this->transaction === false) {
                status_header(400);
                $this->log(
                    "Couldn't find transaction, token: " . $full_token,
                    Helpers_General::TYPE_INFO,
                    ['post' => $data_input]
                );
                exit(_("Bad request! Please contact us!"));
            }
            
            // SET PAYMENT PARAMS
            $payment_params = $this->get_model_whitelabel_payment_method();
            
            $this->set_model_whitelabel_payment_method($payment_params);
            
            $this->check_merchant_settings();
            
            $calculated_md5_from_data = $this->get_generated_MD5_checksum_from_data_send();
            $md5_from_data = $this->get_MD5_checksum_from_data_send();
            
            if (is_null($md5_from_data) ||
                $calculated_md5_from_data !== $md5_from_data
            ) {
                status_header(400);
                $this->log_error(
                    "There are some errors of md5 checksum!",
                    ['post' => $data_input]
                );
                exit(_("Bad request! Please contact us!"));
            }
            
            $payment_amount = $this->get_payment_amount();
            $amount_from_data = $this->get_amount_from_data();
            
            if (is_null($amount_from_data) ||
                round($amount_from_data, 2) != $payment_amount
            ) {
                status_header(400);
                $this->log_error(
                    "Wrong amount send!",
                    ['post' => $data_input]
                );
                exit(_("Bad request! Please contact us!"));
            }
            
            $status = $this->get_status_data();
            
            switch ($status) {
                case 'Y':       // ‘Y’ – Successfully processed
                    status_header(200);
                    
                    $this->log_success(
                        "Payment finished with success. ",
                        ['post' => $data_input]
                    );
                    
                    $payment_id = $this->get_payment_id();
                    
                    $data_input_decoded = json_decode($data_input, true);
                    
                    return [
                        'transaction' => $this->transaction,
                        'out_id' => $payment_id,
                        'data' => $data_input_decoded
                    ];
                case 'N':       // ‘N’ – Failed
                    status_header(400);
                    $this->log_error(
                        "Transaction failed!",
                        ['post' => $data_input]
                    );
                    exit(_("Bad request! Please contact us!"));
                    break;
                case 'C':       // ‘C’ – Cancelled
                    status_header(400);
                    $this->log_error(
                        "Transaction canceled!",
                        ['post' => $data_input]
                    );
                    exit(_("Bad request! Please contact us!"));
                    break;
                case 'P':       // ‘P’ – Pending
                case '3D':      // ‘3D’ – Pending for 3D authentication
                    status_header(400);
                    $this->log_error(
                        "Transaction pending!",
                        ['post' => $data_input]
                    );
                    exit(_("Bad request! Please contact us!"));
                    break;
                default:
                    status_header(400);
                    $this->log_error(
                        "Not known status!",
                        ['post' => $data_input]
                    );
                    exit(_("Bad request! Please contact us!"));
            }
        } catch (\Exception $ex) {
            status_header(400);
            $this->log_error(
                "Unknown error: " . json_encode($ex->getMessage()),
                json_encode($ex)
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
