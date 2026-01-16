<?php

/**
 * Presenter for views\whitelabel\users\edit.php
 */
class Presenter_Whitelabel_Users_Edit extends Presenter_Presenter
{
    /**
     * The default view method Should set all expected variables upon itself
     */
    public function view(): void
    {
        // set closure for error classes, view will check using closure.
        $this->set_safe('has_error', $this->closure_input_has_error_class());
        // set closures for input values
        $closure_last_value = $this->closure_input_last_value('user');
        $this->set_safe('last_value', $closure_last_value);
        $this->set_safe('selected', parent::closure_selected($closure_last_value));
    }
    
}
