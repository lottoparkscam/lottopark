<?php

/**
 * Trait for preparation of leads in presenter.
 * @author Marcin Klimek <marcin.klimek at gg.international>
 */
trait Presenter_Traits_Aff_Reports_Leads
{

    /**
     * Prepare leads.
     * @return array prepared leads.
     */
    private function prepare_leads(): array
    {
        $prepared_leads = [];
        // go over every row of leads and prepare them
        foreach ($this->regcount as $lead) {
            // todo: prepare rest of data here, instead of view
            $lead['lead_name'] = $this->prepare_lead_name($lead);
            $lead['lead_email'] = Security::htmlentities($lead['lead_email']);
            $prepared_leads[] = $lead; // add new entry to prepared leads
        }

        // return prepared values
        return $prepared_leads;
    }
}
