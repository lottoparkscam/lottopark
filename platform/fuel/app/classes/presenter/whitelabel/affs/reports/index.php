<?php

/**
 * Description of Presenter_Whitelabel_Affs_Reports_Index
 */
class Presenter_Whitelabel_Affs_Reports_Index extends Presenter_Presenter
{
    use Presenter_Traits_Whitelabel_Reports_Commissions;
    
    public function view()
    {
        if (Input::get("filter.range_start") !== null) {
            // prepare commissions
            $this->set('commissions', $this->prepare_commissions());
            $this->set('casinoCommissions', $this->prepareCasinoCommissions());
        }
    }
}
