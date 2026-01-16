<?php

/**
 * Prepare data for views/whitelabel/affs/view.
 *
 * @author Marcin
 */
class Presenter_Whitelabel_Affs_View extends Presenter_Presenter
{
    /**
     * This method will execute after controller action and before view rendering, so you can prepare necessary data here.
     */
    public function view()
    {
        // set lead lifetime details
        $this->set("lead_lifetime_label", Security::htmlentities(_("Lead lifetime")));
        $lead_value = parent::get_lifetimes(_("unlimited"))[$this->user['aff_lead_lifetime']];
        $this->set("lead_lifetime_value", Security::htmlentities($lead_value));

        // show name and surname of affiliate option
        $this->set('is_show_name_label', parent::prepare_nullable(_('Show name and surname of the leads'))); // 22.03.2019 10:44 Vordis TODO: awkward name of the field in database
        $this->set('is_show_name_value', parent::prepare_bool($this->user['is_show_name']));

        // hide lead
        $this->set('hide_lead_id_label', parent::prepare_nullable(_('Hide lead IDs'))); // 22.03.2019 10:44 Vordis TODO: awkward name of the field in database
        $this->set('hide_lead_id_value', parent::prepare_bool($this->user['hide_lead_id']));

        // hide transaction
        $this->set('hide_transaction_id_label', parent::prepare_nullable(_('Hide transaction IDs'))); // 22.03.2019 10:44 Vordis TODO: awkward name of the field in database
        $this->set('hide_transaction_id_value', parent::prepare_bool($this->user['hide_transaction_id']));

    }
}
