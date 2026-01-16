<?php
/**
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 22.03.2019
 * Time: 12:09
 */

/**
 * Trait for preparation of edit/new views input fields.
 */
trait Presenter_Traits_Whitelabel_Affs_Input
{

    /**
     * @return Void
     */
    private function prepare_input_fields(): Void
    {
        // set tool for checkboxes
        $this->set_safe("post_checked_extended", parent::closure_post_checked_extended());

        // set lifetimes for select
        $this->set("lead_lifetimes", parent::get_lifetimes(_("unlimited"))); // no need for processing with htmlentites, since these are raw values from functino - no possible injection.
        // get and set selection tool for options
        $this->set_safe("post_selected_extended", parent::closure_post_selected_extended());
    }
}