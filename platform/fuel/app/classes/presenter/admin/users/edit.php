<?php

/**
 * Presenter for views\admin\users\edit.php
 */
class Presenter_Admin_Users_Edit extends Presenter_Presenter
{ // TODO: {Vordis 2019-05-27 15:13:49} shared with Presenter_Whitelabel_Users_Edit, but it's fine as it is, because it's very short
    /**
     * The default view method Should set all expected variables upon itself
     */
    public function view(): void
    { // TODO: {Vordis 2019-05-27 15:16:51} this could be pushed onto higher layer of abstraction (parent), only 'user' is unique here.
        // set closure for error classes, view will check using closure.
        $this->set_safe('has_error', $this->closure_input_has_error_class()); 
        // set closures for input values
        $closure_last_value = $this->closure_input_last_value('user');
        $this->set_safe('last_value', $closure_last_value);
        $this->set_safe('selected', parent::closure_selected($closure_last_value));
    }
    
}