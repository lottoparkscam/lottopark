<?php

use Fuel\Core\Validation;

/**
 *
 */
abstract class Forms_Main implements Forms_Status
{
    
    /**
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $validation = Validation::forge();
            
        return $validation;
    }
    
    /**
     *
     * @return string
     */
    public function get_exit_text(): string
    {
        $exit_text = _("Bad request! Please contact us!");
        return $exit_text;
    }
}
