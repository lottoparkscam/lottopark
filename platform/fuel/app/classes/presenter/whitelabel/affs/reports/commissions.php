<?php

/**
 * Description of Presenter_Whitelabel_Affs_Reports_Commissions
 */
class Presenter_Whitelabel_Affs_Reports_Commissions extends Presenter_Presenter
{
    use Presenter_Traits_Whitelabel_Reports_Commissions;
    
    public function view()
    {
        // prepare commissions
        $this->set('commissions', $this->prepare_commissions());
    }
}
