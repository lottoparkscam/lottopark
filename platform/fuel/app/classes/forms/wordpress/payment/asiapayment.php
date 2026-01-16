<?php

use Fuel\Core\Response;
use Services\Logs\FileLoggerService;
use Helpers\Wordpress\LanguageHelper;

/** Class process of the AsiaPaymentGateway */
final class Forms_Wordpress_Payment_Asiapayment implements Forms_Wordpress_Payment_Process
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
     * Request array
     * @var array
     */
    protected $request = [];

    /**
     * Payment credentials
     * @var array
     */
    protected $payment_credentials = [];

    /**
     * Test environment
     * @var bool
     */
    protected $test_environment = false;

    /**
     * APG payment result statuses ('Succeed' field)
     *
     * @var array
     */
    protected $apg_result_status = [
        '0' => 'Failure',
        '1' => 'Success',
        '7' => 'Pending'
    ];

    /**
     *
     * @var array
     */
    protected $supported_locales = [
        'JP' => 'Japan',
        'FR' => 'France',
        'DE' => 'Germany',
        'ES' => 'Spanish',
        'IT' => 'Italy',
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
        $this->whitelabel = $whitelabel;
        $this->user = $user;
        $this->transaction = $transaction;
        $this->model_whitelabel_payment_method = $model_whitelabel_payment_method;
        $this->fileLoggerService = Container::get(FileLoggerService::class);
    }
    
    /**
     *
     * @return bool
     */
    public function is_test_environment(): bool
    {
        return $this->test_environment;
    }

    /**
     *
     * @return \Forms_Wordpress_Payment_Asiapayment
     */
    public function set_test_environment(): Forms_Wordpress_Payment_Asiapayment
    {
        if ((int)$this->payment_credentials['asiapaymenttest'] === 1) {
            $this->test_environment = true;
        }
        
        return $this;
    }
    
    /**
     *
     * @return null|\Forms_Wordpress_Payment_Asiapayment
     */
    public function set_payment_credentials():? Forms_Wordpress_Payment_Asiapayment
    {
        if (empty($this->model_whitelabel_payment_method)) {
            $this->log_error("No model_whitelabel_payment_method set.");
            exit(_("Bad request! Please contact us!"));
        }
        
        $this->payment_credentials = unserialize($this->model_whitelabel_payment_method['data']);
        
        return $this;
    }
    
    /**
     * Set Payment Params
     *
     * @param Model_Whitelabel_Payment_Method $model_whitelabel_payment_method
     * @return \Forms_Wordpress_Payment_Asiapayment
     */
    public function set_model_whitelabel_payment_method(
        Model_Whitelabel_Payment_Method $model_whitelabel_payment_method = null
    ):? Forms_Wordpress_Payment_Asiapayment {
        if (empty($model_whitelabel_payment_method)) {
            $this->log_error("No model_whitelabel_payment_method set.");
            exit(_("Bad request! Please contact us!"));
        }
        
        $this->model_whitelabel_payment_method = $model_whitelabel_payment_method;
        
        return $this;
    }

    /**
     * Set Whitelabel
     *
     * @param array $whitelabel
     */
    public function set_whitelabel($whitelabel)
    {
        $this->whitelabel = $whitelabel;
    }

    /**
     * Get Payment Params
     *
     * @param Model_Whitelabel_Transaction $transaction
     * @return null|Model_Whitelabel_Payment_Method
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
     */
    public function set_transaction(Model_Whitelabel_Transaction $transaction)
    {
        $this->transaction = $transaction;
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
            Helpers_Payment_Method::ASIAPAYMENT,
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
     * Checks if payment params exists
     *
     */
    protected function check_payment_form()
    {
        if (empty($this->payment_credentials['merchant_id_asiapayment']) or
            empty($this->payment_credentials['sha256key']) or
            empty($this->payment_credentials['apiurl'])) {
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
        
        $result_url = lotto_platform_home_url_without_language() .
            "/order/result/" .
            Helpers_Payment_Method::ASIAPAYMENT_URI .
            "/" .
            $whitelabel_payment_method_id .
            "/";
        
        return $result_url;
    }
    
    /**
     * Prepares payment params, update transaction and prints form
     *
     * @return string
     */
    public function process_form()
    {
        $this->set_payment_credentials();
        
        $this->set_test_environment();
        
        $this->check_payment_form();

        // NOTE: Don't know what exactly that value mean
        // TODO: Maybe create some additional stuff like array with
        // proper list of values?
        $language_request = 2;      // fixed value, required by APG
        
        // NOTE: Don't know what exactly that value mean
        // TODO: Maybe create some additional stuff like array with
        // proper list of values?
        $currency_request = 15;     // fixed value, required by APG
        
        $token = $this->get_prefixed_transaction_token();
        
        $item_name = sprintf(
            _("Transaction %s"),
            $token
        );
        
        $wlanguage = LanguageHelper::getCurrentWhitelabelLanguage();
        $lang_code = substr($wlanguage['code'], -2);
        
        $return_url = $this->get_result_url();

        $amount = $this->transaction->amount_payment;
        
        // At this moment the only currency which should be set is EUR!
        $currency_code = $this->get_payment_currency($this->transaction->payment_currency_id);
        
        $this->request['MerNo'] = $this->payment_credentials['merchant_id_asiapayment'];
        $this->request['BillNo'] = $token;
        $this->request['Amount'] = $amount;
        $this->request['Language'] = $language_request;
        $this->request['Currency'] = $currency_request;
        $this->request['ReturnURL'] = $return_url;
        $this->request['baseInfo'] = $this->get_baseinfo();
        
        if (isset($this->supported_locales[$lang_code])) {
            $this->request['locale'] = $lang_code;
        }

        if ($this->test_environment === true) {
            // Amount is overwritten for sandbox tests
            $this->request['Amount'] = '0.01';
        }

        $SHA256info = hash(
            'sha256',
            $this->request['MerNo'] .
            $this->request['BillNo'] .
            $this->request['Currency'] .
            $this->request['Amount'] .
            $this->request['Language'] .
            $this->request['ReturnURL'] .
            $this->payment_credentials['sha256key']
        );

        $this->request['SHA256info'] = $SHA256info;
        $this->request['PayCurrency'] = $currency_code;

        $ET_GOODS_TAB = [
            [
                'name' => $item_name,
                'price' => $this->request['Amount'],
                'num' => 1
            ]
        ];
        $ET_GOODS = json_encode($ET_GOODS_TAB, JSON_UNESCAPED_UNICODE);
        $this->request['ET_GOODS'] = $ET_GOODS;

        $additional_data = [];
        $additional_data['request'] = $this->request;
        $additional_data['paymentParams'] = $this->model_whitelabel_payment_method->to_array();

        $this->transaction->set([
            'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
            'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id'],
            "additional_data" => serialize($additional_data),
        ]);
        $this->transaction->save();
        
        $this->log(
            'Redirecting to AsiaPayment',
            Helpers_General::TYPE_INFO,
            ['request' => $this->request]
        );
        
        $this->print_form();
    }

    /**
     * Get user info
     *
     * @return string
     */
    protected function get_baseinfo(): string
    {
        $user = Lotto_Settings::getInstance()->get("user");
        $address = $user['address_1'] . ((!empty($user['address_2'])) ? ', ' . $user['address_2'] : '');

        $base_info = '';
        $base_info .= $user['name'] . '|';
        $base_info .= $user['surname'] . '|';
        $base_info .= $address . '|';
        $base_info .= $user['city'] . '|';
        $base_info .= $user['country'] . '|';
        $base_info .= $user['zip'] . '|';
        $base_info .= $user['email'] . '|';
        if (!empty($user['phone']) && !empty($user['phone_country'])) {
            $base_info .= $user['phone'] . '|';
        } else {
            $base_info .= '|';
        }
        $base_info .= strtoupper(mb_substr($user['state'], -2)) . '|';
        $base_info .= '|'; // payment bank code
        $base_info .= '0';
        return $base_info;
    }

    /**
     * Return html post form
     *
     * @return void
     */
    protected function print_form(): void
    {
        $form = View::forge("wordpress/payment/asiapaymentgateway");
        $form->set("pdata", $this->payment_credentials);
        $form->set("request", $this->request);
        echo $form;
    }

    /**
     * Get transaction and check if exist
     *
     * @param $token
     * @return mixed
     */
    protected function get_transaction($token)
    {
        $token_int = intval(substr($token, 3));
        $transaction = Model_Whitelabel_Transaction::find([
            "where" => [
                "whitelabel_id" => $this->whitelabel['id'],
                "token" => $token_int
            ]
        ]);

        if (!isset($transaction[0]['id'])) {
            status_header(400);
            $this->log_error(
                'Transaction with APG token ' . $token . ' does not exist',
                ['post' => Input::post()]
            );
            
            return false;
        }

        return $transaction[0];
    }


    /**
     * Check is_user
     *
     * @return bool
     */
    protected function check_payment_user(): bool
    {
        if (Lotto_Settings::getInstance()->get("is_user") !== true) {
            $this->log_error(
                'User missing',
                ['post' => Input::post(), 'server' => Input::server()]
            );
            
            return false;
        }
        return true;
    }

    /**
     * Check session transaction
     *
     * @return bool
     */
    protected function check_payment_transaction(): bool
    {
        if (!is_numeric(Session::get('transaction')) ||
            Session::get('transaction') <= 0
        ) {
            $this->log_error(
                'Transaction missing',
                ['post' => Input::post(), 'server' => Input::server()]
            );
            
            return false;
        }
        return true;
    }

    /**
     * Check session transaction
     *
     * @return bool
     */
    protected function check_payment_post_data(): bool
    {
        if (Input::post('BillNo') &&
            Input::post('Currency') &&
            Input::post('Amount') &&
            Input::post('Result')
        ) {
            return true;
        } else {
            $this->log_error(
                'Required POST fields are missing',
                ['post' => Input::post(), 'server' => Input::server()]
            );
            
            return false;
        }
    }

    /**
     * Check asiapaymentgateway result
     *
     *
     * @return bool
     */
    public function check_payment_result(): bool
    {
        $this->log(
            'Checking AsiaPayment results',
            Helpers_General::TYPE_INFO,
            [
                'post' => Input::post(),
                'user' => Lotto_Settings::getInstance()->get("is_user"),
                'transaction' => Session::get('transaction')
            ]
        );

        if (!$this->check_payment_user()) {
            return false;
        }
        if (!$this->check_payment_transaction()) {
            return false;
        }
        if (!$this->check_payment_post_data()) {
            return false;
        }

        $asg_bill_no = Input::post('BillNo'); # Order Number
        # SHA256 signature info, digitally sign the key fields of payment result
        # (BillNo, Currency, Amount, Succeed, SHA256key)
        $asg_SHA256 = Input::post('SHA256info');
        $asg_succeed = Input::post('Succeed');
        $asg_amount = Input::post('Amount');
        $asg_language = Input::post('Language');
        $asg_currency = Input::post('Currency');
        $asg_tradeno = Input::post('TradeNo');  # Transaction Reference No.

        /*
        # for local tests only
        $asg_bill_no = 'LPP447679224';
        $asg_succeed = '1';
        $asg_amount = '0.01';
        $asg_language = '2';
        $asg_currency = '15';
        $asg_SHA256 = '3519D81B5F3E1ADAFCEA54ECC0D461A956550CFBADAC70F87A6463EC6848338B';
        */

        $this->set_whitelabel(Lotto_Settings::getInstance()->get("whitelabel"));

        $transaction = $this->get_transaction($asg_bill_no);
        if ($transaction === false) {
            return false;
        }

        $payment_params = $this->get_model_whitelabel_payment_method($transaction);
        
        $this->set_model_whitelabel_payment_method($payment_params);
        
        $this->set_payment_credentials();
        
        $this->set_test_environment();

        if ($this->test_environment === true) {
            $transaction->set(['amount' => '0.01']);
        }

        $transaction_additional_data = unserialize($transaction->additional_data);

        $SHA_256_info = hash(
            'sha256',
            $transaction_additional_data['request']['BillNo'] .
            $asg_currency .
            $transaction_additional_data['request']['Amount'] .
            $asg_succeed .
            $this->payment_credentials['sha256key']
        );
        $SHA_256_info = strtoupper($SHA_256_info);

        if ($asg_succeed === '1') {
            if ($asg_amount !== $transaction->amount) {
                $this->log_error(
                    'Transaction failed - Amounts are not the same',
                    [
                        'post' => Input::post(),
                        'amount transaction' => $transaction->amount,
                        'amount APG' => $asg_amount
                    ]
                );
                $transaction->set([
                    'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                    'additional_data' => serialize($transaction_additional_data + ['result' => Input::post()])
                ]);
                $transaction->save();
                
                return false;
            }

            // Hash check
            if ($SHA_256_info === $asg_SHA256) {
                # SUCCESS

                $accept_transaction_result = Lotto_Helper::accept_transaction(
                    $transaction,
                    $asg_tradeno,
                    $transaction_additional_data + ['result' => Input::post(), 'server' => $_SERVER],
                    $this->whitelabel
                );

                // Now transaction returns result as INT value and
                // we can redirect user to fail page or success page
                // or simply inform system about that fact
                if ($accept_transaction_result === Forms_Transactions_Accept::RESULT_WITH_ERRORS) {
                    $this->log_error(
                        'Transaction failed - something went wrong',
                        [
                            'post' => Input::post(),
                            'SHA256info' => $SHA_256_info,
                            'asgSHA256' => $asg_SHA256
                        ]
                    );

                    $transaction->set([
                        'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                        'additional_data' => serialize($transaction_additional_data + ['result' => Input::post()])
                    ]);
                    $transaction->save();

                    return false;
                }
                
                $this->log_success('Transaction successed', ['post' => Input::post()]);
                
                return true;
            } else {
                $this->log_error(
                    'Transaction failed - hashes are not identical',
                    [
                        'post' => Input::post(),
                        'SHA256info' => $SHA_256_info,
                        'asgSHA256' => $asg_SHA256
                    ]
                );

                $transaction->set([
                    'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                    'additional_data' => serialize($transaction_additional_data + ['result' => Input::post()])
                ]);
                $transaction->save();
                
                return false;
            }
        } else {
            // Failure - bad status
            $fail_status_name = (isset($this->apg_result_status[$asg_succeed])) ? $this->apg_result_status[$asg_succeed] : 'unknown';
            $this->log_error(
                'Transaction failed with status ' . $asg_succeed . ' ('.$fail_status_name.')',
                ['post' => Input::post()]
            );

            $transaction->set([
                'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                'additional_data' => serialize($transaction_additional_data + ['result' => Input::post()])
            ]);
            $transaction->save();
            
            return false;
        }
        return false;
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
