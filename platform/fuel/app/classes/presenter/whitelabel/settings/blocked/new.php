<?php

/**
 * Prepare data for views/whitelabel/blocked/countries/new
 *
 * @author Marcin Klimek <marcin.klimek at gg.international>
 */
class Presenter_Whitelabel_Settings_Blocked_New extends Presenter_Presenter
{

    /**
     * This method will execute after controller action and before view rendering, so you can prepare necessary data here.
     */
    public function view()
    {
        $this->set_safe("input_has_error", parent::closure_input_has_error());
        $this->set_safe("post_selected", parent::closure_post_selected());
    }
}
