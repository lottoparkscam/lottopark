<?php

/**
 * Description of Presenter_Whitelabel_Settings_Payments_List
 */
class Presenter_Whitelabel_Settings_Payments_List extends Presenter_Presenter
{
    use Presenter_Traits_Payments_List;
    
    /**
     *
     * @var string
     */
    private $payment_methods_start_url = "";
    
    /**
     *
     * @var string
     */
    private $ccpayment_methods_start_url = "";
    
    /**
     * The default view method Should set all expected variables upon itself
     */
    public function view(): void
    {
        $this->payment_methods_start_url = "/paymentmethods";
        $this->ccpayment_methods_start_url = "/ccsettings";
        
        $this->main_process();
    }
}
