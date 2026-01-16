<?php

use Fuel\Core\Input;
use Models\WhitelabelTransaction;
use Services\Logs\FileLoggerService;

abstract class Forms_Wordpress_Payment_Base extends Forms_Main
{
    const STATUS_SUCCESS = 0;
    const STATUS_PENDING = 1;
    const STATUS_ERROR = 2;
    const STATUS_COMMUNICATION_ERROR = 3;
    
    /**
     * Note! This should be override in the child class!
     *
     * @var int
     */
    protected $payment_method = Helpers_Payment_Method::TEST;
    
    /**
     *
     * @var array
     */
    protected $whitelabel = [];
    
    /**
     *
     * @var null|int
     */
    protected $whitelabel_id = null;
    
    /**
     *
     * @var array
     */
    protected $user = [];
    
    /**
     *
     * @var Model_Whitelabel_Transaction
     */
    protected $transaction = null;
    
    /**
     *
     * @var null|int
     */
    protected $transaction_id = null;

    /**
     *
     * @var null|Model_Whitelabel_Payment_Method
     */
    protected $model_whitelabel_payment_method = null;
    
    /**
     * Payment credentials
     * @var array
     */
    protected $payment_data = [];
    
    /**
     *
     * @var array
     */
    protected $errors = [];
    
    /**
     *
     * @var bool
     */
    protected $should_test = false;
    
    /**
     *
     * @param array $whitelabel
     * @return \Forms_Wordpress_Payment_Base
     */
    public function set_whitelabel(
        array $whitelabel = null
    ): Forms_Wordpress_Payment_Base {
        if (empty($whitelabel)) {
            if (Helpers_General::is_test_env()) {
                exit("Empty whitelabel.");
            }
            status_header(400);
            $this->log_error("Empty whitelabel.");
            exit($this->get_exit_text());
        }
        
        $this->whitelabel = $whitelabel;
        
        return $this;
    }
    
    /**
     *
     * @return array
     */
    public function get_whitelabel(): array
    {
        return $this->whitelabel;
    }
    
    /**
     *
     * @param int $whitelabel_id
     * @return \Forms_Wordpress_Payment_Base
     */
    public function set_whitelabel_id(
        int $whitelabel_id = null
    ): Forms_Wordpress_Payment_Base {
        $this->whitelabel_id = $whitelabel_id;
        
        return $this;
    }
    
    /**
     *
     * @param array $user
     * @return \Forms_Wordpress_Payment_Base
     */
    public function set_user(
        array $user = null
    ): Forms_Wordpress_Payment_Base {
        if (empty($user)) {
            if (Helpers_General::is_test_env()) {
                exit("Empty user.");
            }
            status_header(400);
            $this->log_error("Empty user.");
            exit($this->get_exit_text());
        }
        
        $this->user = $user;
        
        return $this;
    }

    /**
     *
     * @param Model_Whitelabel_Transaction $transaction
     * @return \Forms_Wordpress_Payment_Base
     */
    public function set_transaction(
        Model_Whitelabel_Transaction $transaction = null
    ): Forms_Wordpress_Payment_Base {
        if (empty($transaction)) {
            if (Helpers_General::is_test_env()) {
                exit("Empty transaction.");
            }
            status_header(400);
            $this->log_error("Empty transaction.");
            exit($this->get_exit_text());
        }
        
        $this->transaction = $transaction;
        
        return $this;
    }
    
    /**
     *
     * @return Model_Whitelabel_Transaction
     */
    public function get_transaction():? Model_Whitelabel_Transaction
    {
        return $this->transaction;
    }
    
    /**
     *
     * @param int $transaction_id
     * @return \Forms_Wordpress_Payment_Base
     */
    public function set_transaction_id(
        int $transaction_id = null
    ): Forms_Wordpress_Payment_Base {
        $this->transaction_id = $transaction_id;
        
        return $this;
    }
    
    /**
     *
     * @param Model_Whitelabel_Payment_Method $model_whitelabel_payment_method
     * @return \Forms_Wordpress_Payment_Base
     */
    public function set_model_whitelabel_payment_method(
        Model_Whitelabel_Payment_Method $model_whitelabel_payment_method = null
    ): Forms_Wordpress_Payment_Base {
        if (empty($model_whitelabel_payment_method)) {
            if (Helpers_General::is_test_env()) {
                exit("Empty whitelabel payment method model.");
            }
            status_header(400);
            $this->log_error("Empty whitelabel payment method model.");
            exit($this->get_exit_text());
        }
        
        $this->model_whitelabel_payment_method = $model_whitelabel_payment_method;
        
        return $this;
    }
    
    /**
     *
     * @return array
     */
    public function get_errors(): array
    {
        return $this->errors;
    }
    
    /**
     * @return void
     */
    public function check_credentials(): void
    {
        if ((int)$this->transaction->whitelabel_id !== (int)$this->whitelabel['id'] ||
            (int)$this->transaction->whitelabel_user_id !== (int)$this->user['id']
        ) {
            if (Helpers_General::is_test_env()) {
                exit("Bad request.");
            }
            status_header(400);
            $this->log_error("Bad request.");
            exit($this->get_exit_text());
        }
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
        }
        
        if (empty($this->payment_data)) {
            if (Helpers_General::is_test_env()) {
                exit("Empty payment data.");
            }
            status_header(400);
            $this->log_error("Empty payment data.");
            exit($this->get_exit_text());
        }
        
        return $this->payment_data;
    }
    
    abstract protected function check_merchant_settings(): void;
    
    /**
     *
     * @return Model_Whitelabel_Payment_Method
     */
    public function get_model_whitelabel_payment_method():? Model_Whitelabel_Payment_Method
    {
        return $this->model_whitelabel_payment_method;
    }
    
    /**
     *
     * @param int $status
     * @return void
     */
    protected function save_payment_method_id_for_transaction(
        int $status = null
    ): void {
        $set = [
            'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
            'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
        ];
        
        if (!empty($status)) {
            $set['status'] = $status;
        }
        
        $this->transaction->set($set);
        $this->transaction->save();
    }
    
    /**
     * Get transaction and check if exist
     *
     * @param string|null $token
     * @return null|Model_Whitelabel_Transaction
     */
    protected function get_transaction_by_token(
        string $token = null
    ):? Model_Whitelabel_Transaction {
        if (empty($token)) {
            status_header(400);
            $this->log_error("Empty token given.");
            exit($this->get_exit_text());
        }
        
        $token_int = intval(substr($token, 3));
        
        $this->log_to_error_file('Transaction Token: ' . $token);
        $this->log_to_error_file('Transaction Token_int: ' . $token_int);
        $this->log_to_error_file('Transaction Whitelabel_id: ' . $this->whitelabel['id']);
        
        $transactions = Model_Whitelabel_Transaction::find([
            "where" => [
                "whitelabel_id" => $this->whitelabel['id'],
                "token" => $token_int
            ]
        ]);

        if (!isset($transactions[0]['id'])) {
            status_header(400);
            
            $message = 'Transaction with token ' .
                $token .
                ' does not exist. ' .
                'WhitelabelID: ' .
                $this->whitelabel['id'] . '.';
            $this->log_to_error_file($message);
            
            $data_to_save = [];
            
            if (!empty(Input::post())) {
                $data_to_save['post'] = Input::post();
            }
            
            $this->log_error(
                'Transaction with token ' .
                $token .
                ' does not exist for whitelabel_id: ' .
                $this->whitelabel['id'],
                $data_to_save
            );
            return null;
        }
        
        $this->transaction = $transactions[0];
        
        return $this->transaction;
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
        
        // This function is within Traits_Payment_Method
        $whitelabel_id = $this->get_whitelabel_id();
        
        // This function is within Traits_Payment_Method
        $transaction_id = $this->get_transaction_id();
        
        // This function is within Traits_Payment_Method
        $whitelabel_payment_method_id = $this->get_whitelabel_payment_method_id();
        
        if (!empty($whitelabel_id)) {
            Model_Payment_Log::add_log(
                $type,
                Helpers_General::PAYMENT_TYPE_OTHER,
                $this->payment_method,
                null,
                $whitelabel_id,
                $transaction_id,
                $message,
                $data,
                $whitelabel_payment_method_id
            );
        }
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
     * @param bool $add_important_data
     * @return void
     */
    protected function log_info(
        string $message,
        array $data = [],
        bool $add_important_data = false
    ): void {
        $result = $data;
        
        if ($add_important_data) {
            $important_data = $this->get_important_data_to_log();

            $result = array_merge($data, $important_data);
        }
        
        $this->log($message, Helpers_General::TYPE_INFO, $result);
    }
    
    /**
     *
     * @param string $message
     * @param array $data
     * @return void
     */
    protected function log_error(
        string $message,
        array $data = []
    ): void {
        $important_data = $this->get_important_data_to_log();
        
        $result = array_merge($data, $important_data);
        
        $this->log($message, Helpers_General::TYPE_ERROR, $result);
    }
    
    /**
     *
     * @param string $message
     * @param array $data
     */
    protected function log_warning(
        string $message,
        array $data = []
    ): void {
        $important_data = $this->get_important_data_to_log();
        
        $result = array_merge($data, $important_data);
        
        $this->log($message, Helpers_General::TYPE_WARNING, $result);
    }
    
    /**
     *
     * @param string $message
     * @return void
     */
    protected function log_to_error_file(string $message): void
    {
        $fileLoggerService = Container::get(FileLoggerService::class);
        if ($this->should_test) {
            $fileLoggerService->error(
                $message
            );
        }
    }
    
    /**
     *
     * @return array
     */
    public function get_important_data_to_log(): array
    {
        $important_data = [];

        if (!empty($this->user)) {
            $important_data['user_data'] = $this->user;
        }
        
        if (!empty($this->transaction)) {
            $important_data['transaction_data'] = $this->transaction;
        }
        
        return $important_data;
    }
    
    /**
     *
     * @param int $type 0 - additional_text for success
     *                  2 - additional_text for error (failure)
     * @return void
     */
    protected function set_additional_text_on_failure_success_page(int $type = 0): void
    {
        switch ($type) {
            case self::STATUS_SUCCESS:

                break;
            case self::STATUS_ERROR:
                
                break;
        }
    }

    /**
     * @param WhitelabelTransaction $transaction
     * @return array<string, mixed>
     */
    public function getRequestDataLog(WhitelabelTransaction $transaction): array
    {
        return [
            'method' => Input::method(),
            'ip' => Input::ip(),
            'agent' => Input::user_agent(),
            'referrer' => Input::referrer(),
            'all' => Input::all(),
            'get' => Input::get(),
            'post' => Input::post(),
            'transaction' => $transaction
        ];
    }
}
