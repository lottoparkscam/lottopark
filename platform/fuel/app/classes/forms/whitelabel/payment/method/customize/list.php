<?php

/**
 * Description of Forms_Whitelabel_Payment_Method_Customize_List
 */
class Forms_Whitelabel_Payment_Method_Customize_List extends Forms_Main
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
     * @var Presenter_Presenter|null
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
        
        if ($source === Helpers_General::SOURCE_ADMIN) {
            $template_path = "admin/whitelabels/payments/customize/list";  /// NEED CHANGE
        } elseif ($source === Helpers_General::SOURCE_WHITELABEL) {
            $template_path = "whitelabel/settings/payments/customize/list";
        }
        
        $this->inside = Presenter::forge($template_path);
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
     * @return Presenter_Presenter|null
     */
    public function get_inside():? Presenter_Presenter
    {
        return $this->inside;
    }

    /**
     *
     * @param int $whitelabel_payment_method_index Index for data within kmethods array
     * (for WHITELABEL, but for ADMIN this is strictly equal of the $whitelabel_payment_method_id)
     * @return int
     */
    public function process_form(int $whitelabel_payment_method_index): int
    {
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
        
        $payment_method_customize = Model_Whitelabel_Payment_Method_Customize::get_all_for_whitelabel_payment_method(
            (int)$whitelabel_payment_method_id
        );
        
        $counted_languages = Model_Whitelabel_Language::get_counted_for_whitelabel((int)$this->whitelabel['id']);
        
        $show_new_button = true;
        if ($counted_languages > 0 &&
            $counted_languages === count($payment_method_customize)
        ) {
            $show_new_button = false;
        }
        $this->inside->set("show_new_button", $show_new_button);
        
        $this->inside->set("current_kmethod_idx", $current_whitelabel_payment_method_index);
        $this->inside->set("payment_method_customize", $payment_method_customize);
        
        if ((int)$this->source === Helpers_General::SOURCE_ADMIN) {
            $this->inside->set("whitelabel", $this->whitelabel);
        }
        
        return self::RESULT_OK;
    }
}
