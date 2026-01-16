<?php

/**
 * Class Presenter_Whitelabel_Settings_Payments_Currency_Edit for views/whitelabel/settings/payments/currency/edit
 */
class Presenter_Whitelabel_Settings_Payments_Currency_Edit extends Presenter_Presenter
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
        $this->begin_payments_currency_url = '/paymentmethods/currency/' .
            $this->current_kmethod_idx;
        
        $this->main_process();
    }
}
