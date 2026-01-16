<?php

use Fuel\Core\Response;
use Services\PaymentService;
use Services\Logs\FileLoggerService;

final class Forms_Wordpress_Payment_Ecopayz implements Forms_Wordpress_Payment_Process
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
    private $ips_permitted = [
        "test" => [
            "217.21.166.82",
            "217.21.166.201"
        ],
        "production" => [
            "176.57.42.131",
            "176.57.42.132"
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
            Helpers_Payment_Method::ECOPAYZ,
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
     * @return \Forms_Wordpress_Payment_Ecopayz
     */
    public function set_model_whitelabel_payment_method(
        Model_Whitelabel_Payment_Method $model_whitelabel_payment_method = null
    ): Forms_Wordpress_Payment_Ecopayz {
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
        $val = Validation::forge("ecopayz");
        
        return $val;
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
            (int)$this->model_whitelabel_payment_method->payment_method_id === Helpers_Payment_Method::ECOPAYZ)
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
            Helpers_Payment_Method::ECOPAYZ_URI . '/' .
            $whitelabel_payment_method_id . '/';
        
        return $confirmation_url;
    }
    
    /**
     *
     * @return void
     */
    public function process_form(): void
    {
        if ((int)$this->transaction->whitelabel_id !== (int)$this->whitelabel['id'] ||
            (int)$this->transaction->whitelabel_user_id !== (int)$this->user['id']
        ) {
            $this->log_error("Bad request.");
            
            exit(_("Bad request! Please contact us!"));
        }
        
        $payment_params = unserialize($this->model_whitelabel_payment_method['data']);
        
        if (empty($payment_params['merchant_id']) ||
            empty($payment_params['account']) ||
            empty($payment_params['password'])
        ) {
            $this->transaction->set([
                'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
                'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
            ]);
            $this->transaction->save();
            
            $this->log_error(
                "Empty Merchant ID, Merchant Account Number or Merchant Password."
            );
            
            exit(_("Bad request! Please contact us!"));
        }
        
        $endpoint = "https://secure." . ($payment_params['test'] ? "test." : '') .
            "ecopayz.com/PrivateArea/WithdrawOnlineTransfer.aspx";

        $amount = $this->transaction->amount_payment;
        
        $transaction_text = $this->get_prefixed_transaction_token();
        
        $currency_code = $this->get_payment_currency($this->transaction->payment_currency_id);
        
        $user_text = $this->get_prefixed_user_token($this->user);
        
        $success_url = lotto_platform_home_url(Helper_Route::ORDER_SUCCESS);
        $failure_url = lotto_platform_home_url(Helper_Route::ORDER_FAILURE);
        
        $confirmation_url = $this->get_confirmation_url();
        
        $data = [
            'PaymentPageID' => $payment_params['merchant_id'],
            'TxID' => $transaction_text,
            'TxBatchNumber' => 0,
            'MerchantAccountNumber' => $payment_params['account'],
            'Currency' => $currency_code,
            'Amount' => $amount,
            'CustomerIdAtMerchant' => $user_text,
            'MerchantFreeText' => '',
            'OnSuccessUrl' => $success_url,
            'OnFailureUrl' => $failure_url,
            'TransferUrl' => $confirmation_url,             // Hmm, I am not quite sure if this URL is OK
            'CallbackUrl' => $confirmation_url
        ];

        $checksum = md5(
            $data['PaymentPageID'] .
            $data['MerchantAccountNumber'] .
            $data['CustomerIdAtMerchant'] .
            $data['TxID'] .
            $data['TxBatchNumber'] .
            $data['Amount'] .
            $data['Currency'] .
            $data['MerchantFreeText'] .
            $data['OnSuccessUrl'] .
            $data['OnFailureUrl'] .
            $data['TransferUrl'] .
            $data['CallbackUrl'] .
            $payment_params['password']
        );

        $data['Checksum'] = $checksum;

        $this->log_success("Redirecting to ecoPayz.");
        
        $this->transaction->set([
            'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
            'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
        ]);
        $this->transaction->save();

        Response::redirect($endpoint . '?' . http_build_query($data));
    }
    
    /**
     *
     * @param SimpleXMLElement $xmlres
     */
    private function prepare_data($xmlres)
    {
        $this->data['status'] = (string) $xmlres->StatusReport->Status[0];
        $this->data['status_description'] = (string) $xmlres->StatusReport->StatusDescription[0];
        $this->data['customer_ip'] = (string) $xmlres->StatusReport->SVSCustomer->IP[0];
        $this->data['customer_firstname'] = (string) $xmlres->StatusReport->SVSCustomer->FirstName[0];
        $this->data['customer_lastname'] = (string) $xmlres->StatusReport->SVSCustomer->LastName[0];
        $this->data['customer_country'] = (string) $xmlres->StatusReport->SVSCustomer->Country[0];
        $this->data['customer_postalcode'] = (string) $xmlres->StatusReport->SVSCustomer->PostalCode[0];

        if (isset($xmlres->StatusReport->TransactionType)) {
            $this->data['transaction_type'] = (string) $xmlres->StatusReport->TransactionType;
        }
        $this->data['transaction_customer_account'] = (string) $xmlres->StatusReport->SVSTransaction->SVSCustomerAccount[0];
        $this->data['transaction_processing_time'] = (string) $xmlres->StatusReport->SVSTransaction->ProcessingTime[0];
        $this->data['transaction_result_code'] = (string) $xmlres->StatusReport->SVSTransaction->Result->Code[0];
        $this->data['transaction_result_description'] = (string) $xmlres->StatusReport->SVSTransaction->Result->Description[0];
        $this->data['transaction_batch_number'] = (string) $xmlres->StatusReport->SVSTransaction->BatchNumber[0];
        $this->out_id = (string) $xmlres->StatusReport->SVSTransaction->Id[0];

        foreach ($this->data as $key => $item) {
            $this->data[$key] = trim($item);
        }
    }
    
    /**
     *
     * @param string $status
     * @param string $password
     * @param int $code
     * @param string $desc
     */
    private function confirmation($status, $password, $code = 0, $desc = null)
    {
        if ($desc == null) {
            $desc = $status;
        }
        $answer = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><SVSPurchaseStatusNotificationResponse/>');
        $result = $answer->addChild('TransactionResult');
        $result->addChild('Description', $desc);
        $result->addChild('Code', $code);

        // WAS:
        // $status = $answer->addChild('Status', $status);
        // $status as result of this instruction is unused
        $answer->addChild('Status', $status);

        $auth = $answer->addChild('Authentication');
        $auth->addChild('Checksum', $password);

        $answer_res_t1 = trim($answer->asXML());
        $answer_res_t2 = str_replace("\n", "", $answer_res_t1);
        $checksum = md5($answer_res_t2);
        $answer_res = str_replace($password, $checksum, $answer_res_t2);

        header('Content-type: text/xml');
        echo $answer_res;
    }
    
    /**
     *
     */
    public function process_confirmation()
    {
        try {
            libxml_use_internal_errors(true);

            $this->log_info(
                "Received ecoPayZ confirmation.",
                Input::post()
            );
            
            if (Input::post("XML") === null) {
                status_header(400);
                
                // I have changed that to ERROR from INFO
                $this->log_error(
                    "XML not found.",
                    Input::post()
                );
                
                exit(_("Bad request! Please contact us!"));
            }
            
            $xml = stripslashes(Input::post("XML"));

            $xmlres = new SimpleXMLElement($xml);

            if (!isset($xmlres->Request->TxID)) {
                status_header(400);
                
                $this->log_error("No TxID present in the XML.");
                
                exit(_("Bad request! Please contact us!"));
            }
            
            $txid = $xmlres->Request->TxID[0];
            $transaction_token = intval(substr($txid, 3));

            $transaction_temp = Model_Whitelabel_Transaction::find([
                "where" => [
                    "whitelabel_id" => $this->whitelabel['id'],
                    "token" => $transaction_token
                ]
            ]);
            
            if ($transaction_temp === null || count($transaction_temp) == 0) {
                status_header(400);
                
                $this->log_error("Couldn't find transaction.");
                
                exit(_("Bad request! Please contact us!"));
            }

            $this->transaction = $transaction_temp[0];
            
            if (!((int)$this->transaction->payment_method_type === Helpers_General::PAYMENT_TYPE_OTHER &&
                !empty($this->transaction->whitelabel_payment_method_id))
            ) {
                status_header(400);
                
                $this->log_error("Bad payment type.");
                
                exit(_("Bad request! Please contact us!"));
            }
            
            $this->get_model_whitelabel_payment_method();
            
            $pay_data = unserialize($this->model_whitelabel_payment_method->data);

            $test = $pay_data['test'];

            if (($test && !in_array($this->ip, $this->ips_permitted['test'])) ||
                (!$test && !in_array($this->ip, $this->ips_permitted['production']))
            ) {
                status_header(400);
                
                $this->log_error("Bad IP.");
                
                exit(_("Bad request! Please contact us!"));
            }
            
            if (empty($pay_data['merchant_id']) ||
                empty($pay_data['account']) ||
                empty($pay_data['password'])
            ) {
                status_header(400);
                
                $this->log_error(
                    "Empty MerchantID, Account or Password.",
                    $pay_data
                );
                
                exit(_("Bad request! Please contact us!"));
            }

            $checksum = (string) $xmlres->Authentication->Checksum[0];
            $rawxml = str_replace($checksum, $pay_data['password'], $xml);
            $rawxml_md5_lower = strtolower(md5($rawxml));

            if ($rawxml_md5_lower !== $checksum) {
                status_header(400);
                
                $this->log_error(
                    "Bad checksum.",
                    [$checksum]
                );
                
                $this->confirmation(
                    'InvalidRequest',
                    $pay_data['password'],
                    1,
                    "Bad checksum."
                );
                exit();
            }

            $user_from_transaction = Model_Whitelabel_User::get_single_by_id($this->transaction->whitelabel_user_id);
            
            if ($user_from_transaction === null) {
                status_header(400);
                
                $this->log_error(
                    "Cannot find specified user.",
                    [$this->transaction->whitelabel_user_id]
                );
                
                $this->confirmation(
                    'InvalidRequest',
                    $pay_data['password'],
                    2,
                    "Cannot find specified user."
                );
                exit();
            }

            $transaction_full_id = $this->get_prefixed_transaction_token();
        
            $currency_code = $this->get_payment_currency($this->transaction->payment_currency_id);

            $user_text = $this->get_prefixed_user_token($user_from_transaction);

            if (((string) $xmlres->Request->TxID[0]) != $transaction_full_id ||
                ((string) $xmlres->Request->TxBatchNumber[0]) != "0" ||
                ((string) $xmlres->Request->Amount[0]) != $this->transaction->amount_payment ||
                ((string) $xmlres->Request->Currency[0]) != $currency_code ||
                ((string) $xmlres->Request->MerchantAccountNumber[0]) != $pay_data['account'] ||
                ((string) $xmlres->Request->CustomerIdAtMerchant[0]) != $user_text ||
                ((string) $xmlres->Request->MerchantFreeText[0]) != ''
            ) {
                status_header(400);
                
                $this->log_error(
                    "Incorrect request data.",
                    [
                        $this->transaction->amount_payment,
                        $pay_data['account'],
                        $user_text
                    ]
                );
                
                $this->confirmation(
                    'InvalidRequest',
                    $pay_data['password'],
                    3,
                    "Incorrect request data."
                );
                exit();
            }

            $this->prepare_data($xmlres);

            $status = $this->data['status'];
            
            switch ($status) {
                case '1':   // InvalidRequest
                case '2':   // DeclinedByCustomer
                case '3':   // TransactionFailed
                    $this->transaction->set([
                        'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                        'transaction_out_id' => $this->out_id,
                        'additional_data' => serialize($this->data)
                    ]);
                    $this->transaction->save();

                    $this->log_error(
                        "Received confirmation with failed/declined/invalid status.",
                        [$this->data['status']]
                    );

                    // This is strange that confirmation is OK if statuses are
                    // different than OK
                    $this->confirmation('OK', $pay_data['password']);
                    exit();

                    break;
                case '4':   // TransactionRequiresMerchantConfirmation
                    if ($xmlres->getName() == "CallbackStatusNotification") {
                        if ($this->data['transaction_result_code'] == "0" &&
                            $this->data['transaction_result_description'] == "OK"
                        ) {
                            $this->log_success("Confirmation successfully processed.");
                            
                            $this->ok = true;
                        } else {
                            $this->log_error("Confirmation received without OK/0 status.");
                        }
                    } else {
                        $this->log_info("Confirmation accepted by us. Waiting for Callback.");
                    }

                    $this->confirmation('Confirmed', $pay_data['password']);
                    break;
                case '5':   // TransactionCancelled
                    $this->transaction->set([
                        'transaction_out_id' => $this->out_id,
                        'additional_data' => serialize($this->data)
                    ]);
                    $this->transaction->save();

                    $this->log_warning("Received transaction with canceled status.");
                    
                    // This is also strange that is word Confirm in the case of TransactionCancelled
                    $this->confirmation('Confirm', $pay_data['password']);
                    break;
                default:    // Invalid Request
                    status_header(400);
                    
                    $this->log_error(
                        "Bad payment type",
                        [$this->data['status']]
                    );
                    
                    $this->confirmation('InvalidRequest', $pay_data['password']);
                    
                    exit();
                    break;
            }
        } catch (Exception $e) {
            status_header(400);

            $this->log_error(
                "Unknown error: " . json_encode($e->getMessage()),
                [json_encode($e)]
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
        $this->process_confirmation();
                
        $transaction = $this->get_transaction();
        $data = $this->get_data();
        $out_id = $this->get_out_id();
        $ok = $this->get_ok();
                
        return $ok;
    }
}
