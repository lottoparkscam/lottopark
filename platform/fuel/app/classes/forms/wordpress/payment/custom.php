<?php

use Fuel\Core\Validation;
use Fuel\Core\Response;

/**
 * Description of Forms_Wordpress_Payment_Custom
 */
final class Forms_Wordpress_Payment_Custom extends Forms_Wordpress_Payment_Base implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method;
    
    /**
     *
     * @var int
     */
    protected $payment_method = Helpers_Payment_Method::CUSTOM;
    
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
        $validation = Validation::forge("custom");
        
        return $validation;
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
     * This function is for outside use so it is public
     * but the body is almost the same as check_merchant_settings() function
     * which is protected and could not be visible outside
     *
     * @return bool
     */
    public function check_is_custom_url_to_redirect_set(): bool
    {
        $this->get_payment_data();
        
        if (empty($this->payment_data['custom_url_to_redirect'])) {
            $this->errors = [
                'payment' => _("Unknown error! Please contact us!")
            ];
            return false;
        }
        
        return true;
    }
    
    /**
     *
     * @return string|null
     */
    public function get_payment_url(): string
    {
        $this->get_payment_data();
        
        $payment_url = "";
        if (!empty($this->payment_data['custom_url_to_redirect'])) {
            $payment_url = $this->payment_data['custom_url_to_redirect'];
        }
        
        return $payment_url;
    }
    
    /**
     *
     */
    public function process_form(): void
    {
        $this->log_info('Begin payment process.', [], true);
        
        $this->check_credentials();
        
        $this->check_merchant_settings();
        
        $url_to_redirect = $this->get_payment_url();
        
        if (empty($url_to_redirect)) {
            status_header(400);
            
            $this->log_error("Empty URL to redirect for Whitelabel.");
            exit($this->get_exit_text());
        }
        
        $this->save_payment_method_id_for_transaction();
        
        $this->log_info('Redirect to custom URL.', [], true);
        
        // redirect to thank you page!!
        Response::redirect($url_to_redirect);
    }

    /**
     * @return void
     */
    protected function check_merchant_settings(): void
    {
        $this->get_payment_data();
        
        // In that payment method the URL is not required, but reaction
        // on that fact is on the Front-end -> Pay Now button is hidden
        if (empty($this->payment_data['custom_url_to_redirect'])) {
            $this->log_error("Empty custom_url_to_redirect.");
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
        $ok = false;
        
        return $ok;
    }
}
