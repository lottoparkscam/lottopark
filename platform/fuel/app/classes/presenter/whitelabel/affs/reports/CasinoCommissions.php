<?php

/**
 * View for: platform/fuel/app/views/whitelabel/affs/reports/CasinoCommissions.php
*/
class Presenter_Whitelabel_Affs_Reports_CasinoCommissions extends Presenter_Presenter
{
    use Presenter_Traits_Whitelabel_Reports_Commissions;
    
    public function view()
    {
        $this->set('casinoCommissions', $this->prepareCasinoCommissions());
    }
}
