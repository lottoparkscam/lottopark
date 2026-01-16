<?php

require_once APPPATH . "vendor/sofort/SofortLib/Xml/XmlToArrayException.php";
require_once APPPATH . "vendor/sofort/SofortLib/Xml/XmlToArrayNode.php";
require_once APPPATH . "vendor/sofort/SofortLib/Xml/XmlToArray.php";
require_once APPPATH . "vendor/sofort/SofortLib/Xml/Element/Element.php";
require_once APPPATH . "vendor/sofort/SofortLib/Xml/Element/Text.php";
require_once APPPATH . "vendor/sofort/SofortLib/Xml/Element/Tag.php";
require_once APPPATH . "vendor/sofort/SofortLib/Xml/ArrayToXml.php";
require_once APPPATH . "vendor/sofort/SofortLib/AbstractLoggerHandler.php";
require_once APPPATH . "vendor/sofort/SofortLib/AbstractDataHandler.php";
require_once APPPATH . "vendor/sofort/SofortLib/AbstractHttp.php";
require_once APPPATH . "vendor/sofort/SofortLib/Factory.php";
require_once APPPATH . "vendor/sofort/SofortLib/AbstractWrapper.php";
require_once APPPATH . "vendor/sofort/SofortLib/Multipay.php";
require_once APPPATH . "vendor/sofort/SofortLib/Sofortueberweisung.php";
require_once APPPATH . "vendor/sofort/SofortLib/Notification.php";
require_once APPPATH . "vendor/sofort/SofortLib/TransactionData.php";

use Fuel\Core\Validation;
use Services\PaymentService;

/**
 * Class for preparing Forms_Wordpress_Payment_Sofort form
 */
final class Forms_Wordpress_Payment_Sofort extends Forms_Wordpress_Payment_Base implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method;

    /**
     *
     * @var int
     */
    protected $payment_method = Helpers_Payment_Method::SOFORT;
    
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
    public function validate_form(): Validation
    {
        $validation = Validation::forge("sofort");

        return $validation;
    }

    /**
     *
     * @return void
     */
    protected function check_merchant_settings(): void
    {
        if (empty($this->payment_data['config_key'])) {
            $this->save_payment_method_id_for_transaction(Helpers_General::STATUS_TRANSACTION_ERROR);

            if (Helpers_General::is_test_env()) {
                exit("Empty configuration key.");
            }
            
            $this->log_error("Empty configuration key.");
            exit($this->get_exit_text());
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
            Helpers_Payment_Method::SOFORT_URI . '/' .
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

        $this->get_payment_data();

        $this->check_merchant_settings();
        
        try {
            $configkey = $this->payment_data['config_key'];

            $amount = $this->transaction->amount_payment;

            $transaction_text = $this->get_prefixed_transaction_token();

            $currency_code = $this->get_payment_currency($this->transaction->payment_currency_id);

            $confirmation_url = $this->get_confirmation_url();
            
            $failure_url = lotto_platform_home_url(Helper_Route::ORDER_FAILURE);
            $success_url = lotto_platform_home_url(Helper_Route::ORDER_SUCCESS); // i.e. http://my.shop/order/success

            $timeout = 900;     // 15 * 60;

            $Sofortueberweisung = new \Sofort\SofortLib\Sofortueberweisung($configkey);

            $Sofortueberweisung->setAmount($amount);
            $Sofortueberweisung->setCurrencyCode($currency_code);
            $Sofortueberweisung->setReason($transaction_text);
            $Sofortueberweisung->setSuccessUrl($success_url);
            $Sofortueberweisung->setAbortUrl($failure_url);
            $Sofortueberweisung->setEmailCustomer($this->user['email']);

            if (!empty($this->user['phone'])) {
                $Sofortueberweisung->setPhoneCustomer($this->user['phone']);
            }
            if (!empty($this->user['country'])) {
                $Sofortueberweisung->setSenderCountryCode($this->user['country']);
            }

            $Sofortueberweisung->setTimeout($timeout);
            $Sofortueberweisung->setVersion("WhiteLotto_1.0");
            $Sofortueberweisung->setNotificationUrl($confirmation_url);

            $Sofortueberweisung->sendRequest();

            if ($Sofortueberweisung->isError()) {
                // SOFORT-API didn't accept the data
                $this->transaction->set([
                    'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                    'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
                    'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
                ]);
                $this->transaction->save();

                $sofort_error_data = [];
                $sofort_error = $Sofortueberweisung->getError();
                if ($sofort_error !== false) {
                    $sofort_error_data[] = $sofort_error;
                }
                $this->log_error("Sofort error", $sofort_error_data);
            } else {
                // get unique transaction-ID useful for check payment status
                $transaction_sofort_id = $Sofortueberweisung->getTransactionId();

                $this->transaction->set([
                    "transaction_out_id" => $transaction_sofort_id,
                    'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
                    'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
                ]);
                $this->transaction->save();

                // buyer must be redirected to $paymentUrl else payment cannot be successfully completed!
                $payment_url = $Sofortueberweisung->getPaymentUrl();

                $this->log_success("Redirecting to Sofort.");

                header('Location: ' . $payment_url);
            }
        } catch (Exception $e) {
            $this->transaction->set([
                'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
                'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
            ]);
            $this->transaction->save();

            $this->log_error("Something went wrong: " . $e->getMessage());
            
            exit($this->get_exit_text());
        }
    }

    /**
     *
     * @return \Model_Whitelabel_Transaction|null
     */
    private function get_transaction_by_out_id():? Model_Whitelabel_Transaction
    {
        $transaction = Model_Whitelabel_Transaction::find([
            "where" => [
                "whitelabel_id" => $this->whitelabel['id'],
                "transaction_out_id" => $this->out_id
            ]
        ]);

        if (is_null($transaction) || count($transaction) == 0) {
            status_header(400);

            $this->log_error("Couldn't find transaction.");

            exit($this->get_exit_text());
        }

        $this->transaction = $transaction[0];
        
        return $this->transaction;
    }
    
    /**
     *
     * @return void
     */
    private function check_transaction(): void
    {
        if (!((int)$this->transaction->payment_method_type === Helpers_General::PAYMENT_TYPE_OTHER &&
            !empty($this->transaction->whitelabel_payment_method_id))
        ) {
            status_header(400);

            $this->log_error("Bad payment type.");

            exit($this->get_exit_text());
        }
    }
    
    /**
     *
     * @return \Model_Whitelabel_Payment_Method|null
     */
    public function get_model_whitelabel_payment_method():? Model_Whitelabel_Payment_Method
    {
        $whitelabel_payment_method_id = $this->transaction->whitelabel_payment_method_id;
        $this->model_whitelabel_payment_method = Model_Whitelabel_Payment_Method::find_by_pk($whitelabel_payment_method_id);

        if (!($this->model_whitelabel_payment_method !== null &&
            (int)$this->model_whitelabel_payment_method->whitelabel_id === (int)$this->transaction->whitelabel_id &&
            (int)$this->model_whitelabel_payment_method->payment_method_id === Helpers_Payment_Method::SOFORT)
        ) {
            status_header(400);

            $this->log_error("Bad payment method.");

            exit($this->get_exit_text());
        }
        
        return $this->model_whitelabel_payment_method;
    }
    
    /**
     *
     */
    public function process_confirmation()
    {
        try {
            $notification = file_get_contents('php://input');

            if ($notification === false) {
                throw new \Exception("No notification data");
            }
            
            $sofort_info_data = [];
            $sofort_info_data[] = $notification;
            
            $this->log_info("Received confirmation.", $sofort_info_data);
            
            $SofortLib_Notification = new Sofort\SofortLib\Notification();
            $TestNotification = $SofortLib_Notification->getNotification($notification);
            $this->out_id = $SofortLib_Notification->getTransactionId();

            $this->get_transaction_by_out_id();

            $this->check_transaction();

            $this->get_model_whitelabel_payment_method();

            $paydata = unserialize($this->model_whitelabel_payment_method['data']);

            $SofortLibTransactionData = new \Sofort\SofortLib\TransactionData($paydata['config_key']);
            $SofortLibTransactionData->addTransaction($TestNotification);
            $SofortLibTransactionData->setApiVersion('2.0');
            $SofortLibTransactionData->sendRequest();

            $output = [];
            $methods = [
                'getAmount' => '',
                'getAmountRefunded' => '',
                ///'getCount' => '',
                'getPaymentMethod' => '',
                'getConsumerProtection' => '',
                'getStatus' => '',
                'getStatusReason' => '',
                'getStatusModifiedTime' => '',
                'getLanguageCode' => '',
                'getCurrency' => '',
                ///'getTransaction' => '',
                'getReason' => [0, 0],
                ///'getUserVariable' => 0,
                'isTest' => '',
                'getTime' => '',
                'getProjectId' => '',
                'getRecipientHolder' => '',
                'getRecipientAccountNumber' => '',
                'getRecipientBankCode' => '',
                'getRecipientCountryCode' => '',
                'getRecipientBankName' => '',
                'getRecipientBic' => '',
                'getRecipientIban' => '',
                'getSenderHolder' => '',
                'getSenderAccountNumber' => '',
                'getSenderBankCode' => '',
                'getSenderCountryCode' => '',
                'getSenderBankName' => '',
                'getSenderBic' => '',
                'getSenderIban' => '',
                'getExchangeRate' => '',
                'getCostsExchangeRate' => '',
                'getCostsFees' => ''
            ];

            foreach ($methods as $method => $params) {
                if (is_array($params) && count($params) == 2) {
                    $output[$method] = $SofortLibTransactionData->$method($params[0], $params[1]);
                } elseif ($params !== '') {
                    $output[$method] = $SofortLibTransactionData->$method($params);
                } else {
                    $output[$method] = $SofortLibTransactionData->$method();
                }
            }

            $output['status_history'] = [];
            $i = 0;
            while ($SofortLibTransactionData->getStatusHistoryItem(0, $i) !== false) {
                $output['status_history'][] = $SofortLibTransactionData->getStatusHistoryItem(0, $i);
                $i++;
            }

            if ($SofortLibTransactionData->isError()) {
                status_header(400);
                
                $sofort_error_data = "";
                $sofort_error = $SofortLibTransactionData->getError();
                if ($sofort_error !== false) {
                    $sofort_error_data = $sofort_error;
                }
                
                $this->log_error("Sofort error: " . $sofort_error_data);
                
                exit($this->get_exit_text());
            } else {
                $this->log_info("Received Sofort data.", $output);

                $this->data = [
                    'amount' => $output['getAmount'],
                    'amount_refunded' => $output['getAmountRefunded'],
                    'payment_method' => $output['getPaymentMethod'],
                    'consumer_protection' => $output['getConsumerProtection'],
                    'status' => $output['getStatus'],
                    'status_reason' => $output['getStatusReason'],
                    'status_modified_time' => $output['getStatusModifiedTime'],
                    'language_code' => $output['getLanguageCode'],
                    'currency' => $output['getCurrency'],
                    'reason' => $output['getReason'],
                    'test' => $output['isTest'],
                    'time' => $output['getTime'],
                    'project_id' => $output['getProjectId'],
                    'recipient_holder' => $output['getRecipientHolder'],
                    'recipient_account_number' => $output['getRecipientAccountNumber'],
                    'recipient_bank_code' => $output['getRecipientBankCode'],
                    'recipient_country_code' => $output['getRecipientCountryCode'],
                    'recipient_bank_name' => $output['getRecipientBankName'],
                    'recipient_bic' => $output['getRecipientBic'],
                    'recipient_iban' => $output['getRecipientIban'],
                    'sender_holder' => $output['getSenderHolder'],
                    'sender_account_number' => $output['getSenderAccountNumber'],
                    'sender_bank_code' => $output['getSenderBankCode'],
                    'sender_country_code' => $output['getSenderCountryCode'],
                    'sender_bank_name' => $output['getSenderBankName'],
                    'sender_bic' => $output['getSenderBic'],
                    'sender_iban' => $output['getSenderIban'],
                    'exchange_rate' => $output['getExchangeRate'],
                    'costs_exchange_rate' => $output['getCostsExchangeRate'],
                    'costs_fees' => $output['getCostsFees'],
                    'status_history' => $output['status_history']
                ];

                switch ($this->data['status']) {
                    case 'untraceable':
                    case 'received':
                        $this->ok = true;
                        $this->log_success("Confirmation successfully processed with status: " . $this->data['status'] . ".");
                        break;
                    case 'loss':
                    case 'refunded':
                        $this->transaction->set([
                            'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                            'transaction_out_id' => $this->out_id,
                            'additional_data' => serialize($this->data)
                        ]);
                        $this->transaction->save();
                        
                        $this->log_error("Received confirmation with loss or refunded status.");
                        break;
                    case 'pending':
                        $this->transaction->set([
                            'transaction_out_id' => $this->out_id,
                            'additional_data' => serialize($this->data)
                        ]);
                        $this->transaction->save();
                        
                        $this->log_info("Received confirmation with pending status.");
                        break;
                    default:
                        break;
                }
            }
        } catch (\Exception $e) {
            status_header(400);
            
            $this->log_error("Unknown error: " . $e->getMessage());
            
            exit($this->get_exit_text());
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
