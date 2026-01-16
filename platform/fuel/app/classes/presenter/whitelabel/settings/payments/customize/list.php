<?php

/**
 * Class Presenter_Whitelabel_Settings_Payments_Customize_List for views/whitelabel/settings/payments/customize/list
 */
final class Presenter_Whitelabel_Settings_Payments_Customize_List extends Presenter_Presenter
{
    use Presenter_Traits_Payments_Customize_List;
    
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
