<?php

/**
 * Class Presenter_Admin_Whitelabels_Payments_Customize_List for views/admin/whitelabels/payments/customize/list
 */
final class Presenter_Admin_Whitelabels_Payments_Customize_List extends Presenter_Presenter
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
        $this->start_url = "/whitelabels/payments/" . $this->whitelabel['id'];

        $this->main_process();
    }
}
