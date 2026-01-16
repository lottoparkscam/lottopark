<?php

use Fuel\Core\Input;
use Fuel\Core\Session;
use Fuel\Core\Validation;

class Forms_Wordpress_Link_Content extends Forms_Main
{
    protected function validate_form(): Validation
    {
        $val = Validation::forge('content');
        $val->add('content')
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('min_length', 1)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);

        return $val;
    }

    public function process_form(): void
    {
        $val = $this->validate_form();
        if ($val->run(Input::get())) {
            Session::set('content', Input::get('content'));
        }
    }
}
