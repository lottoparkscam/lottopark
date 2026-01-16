<?php

/**
 * Description of Forms_Whitelabel_Payment_Currency_List
 */
class Forms_Whitelabel_Payment_Method_Currency_List extends Forms_Main
{
    /**
     *
     * @var int
     */
    private $source;
    
    /**
     *
     * @var array
     */
    private $whitelabel = [];

    /**
     *
     * @var View
     */
    private $inside = null;

    /**
     *
     * @var array
     */
    private $whitelabel_payment_methods_indexed = [];
    
    /**
     *
     * @param int $source
     * @param array $whitelabel
     * @param array $whitelabel_payment_methods_indexed
     */
    public function __construct(
        int $source,
        array $whitelabel,
        array $whitelabel_payment_methods_indexed
    ) {
        $this->source = $source;
        $this->whitelabel = $whitelabel;
        $this->whitelabel_payment_methods_indexed = $whitelabel_payment_methods_indexed;
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
     * @return View
     */
    public function get_inside()
    {
        return $this->inside;
    }

    /**
     *
     * @param int $whitelabel_payment_method_index Index for data within
     * whitelabel_payment_methods_indexed array
     * (for WHITELABEL, but for ADMIN this is strictly equal of the $whitelabel_payment_method_id)
     * @param string $template_path
     * @return int
     */
    public function process_form(
        int $whitelabel_payment_method_index,
        string $template_path
    ): int {
        $whitelabel_payment_method_id = 0;
        $current_whitelabel_payment_method_index = 0;
        if ((int)$this->source === Helpers_General::SOURCE_ADMIN) {
            $whitelabel_payment_method_id = $whitelabel_payment_method_index;
            $current_whitelabel_payment_method_index = $whitelabel_payment_method_index;
        } else {
            $whitelabel_payment_method_id = (int)$this->whitelabel_payment_methods_indexed[$whitelabel_payment_method_index]['id'];
            // Because it is previously decreased
            $current_whitelabel_payment_method_index = $whitelabel_payment_method_index + 1;
        }
        
        $whitelabel_payment_method = Model_Whitelabel_Payment_Method::find_by_pk($whitelabel_payment_method_id);
        
        if ($whitelabel_payment_method === null ||
            (int)$whitelabel_payment_method->whitelabel_id !== (int)$this->whitelabel['id']
        ) {
            return self::RESULT_WRONG_PAYMENT_METHOD;
        }
        
        $payment_method_currencies = Model_Whitelabel_Payment_Method_Currency::get_all_for_whitelabel_payment_method(
            (int)$whitelabel_payment_method_id
        );
        
        $this->inside = Presenter::forge($template_path);
        $this->inside->set("current_kmethod_idx", $current_whitelabel_payment_method_index);
        $this->inside->set("payment_method_currencies", $payment_method_currencies);
        
        $this->inside->set("source", $this->source);
        
        if ((int)$this->source === Helpers_General::SOURCE_ADMIN) {
            $this->inside->set("whitelabel", $this->whitelabel);
        }
        
        return self::RESULT_OK;
    }
}
