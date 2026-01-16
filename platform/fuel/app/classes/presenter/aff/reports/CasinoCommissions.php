<?php

/**
 * View for: platform/fuel/app/views/aff/reports/CasinoCommissions.php
*/
class Presenter_Aff_Reports_CasinoCommissions extends Presenter_Aff_Reports_Subaffs
{
    use Presenter_Traits_Aff_Reports_Name,
        Presenter_Traits_Aff_Reports_Commissions,
        Presenter_Traits_Aff_Reports_Hide;

    /**
     * This method will execute after controller action and before view rendering, so you can prepare necessary data here.
     */
    public function view()
    {
        $this->prepare_hidden_indicators();

        // prepare commissions
        $this->set('casinoCommissions', $this->prepareCasinoCommissions());

        // prepare data for subaffs TODO: this should be changed into trait
        parent::prepare_for_subaffs($this);
    }
}
