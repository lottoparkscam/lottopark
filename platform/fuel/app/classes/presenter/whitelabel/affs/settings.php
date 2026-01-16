<?php

/**
 * Prepare data for views/whitelabel/affs/settings.
 *
 * @author Marcin
 */
class Presenter_Whitelabel_Affs_Settings extends Presenter_Presenter
{
    /**
     * This method will execute after controller action and before view rendering, so you can prepare necessary data here.
     */
    public function view()
    {
        $registration_url = 'https://aff.' . $this->whitelabel['domain'];
        $registration_url_help_block = sprintf(
            _("Affiliate registration URL: <strong>%s</strong>."),
            $registration_url
        );
        $this->set("registration_url_help_block", $registration_url_help_block);
        
        $activation_help_block_text = _(
            "Be aware that changing the e-mail activation type " .
            "from no activation or optional level to required level " .
            "will make all your not-confirmed users inactive! " .
            "In other way, changing activation type from required " .
            "level to optional or no activation level will make " .
            "all your not-confirmed users active!"
        );
        $this->set("activation_help_block_text", $activation_help_block_text);
        
        // set tool for checkboxes
        $this->set_safe("get_checked_extended", parent::closure_get_checked_extended());
        
        // set lifetimes for select
        $this->set("ref_lifetimes", array_slice(self::get_lifetimes(_("unlimited")), 0, 9)); // no need for processing with htmlentites, since these are raw values from functino - no possible injection.
        
        // get and set selection tool for options
        $this->set_safe("get_selected_extended", parent::closure_get_selected_extended());
    }
}
