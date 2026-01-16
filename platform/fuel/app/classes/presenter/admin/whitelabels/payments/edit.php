<?php
/**
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 14.03.2019
 * Time: 09:33
 */

/**
 * Class Presenter_Admins_Whitelabels_Payments_Edit for views/admin/whitelabels/payments/edit
 */
class Presenter_Admin_Whitelabels_Payments_Edit extends Presenter_Presenter
{ // 20.03.2019 11:06 Vordis TODO: shared with Presenter_Whitelabel_Settings_Payments_Edit

    use Presenter_Traits_Payments_Edit;

    /**
     *
     * @var string
     */
    private $begin_payments_url = '';
    
    /**
     * The default view method Should set all expected variables upon itself
     */
    public function view(): void
    {
        $this->begin_payments_url = "/whitelabels/payments/" . $this->whitelabel['id'];
        
        $this->main_process();
    }
}
