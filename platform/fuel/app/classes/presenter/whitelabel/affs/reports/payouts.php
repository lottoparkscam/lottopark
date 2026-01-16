<?php

/**
 * Description of Presenter_Whitelabel_Affs_Reports_Payouts
 */
class Presenter_Whitelabel_Affs_Reports_Payouts extends Presenter_Presenter
{
    use Presenter_Traits_Whitelabel_Reports_Payouts;
    
    public function view()
    {
        // prepare commissions
        $this->set('payouts', $this->prepare_payouts());
    }
}
