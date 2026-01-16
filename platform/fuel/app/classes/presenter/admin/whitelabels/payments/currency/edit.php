<?php

/**
 * Description of Presenter_Admin_Whitelabels_Payments_Currency_Edit
 */
class Presenter_Admin_Whitelabels_Payments_Currency_Edit extends Presenter_Presenter
{
    use Presenter_Traits_Payments_Currency_Edit;
    
    /**
     *
     * @var string
     */
    private $begin_payments_currency_url = "";
    
    /**
     * The default view method Should set all expected variables upon itself
     */
    public function view(): void
    {
        $this->begin_payments_currency_url = "/whitelabels/payments/" .
            $this->whitelabel['id'] . '/currency/' .
            $this->current_kmethod_idx;
        
        $this->main_process();
    }
}
