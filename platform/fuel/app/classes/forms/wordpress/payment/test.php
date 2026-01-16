<?php

use Fuel\Core\Response;
use Fuel\Core\Validation;
use Services\Logs\FileLoggerService;

final class Forms_Wordpress_Payment_Test extends Forms_Main implements Forms_Wordpress_Payment_Process
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
            Helpers_Payment_Method::TEST,
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
     * @return \Forms_Wordpress_Payment_Test
     */
    public function set_user(array $user): Forms_Wordpress_Payment_Test
    {
        $this->user = $user;
        
        return $this;
    }

    /**
     *
     * @param Model_Whitelabel_Transaction $transaction
     * @return \Forms_Wordpress_Payment_Test
     */
    public function set_transaction(
        Model_Whitelabel_Transaction $transaction = null
    ): Forms_Wordpress_Payment_Test {
        if (empty($transaction)) {
            status_header(400);
            $this->log_error("Bad request.");
            exit($this->get_exit_text());
        }
        
        $this->transaction = $transaction;
        
        return $this;
    }

    /**
     *
     * @param Model_Whitelabel_Payment_Method $model_whitelabel_payment_method
     * @return \Forms_Wordpress_Payment_Test
     */
    public function set_model_whitelabel_payment_method(
        Model_Whitelabel_Payment_Method $model_whitelabel_payment_method = null
    ): Forms_Wordpress_Payment_Test {
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
                Helpers_Payment_Method::TEST;
            $this->log_error($error_message);
            exit($this->get_exit_text());
        }
        
        $whitelabel_id = (int)$this->model_whitelabel_payment_method->whitelabel_id;
        
        $this->whitelabel = Model_Whitelabel::get_single_by_id($whitelabel_id);
        
        if (empty($this->whitelabel)) {
            $this->log_to_error_file("No whitelabel data found.");
            
            $error_message = "No whitelabel data found. Payment method of ID: " .
                Helpers_Payment_Method::TEST;
            $this->log_error($error_message);
            exit($this->get_exit_text());
        }
        
        return $this->whitelabel;
    }
    
    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge("test");
        
        return $validation;
    }
    
    /**
     *
     */
    public function process_form(): void
    {
        if ((int)$this->transaction->whitelabel_id !== (int)$this->whitelabel['id'] ||
            (int)$this->transaction->whitelabel_user_id !== (int)$this->user['id']
        ) {
            $this->log_error("Bad request.");
            
            exit(_("Bad request! Please contact us!"));
        }
        
        $this->transaction->set([
            'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
            'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
        ]);
        
        // Save transaction data before rest of transaction to accept
        $this->transaction->save();
        
        $accept_transaction_result = Lotto_Helper::accept_transaction(
            $this->transaction,
            null,
            null,
            $this->whitelabel
        );

        // Now transaction returns result as INT value and
        // we can redirect user to fail page or success page
        // or simply inform system about that fact
        if ($accept_transaction_result === Forms_Transactions_Accept::RESULT_WITH_ERRORS) {

            $this->log_error("Payment failure.");

            // redirect to thank you page!!
            Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
        }
        
        $success_url = lotto_platform_home_url(Helper_Route::ORDER_SUCCESS);
        
        $this->log_success("Payment successful.");
        
        // redirect to thank you page!!
        Response::redirect($success_url);
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
     * @return void
     */
    public function confirm_payment(
        Model_Whitelabel_Transaction &$transaction = null,
        string &$out_id = null,
        array &$data = []
    ): bool {
        $this->get_whitelabel_from_whitelabel_payment_method();
        
        $this->log_info("Confirmation begin");
        
        echo "TEST CONFIRM - nothing really is confirmed<br><br>";
        
        $transaction = $this->transaction;
        
        echo 'Whitelabel_payment_method_id: ' .
            $this->get_whitelabel_payment_method_id() . '<br>';
        
        var_dump($transaction);
        var_dump($out_id);
        var_dump($data);
        
        $ok = true;
        
        return $ok;
    }
}
