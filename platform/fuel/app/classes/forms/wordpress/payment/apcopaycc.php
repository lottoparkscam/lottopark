<?php

use Fuel\Core\Validation;
use Services\PaymentService;
use Services\Logs\FileLoggerService;

final class Forms_Wordpress_Payment_Apcopaycc implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method,
        Traits_Payment_Method_Currency;

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
     * Payment credentials
     * @var array
     */
    private $payment_credentials = [];

    /**
     * Variable for storing proper url of the server of payments feature on Apcopay
     * Could be url of sandbox or live server
     * @var string
     */
    private $wsdl = "https://www.apsp.biz:9085/merchantTools.asmx?WSDL";

    /**
     *
     * @var string
     */
    private $fast_pay_url = "https://www.apsp.biz/pay/fp5A/Checkout.aspx";

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
        $this->fileLoggerService = Container::get(FileLoggerService::class);
    }

    /**
     *
     * @return null|\Forms_Wordpress_Payment_Apcopaycc
     */
    public function set_payment_credentials():? Forms_Wordpress_Payment_Apcopaycc
    {
        if (empty($this->model_whitelabel_payment_method)) {
            $this->log_error("No model_whitelabel_payment_method set.");
            exit(_("Bad request! Please contact us!"));
        }
        
        $this->payment_credentials = unserialize($this->model_whitelabel_payment_method['data']);
        
        return $this;
    }
    
    /**
     * Set Payment Params and set URL for process payment to live or sandbox server
     *
     * @param Model_Whitelabel_Payment_Method $model_whitelabel_payment_method
     * @return null|\Forms_Wordpress_Payment_Apcopaycc
     */
    public function set_model_whitelabel_payment_method(
        Model_Whitelabel_Payment_Method $model_whitelabel_payment_method = null
    ):? Forms_Wordpress_Payment_Apcopaycc {
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
     *
     * @param array $user
     */
    public function set_user($user)
    {
        $this->user = $user;
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
            Helpers_Payment_Method::APCOPAY_CC,
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
     * Check credentials needed for communication with Apcopay
     *
     * @return void
     */
    private function check_credentials(): void
    {
        if ((int)$this->transaction->whitelabel_id !== (int)$this->whitelabel['id'] ||
            (int)$this->transaction->whitelabel_user_id !== (int)$this->user['id']
        ) {
            $this->log_error("Bad request.");
            exit(_("Bad request! Please contact us!"));
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
            Helpers_Payment_Method::APCOPAY_CC_URI . '/' .
            $whitelabel_payment_method_id . '/';
        
        return $confirmation_url;
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
        
        // Here apcopay uri is used
        // because of differences between defined
        // URL and real URL
        $result_url = lotto_platform_home_url_without_language() .
            "/order/result/apcopay/" .
            $whitelabel_payment_method_id .
            "/";
        
        return $result_url;
    }
    
    /**
     * Main function to prepare payment on ApcoPay for user
     */
    public function process_form(Validation $form_values)
    {
        $this->set_payment_credentials();
        
        $this->check_credentials();

        $this->transaction->set([
            'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
            'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
        ]);
        $this->transaction->save();

        $token = $this->get_prefixed_transaction_token();

        // This could be remapped on int values within that class
        $currency_code = $this->get_payment_currency($this->transaction->payment_currency_id);
        $currency_iso_code = $this->get_currency_iso_code(
            Helpers_Payment_Method::APCOPAY_CC,
            $currency_code
        );

        $redirect_url = $this->get_result_url();
        
        $confirmation_url = $this->get_confirmation_url();

        $info_text = sprintf(_("Transaction %s"), $token);

        $data = [
            'amount' => $this->transaction->amount_payment,
            'token' => $token,
            'currency' => $currency_iso_code,
            'notification_url' => $confirmation_url,
            'redirect_url' => $redirect_url,
            'fast_pay_url' => $this->fast_pay_url,
            'description' => $info_text
        ];

        $transaction_xml_string = $this->generate_XML($data, $form_values);

        $apcopaycc_view = View::forge("wordpress/payment/apcopaycc");
        $apcopaycc_view->set("fast_pay_url", $this->fast_pay_url);
        $apcopaycc_view->set("transaction_xml_string", $transaction_xml_string);
        
        $this->log(
            "Redirecting to ApcoPay Payment page.",
            Helpers_General::TYPE_INFO,
            $data
        );
        ob_clean();

        echo $apcopaycc_view;
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
            $this->log_error(
                'Transaction with token ' . $token . ' does not exist',
                ['post' => Input::post()]
            );
            return false;
        }

        return $transaction[0];
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
     * Main method for checking result
     *
     * Return array if success
     *
     * @return array|bool
     */
    public function check_payment_result()
    {
        try {
            $this->log("Received ApcoPay confirmation.");

            if (empty(Input::post())) {
                status_header(400);
                $this->log_error("Empty POST data array!");
                exit(_("Bad request! Please contact us!"));
            }

            //RETRIEVE THE FASTPAY RESPONSE DomDocument
            $fast_pay_xml = $this->get_params_and_convert_to_DOM_document();

            // GET TOKEN
            $get_token = $fast_pay_xml->getElementsByTagName('ORef');
            $token = $get_token[0]->nodeValue;

            // GET TRANSACTION ID
            $get_transaction_tag = $fast_pay_xml->getElementsByTagName('Transaction');
            $transaction_out_id = $get_transaction_tag[0]->getAttribute("hash");

            $result_value = "";

            $transaction = $this->get_transaction($token);

            // CHECK IF TRANSACTION EXIST
            if ($transaction === false) {
                status_header(400);
                $this->log(
                    "Couldn't find transaction, token: " . $token,
                    Helpers_General::TYPE_INFO,
                    ['post' => Input::post()]
                );
                exit(_("Bad request! Please contact us!"));
            }

            // SET PAYMENT PARAMS
            $payment_params = $this->get_model_whitelabel_payment_method($transaction);
            
            $this->set_model_whitelabel_payment_method($payment_params);
            
            $this->set_payment_credentials();

            // VALIDATE PAYMENT SIGNATURE
            $re_check_validation = $this->re_check_MD5_validation_on_fast_pay_XML(
                $fast_pay_xml,
                $this->payment_credentials['secret_word'],
                $result_value
            );
            if ($re_check_validation) {
                //MDF successfully matched

                switch (strtoupper($result_value)) {
                    case "OK":
                        /* Print OK so the APCO system knows that the message is recieved */
                        echo "OK";

                        $validate_response = $this->validate_response_with_tool(
                            $fast_pay_xml,
                            $transaction,
                            $transaction_out_id
                        );
                        if (!$validate_response) {
                            return false;
                        }

                        $this->log_success(
                            'ApcoPay transaction succeeded',
                            ['post' => Input::post()]
                        );

                        return [
                            'transaction' => $transaction,
                            'out_id' => $transaction_out_id,
                            'data' => [
                                'result' => Input::post(),
                                'server' => Input::server()
                            ]
                        ];

                        break;
                    case "NOTOK":
                        $transaction->set([
                            'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                            'transaction_out_id' => $transaction_out_id
                        ]);
                        $transaction->save();

                        $this->log(
                            'ApcoPay transaction status: NOT OK',
                            Helpers_General::TYPE_ERROR,
                            [
                                'post' => Input::post(),
                                'server' => Input::server()
                            ]
                        );

                        break;
                    case "DECLINED":
                        $transaction->set([
                            'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                            'transaction_out_id' => $transaction_out_id
                        ]);
                        $transaction->save();

                        $this->log(
                            'ApcoPay transaction status: DECLINED',
                            Helpers_General::TYPE_ERROR,
                            [
                                'post' => Input::post(),
                                'server' => Input::server()
                            ]
                        );

                        break;
                    case "PENDING":
                        $transaction->set([
                            'status' => Helpers_General::STATUS_TRANSACTION_PENDING,
                            'transaction_out_id' => $transaction_out_id
                        ]);
                        $transaction->save();

                        $this->log(
                            'ApcoPay transaction status: PENDING',
                            Helpers_General::TYPE_INFO,
                            ['post' => Input::post()]
                        );

                        break;
                    case "CANCEL":
                        $transaction->set([
                            'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                            'transaction_out_id' => $transaction_out_id
                        ]);
                        $transaction->save();

                        $this->log(
                            'ApcoPay transaction status: CANCEL',
                            Helpers_General::TYPE_ERROR,
                            [
                                'post' => Input::post(),
                                'server' => Input::server()
                            ]
                        );

                        break;
                    default:
                        $transaction->set([
                            'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                            'transaction_out_id' => $transaction_out_id
                        ]);
                        $transaction->save();

                        $this->log(
                            'ApcoPay transaction status: OTHER: '.strtoupper($result_value).'',
                            Helpers_General::TYPE_ERROR,
                            [
                                'post' => Input::post(),
                                'server' => Input::server()
                            ]
                        );

                        break;
                }
            } else {
                $transaction->set([
                    'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                    'transaction_out_id' => $transaction_out_id
                ]);
                $transaction->save();

                $this->log(
                    'ApcoPay transaction status: INVALID HASH',
                    Helpers_General::TYPE_ERROR,
                    [
                        'post' => Input::post(),
                        'server' => Input::server()
                    ]
                );
            }
        } catch (\Exception $ex) {
            status_header(400);
            $this->log_error("Unknown error: " . json_encode($ex->getMessage()), json_encode($ex));
            exit(_("Bad request! Please contact us!"));
        }
    }

    /**
     *
     * @param array $data
     * @param Validation $form_values
     * @return type
     * @throws \Exception
     */
    private function generate_XML(array $data, Validation $form_values)
    {
        try {

            //GET INFORMATION FROM THE CLIENT'S SYSTEM TO BUILD THE XML
            $secret_word = $this->payment_credentials['secret_word'];
            $profile_ID = $this->payment_credentials['profile_id']; //The ID of the profile used to identify the merchant using the service (REQUIRED)

            $amount = $data['amount']; //the amount of the transaction (REQUIRED)
            $currency = $data['currency']; // the currency code of the transaction - 3 digit ISO code (REQUIRED)
            // Should it be hardcoded?
            $language = 'en'; //The language displayed in the checkout page. - 2 char code(REQUIRED)
            $order_reference = $data['token']; //the order reference for the transaction - depends on the merchants order table (REQUIRED)
            $redirection_URL = $data['redirect_url']; //The URL of the successful page (message page)(REQUIRED)
            // Should it be hardcoded?
            $action_type = 4; // The transaction Type ID (ex: 1 = Purchase)(REQUIRED)
            $status_URL = $data['notification_url'];

            // 3D Secure
            $secure_3D = '';
            if ($this->payment_credentials['3d_secure']) {
                $secure_3D .= '<Secure3D>';

                // BYPASS 3DS
                if ($this->payment_credentials['bypass_3ds']) {
                    $secure_3D .= '<Bypass3DS />';
                }

                // Only 3DS
                if ($this->payment_credentials['only_3ds']) {
                    $secure_3D .= '<Only3DS />';
                }

                $secure_3D .= '</Secure3D>';
            }

            $card_holder_name = str_replace(',', '', $form_values->validated("apcopaycc.name"));
            $address1 = str_replace(',', '', $form_values->validated("apcopaycc.address_1"));
            $address2 = str_replace(',', '', $form_values->validated("apcopaycc.address_2"));
            $post_code = str_replace(',', '', $form_values->validated("apcopaycc.post-code"));
            $city = str_replace(',', '', $form_values->validated("apcopaycc.city"));
            $country = str_replace(',', '', $form_values->validated("apcopaycc.country"));

            $transaction_XML_string = '<Transaction hash="'.$secret_word.'">';
            $transaction_XML_string .= '
                <ProfileID>'.$profile_ID.'</ProfileID>
                <Value>'.$amount.'</Value>
                <Curr>'.$currency.'</Curr>
                <Lang>'.$language.'</Lang>
                <RegName>'.$card_holder_name.'</RegName>
                <RegCountry>NG</RegCountry>
                <CIP>'.$_SERVER['REMOTE_ADDR'].'</CIP>
                <Address>'.$address1.', '.$address2.', '.$city.', '.$post_code.', '.$country.'</Address>
                <ORef>'.$order_reference.'</ORef>
                <ClientAcc>'.$this->user['token'].'</ClientAcc>
                <MobileNo>'.$this->user['phone'].'</MobileNo>
                <Email>'.$this->user['email'].'</Email>
                <NoCardList />
                <RedirectionURL>'.$redirection_URL.'</RedirectionURL>
                <UDF1 />
                <UDF2 />
                <UDF3 />
                <FastPay>
                  <ListAllCards>ALL</ListAllCards>
                  <NewCard1Try />
                  <NewCardOnFail />
                  <PromptCVV />
                  <PromptExpiry />
                </FastPay>
                <ActionType>'.$action_type.'</ActionType>
                <status_url urlEncode="true">'.$status_URL.'</status_url>
                <Enc>UTF8</Enc>
                <HideSSLLogo></HideSSLLogo>
                <AntiFraud>
                  <Provider></Provider>
                </AntiFraud>
                <return_pspid></return_pspid>
                <ForceBank>RAVEDIRECTFP</ForceBank>
				<StatementTicketNo>'.$data['description'].'</StatementTicketNo>
				'.$secure_3D.'
            ';

            $transaction_XML_string .= '</Transaction>';

            /* Call Ws to update the hash value */
            $transaction_XML_string = $this->update_XML_hash($transaction_XML_string);

            return $transaction_XML_string;
        } catch (\Exception $ex) {
            echo "<br/><strong>Message: </strong>" . $ex->getMessage();
            echo "<br/><strong>Trace: </strong>" . $ex->getTraceAsString();
        }
    }

    /**
     *
     * @param string $transaction_XML_string
     * @return type
     * @throws \Exception
     */
    private function update_XML_hash(string $transaction_XML_string)
    {
        try {
            /* CONNECT WITH THE TOOL AND RETRIEVE THE LAST TRANSACTION */
            $client = new SoapClient(
                $this->wsdl,
                ["trace" => 0, "exception" => 0]
            );
            $soap_result = $client->ComputeHash([
                "MerchID" => $this->payment_credentials['merchant_code'],
                "MerchPass" => $this->payment_credentials['password'],
                "XML" => $transaction_XML_string
            ]);
            /* Retrieve the update XML (String) - hash updated */
            $xml_tool_response = $soap_result->ComputeHashResult;
            /* Return Result */
            return $xml_tool_response;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Compare the XmlResponse from FastPay with the Transaction Information to make sure that it matches
     * @param type $xmlResponse the response given from the FastPay in DomDocument Format
     * @param $transaction
     * @param $transaction_out_id
     * @return bool
     * @throws \Exception
     */
    private function validate_response_with_tool(
        $xml_fast_pay_response,
        $transaction,
        $transaction_out_id
    ) {
        try {
            //Retrieve the values from the XML response of FASTPAY
            $node = $xml_fast_pay_response->getElementsByTagName('ORef');
            $fast_pay_oref = "";
            foreach ($node as $node) {
                $fast_pay_oref = $node->textContent;
            }
            
            $node = $xml_fast_pay_response->getElementsByTagName('Value');
            $fast_pay_amount = "";
            foreach ($node as $node) {
                $fast_pay_amount = number_format((float) $node->textContent, 2);
            }
            
            // NOTE! This node seems to be unused
            $node = $xml_fast_pay_response->getElementsByTagName('PSPID');
            $fast_pay_ps_PID = "";
            foreach ($node as $node) {
                $fast_pay_ps_PID = $node->textContent;
            }

            //CONNECT WITH THE TOOL AND RETRIEVE THE LAST TRANSACTION
            $client = new SoapClient($this->wsdl, ["trace" => 0, "exception" => 0]);
            $soap_result = $client->getTransactionsByORef(
                [
                "MCHCode" => $this->payment_credentials['merchant_code'],
                "MCHPass" => $this->payment_credentials['password'],
                "Oref" => $fast_pay_oref]
            );

            $xml_tool_response = new DOMDocument();
            $xml_tool_response->loadXML($soap_result->getTransactionsByORefResult->any);

            $node = $xml_tool_response->getElementsByTagName('OrderRef');
            $tool_oref = "";
            foreach ($node as $node) {
                $tool_oref = $node->textContent;
            }
            
            // NOTE! This node seems to be unused
            $node = $xml_tool_response->getElementsByTagName('PSPID');
            $tool_ps_PID = "";
            foreach ($node as $node) {
                $tool_ps_PID = $node->textContent;
            }
            
            $node = $xml_tool_response->getElementsByTagName('Amount');
            $tool_amount = "";
            foreach ($node as $node) {
                $tool_amount = number_format((float) $node->textContent, 2);
            }

            //COMPARE THE RESULTS OF BOTH XMLs TO MAKE SURE THAT THEY MATCH
            if ($fast_pay_oref != $tool_oref) {
                $transaction->set([
                    'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                    'transaction_out_id' => $transaction_out_id
                ]);
                $transaction->save();

                $this->log(
                    'Apcopay payment status: Order reference mismatch',
                    Helpers_General::TYPE_ERROR,
                    [
                        'post' => Input::post(),
                        'server' => Input::server()
                    ]
                );
            }
            
            if ($fast_pay_amount != $tool_amount) {
                $transaction->set([
                    'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                    'transaction_out_id' => $transaction_out_id
                ]);
                $transaction->save();

                $this->log(
                    'Apcopay payment status: Amount mismatch',
                    Helpers_General::TYPE_ERROR,
                    [
                        'stpost' => Input::post(),
                        'server' => Input::server()
                    ]
                );

                return false;
            }

            return true;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Collects the response from FastPay and converts to DomDocument(XML)
     * @return \DOMDocument Returns an XML DomDocument object with the response of FastPay
     * @throws \Exception
     */
    private function get_params_and_convert_to_DOM_document()
    {
        $result_XML = [];
        
        try {
            //GET THE XML RESULT FROM THE FASTPAY
            if (isset($_POST['params'])) {
                //ONLY WHEN THIS INTEGRATION PAGE IS LIVE WILL BE RETRIEVED BY POST (localhost will not get XML via post)
                $result_XML = $_POST['params'];
            } elseif (isset($_GET['params'])) {
                //WHEN WORKING ON LOCAL HOST YOU WILL NOT GET THE RESULT FROM POST THEREFORE USE THE GET (QueryString)
                $result_XML = $_GET['params'];
            } else {
                throw new Exception("Could not retrieve the result XML from both the POST and the GET!!!!");
            }

            //CONVERT THE XML GAIVEN FROM FastPay TO DomDocument XmlObject
            $dom_XML = new DOMDocument();
            $dom_XML->loadXML(stripslashes($result_XML));
            return $dom_XML;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Gets the XML response from FastPay, replace the hash value of the Transaction tag with the secretword
     * and re-hash the xml to compare with the given hash value in the Transaction tag.
     *
     * @param type $fast_pay_XML The FastPay Xml response (DomDocument)
     * @param type $secret_word The Merchant's secret word, to be updated in the hash tag with it
     * @param type $result_value A string to be filled up with the result of the transaction if the XML hash matching is
     * correct
     * @return bool Where TRUE means that the validation is successful
     * @throws \Exception
     */
    private function re_check_MD5_validation_on_fast_pay_XML(
        $fast_pay_XML,
        $secret_word,
        &$result_value
    ) {
        try {
            //GET THE HASH VALUE, STORE TO BE USED FOR COMPARE AT A LATER STAGE AND REPLACE IT WITH THE SECRET WORD
            $sent_hash_value = "";
            $node = $fast_pay_XML->getElementsByTagName('Transaction');

            foreach ($node as $node) {
                $sent_hash_value = $node->getAttribute("hash");
                $node->setAttribute("hash", $secret_word);
                $fast_pay_XML->saveXML();
            }

            //RETRIEVE THE RESULT OF THE TRANSACTION TO KNOW WHETHER THE TRANSACTION WAS SUCCESSFUL OR NOT
            $node = $fast_pay_XML->getElementsByTagName('Result');
            foreach ($node as $node) {
                $result_value = $node->textContent;
            }

            //CONVERT $domXML BACK TO A STRING TO REMOVE ANY EXTRA TAGS AND SPACES THAT MIGHT EFFECT THE HASH
            $final_XML = $fast_pay_XML->saveXML($fast_pay_XML, LIBXML_NOEMPTYTAG);
            $final_XML = trim(substr($final_XML, strpos($final_XML, "<Transaction")));

            //validate Hash values
            return (strcmp(md5($final_XML), $sent_hash_value) == 0);
        } catch (\Exception $ex) {
            throw $ex;
        }
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
