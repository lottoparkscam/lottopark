<?php

/**
 * Prepare data for views/aff/reports/index (reports).
 *
 * @author Marcin
 */
class Presenter_Aff_Reports_Index extends Presenter_Aff_Reports_Subaffs
{
    use Presenter_Traits_Aff_Reports_Name,
        Presenter_Traits_Aff_Reports_Ftps,
        Presenter_Traits_Aff_Reports_Commissions,
        Presenter_Traits_Aff_Reports_Leads,
        Presenter_Traits_Aff_Reports_Hide;

    /**
     * This method will execute after controller action and
     * before view rendering, so you can prepare necessary data here.
     */
    public function view()
    {
        $this->prepare_hidden_indicators();

        // prepare report data only if report is displaying something
        if (Input::get("filter.range_start") !== null) {
            // prepare leads names
            $this->set('commissions', $this->prepare_commissions());
            $this->set('casinoCommissions', $this->prepareCasinoCommissions());
            $this->set('regcount', $this->prepare_leads());
            $this->set('ftpcount', $this->prepare_ftps());
        }

        // prepare data for subaffs
        parent::prepare_for_subaffs($this);
    }
}
