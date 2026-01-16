<?php

/**
 *
 */
trait Traits_Payment_Method
{
    /**
     *
     * @var string
     */
    private $ip = "";
    
    /**
     *
     * @var string
     */
    private $code = "";
    
    /**
     *
     * @var array
     */
    private $lotteries = [];
    
    /**
     *
     * @var int
     */
    private $whitelabel_payment_method_id = null;
    
    /**
     *
     * @param int $payment_currency_id
     * @return string
     */
    protected function get_payment_currency(
        int $payment_currency_id
    ): string {
        $payment_currency_tab = Helpers_Currency::get_mtab_currency(
            false,
            null,
            $payment_currency_id
        );
        
        $payment_currency_code = $payment_currency_tab['code'];
        
        return $payment_currency_code;
    }
    
    /**
     *
     * @return string
     */
    protected function get_prefixed_transaction_token(): string
    {
        $token = $this->whitelabel['prefix'];
        if ((int)$this->transaction->type === Helpers_General::TYPE_TRANSACTION_PURCHASE) {
            $token .= 'P';
        } else {
            $token .= 'D';
        }
        $token .= $this->transaction->token;
        
        return $token;
    }
    
    /**
     *
     * @param array $user
     * @return string
     */
    protected function get_prefixed_user_token(array $user): string
    {
        $prefixed_user_id = $this->whitelabel['prefix'] . 'U' .
            $user['token'];
        
        return $prefixed_user_id;
    }
    
    /**
     *
     * @param string $code 2 characters string of the language as code
     */
    public function set_code(string $code): void
    {
        $this->code = $code;
    }
    
    /**
     *
     * @param array $lotteries
     * @return void
     */
    public function set_lotteries(array $lotteries): void
    {
        $this->lotteries = $lotteries;
    }
    
    /**
     *
     * @param string $ip
     * @return void
     */
    public function set_ip(string $ip): void
    {
        $this->ip = $ip;
    }
    
    /**
     *
     * @return int|null
     */
    public function get_whitelabel_id():? int
    {
        $whitelabel_id = null;
        if (!empty($this->whitelabel['id'])) {
            $whitelabel_id = (int)$this->whitelabel['id'];
        } elseif (isset($this->whitelabel_id) &&
            !empty($this->whitelabel_id)
        ) {
            $whitelabel_id = (int)$this->whitelabel_id;
        }
        
        return $whitelabel_id;
    }
    
    /**
     *
     * @return int|null
     */
    public function get_transaction_id():? int
    {
        $transaction_id = null;
        if (!empty($this->transaction->id)) {
            $transaction_id = (int)$this->transaction->id;
        } elseif (isset($this->transaction_id) &&
            !empty($this->transaction_id)
        ) {
            $transaction_id = (int)$this->transaction_id;
        }
        
        return $transaction_id;
    }
    
    /**
     *
     * @param int $whitelabel_payment_method_id
     * @return void
     */
    public function set_whitelabel_payment_method_id(
        int $whitelabel_payment_method_id
    ): void {
        $this->whitelabel_payment_method_id = $whitelabel_payment_method_id;
    }
    
    /**
     *
     * @return null|int
     */
    public function get_whitelabel_payment_method_id():? int
    {
        if (!empty($this->whitelabel_payment_method_id)) {
            return $this->whitelabel_payment_method_id;
        } elseif (!empty($this->model_whitelabel_payment_method['id'])) {
            return (int)$this->model_whitelabel_payment_method['id'];
        } elseif (!empty($this->transaction->whitelabel_payment_method_id)) {
            return (int)$this->transaction->whitelabel_payment_method_id;
        }
        return null;
    }
}
