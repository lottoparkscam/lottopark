<?php

use Fuel\Core\Response;
use Services\Logs\FileLoggerService;

final class Forms_Wordpress_Payment_Piastrix implements Forms_Wordpress_Payment_Process
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
            Helpers_Payment_Method::PIASTRIX,
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
     * @return \Forms_Wordpress_Payment_Piastrix
     */
    public function set_model_whitelabel_payment_method(
        Model_Whitelabel_Payment_Method $model_whitelabel_payment_method = null
    ): Forms_Wordpress_Payment_Piastrix {
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
        $validation = Validation::forge("piastrix");
        
        return $validation;
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
            (int)$this->model_whitelabel_payment_method->payment_method_id === Helpers_Payment_Method::PIASTRIX)
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
     * @return void
     */
    public function process_form(): void
    {
        if ($this->transaction->whitelabel_id != $this->whitelabel['id'] ||
            $this->transaction->whitelabel_user_id != $this->user['id']
        ) {
            $this->log_error("Bad request.");
            
            exit(_("Bad request! Please contact us!"));
        }
        
        $payment_params = unserialize($this->model_whitelabel_payment_method['data']);
        
        if (empty($payment_params['shop_id']) || empty($payment_params['secret_key'])) {
            $this->transaction->set([
                'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
                'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
            ]);
            $this->transaction->save();
            
            $this->log_error("Empty Shop ID or Secret Key.");
            
            exit(_("Bad request! Please contact us!"));
        }

        $amount = $this->transaction->amount_payment;
        
        $transaction_text = $this->get_prefixed_transaction_token();

        $currency_code = $this->get_payment_currency($this->transaction->payment_currency_id);
        $currency_iso_code = $this->get_currency_iso_code(
            Helpers_Payment_Method::PIASTRIX,
            $currency_code
        );
        
        $language = ($this->code == 'ru' ? 'ru' : 'en');
        
        $request = [
            "shop_amount" => $amount,
            "shop_currency" => $currency_iso_code,
            "shop_id" => $payment_params['shop_id'],
            "shop_order_id" => $transaction_text,
        ];
        
        $sign = implode(':', $request) . $payment_params['secret_key'];
        $sign = hash("sha256", $sign);

        $request['sign'] = $sign;
        $request['description'] = $transaction_text;

        $piastrix = View::forge("wordpress/payment/piastrix");
        $piastrix->set("whitelabel", $this->whitelabel);
        $piastrix->set("transaction", $this->transaction);
        $piastrix->set("pdata", $payment_params);
        $piastrix->set("user", $this->user);
        $piastrix->set("request", $request); // euro
        $piastrix->set("lang", $language);
        
        $this->transaction->set([
            'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
            'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
        ]);
        $this->transaction->save();
        
        $this->log_success("Redirecting to piastrix.com.");
        
        ob_clean();
        
        echo $piastrix;
    }
    
    /**
     *
     * @param array $post
     */
    private function prepare_data($post)
    {
        $this->data['status'] = $post["status"];
        $this->data['shop_id'] = $post["shop_id"];
        
        if (!empty($post['description'])) {
            $this->data['description'] = $post["description"];
        }
        if (!empty($post['shop_amount'])) {
            $this->data['shop_amount'] = $post["shop_amount"];
        }
        if (!empty($post['shop_refund'])) {
            $this->data['shop_refund'] = $post["shop_refund"];
        }
        if (!empty($post['shop_currency'])) {
            $this->data['shop_currency'] = $post['shop_currency'];
        }
        if (!empty($post['payment_id'])) {
            $this->out_id = $post["payment_id"];
        }
        if (!empty($post['client_price'])) {
            $this->data['client_price'] = $post["client_price"];
        }
        if (!empty($post['ps_currency'])) {
            $this->data['ps_currency'] = $post["ps_currency"];
        }
        if (!empty($post['payway'])) {
            $this->data['payway'] = $post["payway"];
        }
        if (!empty($post['ps_data'])) {
            $ps_data = json_decode($post['ps_data'], true);
            $this->data['ps_data'] = $ps_data;
        }
        if (!empty($post['created'])) {
            $this->data['created'] = $post["created"];
        }
        if (!empty($post['processed'])) {
            $this->data['processed'] = $post["processed"];
        }
        if (!empty($post['addons'])) {
            $this->data['addons'] = $post["addons"];
        }
        if (!empty($post['test_add_on'])) {
            $this->data['test_add_on'] = $post["test_add_on"];
        }
    }
    
    /**
     *
     * @return void
     */
    public function process_confirmation(): void
    {
        try {
            if (empty(Input::post())) {
                status_header(400);
                
                $this->log_error("Empty POST.");
                
                exit(_("Bad request! Please contact us!"));
            }
            
            $this->log_info(
                "Received confirmation.",
                Input::post()
            );
            
            $transaction_token = intval(substr(Input::post("shop_order_id"), 3));
            
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

            $post = Input::post();
            $post['ps_data'] = stripslashes($post['ps_data']);

            ksort($post);
            $signature = $post['sign'];
            unset($post['sign']);

            $sigverify = implode(':', $post);
            $sigverify .= $pay_data['secret_key'];

            if (hash("sha256", $sigverify) !== $signature) {
                status_header(400);
                
                $this->log_error("Bad signature.");
                
                exit(_("Bad request! Please contact us!"));
            }

            $this->prepare_data($post);

            if ($this->data['status'] == "success") {
                $this->ok = true;
                
                $this->log_success("Confirmation successfully processed.");
            } else {
                // it should be marked as failed already, but let's add more data

                $this->transaction->set([
                    'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                    'transaction_out_id' => $this->out_id,
                    'additional_data' => serialize($this->data)
                ]);
                $this->transaction->save();
                
                $this->log_error("Received confirmation with canceled status.");
            }
            echo 'OK';
        } catch (Exception $e) {
            status_header(400);
            
            $this->log_error(
                "Unknown error: " . $e->getMessage(),
                [$e]
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
