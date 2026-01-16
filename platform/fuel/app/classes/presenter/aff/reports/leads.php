<?php

/**
 * Prepare data for views/aff/reports/leads.
 *
 * @author Marcin
 */
class Presenter_Aff_Reports_Leads extends Presenter_Aff_Reports_Subaffs
{

    use Presenter_Traits_Aff_Reports_Name;
    use Presenter_Traits_Aff_Reports_Leads;

    /**
     * This method will execute after controller action and before view rendering, so you can prepare necessary data here.
     */
    public function view()
    {
        // 22.03.2019 13:38 Vordis TODO: share them across other report views.
        $this->set('is_lead_id_visible', !$this->user['hide_lead_id']);
        $this->set('is_transaction_id_visible', !$this->user['hide_transaction_id']);
        // prepare leads
        $this->set('regcount', $this->prepare_leads());

        // prepare data for subaffs
        parent::prepare_for_subaffs($this);
    }
}
