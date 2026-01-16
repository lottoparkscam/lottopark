<?php

/**
 * Class Presenter_Whitelabel_Settings_Payments_Currency_List for views/whitelabel/settings/payments/currency/list
 */
class Presenter_Whitelabel_Settings_Payments_Currency_List extends Presenter_Presenter
{
    use Presenter_Traits_Payments_Currency_List;
    
    /**
     *
     * @var string
     */
    private $start_url = "";
    
    /**
     * The default view method Should set all expected variables upon itself
     */
    public function view(): void
    {
        $this->start_url = "/paymentmethods";
        
        $this->main_process();
    }
}
