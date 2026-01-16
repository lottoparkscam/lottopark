<?php

/**
 * Description of Presenter_Admin_Whitelabels_Payments_List
 */
class Presenter_Admin_Whitelabels_Payments_List extends Presenter_Presenter
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
        $this->payment_methods_start_url = "/whitelabels/payments/" .
            $this->whitelabel['id'];
        $this->ccpayment_methods_start_url = "/whitelabels/ccpayments/" .
            $this->whitelabel['id'];

        $this->main_process();
    }
}
