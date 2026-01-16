<?php

use Fuel\Core\Validation;

/**
 * Description of Forms_Wordpress_Withdrawal_Bank
 */
class Forms_Wordpress_Withdrawal_Bank extends Forms_Main
{
    /**
     *
     * @var string
     */
    private $form_name = "";
    
    /**
     * @param array $form_name
     */
    public function __construct($form_name)
    {
        $this->form_name = $form_name;
    }

    /**
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge($this->form_name);
        
        $validation->add("withdrawal.add.name", _("First Name"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'singlequotes', 'utf8']);

        $validation->add("withdrawal.add.surname", _("Last Name"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'singlequotes', 'utf8']);

        $validation->add("withdrawal.add.address", _("Address"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'numeric', 'dashes', 'spaces', 'commas', 'dots', 'backwardslashes', 'forwardslashes', 'utf8']);

        $validation->add("withdrawal.add.account_no", _("IBAN / Account number"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'spaces', 'numeric']);

        $validation->add("withdrawal.add.account_swift", _("Routing number / SWIFT / BIC"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'numeric']);

        $validation->add("withdrawal.add.bank_name", _("Bank name"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'numeric', 'specials', 'dashes', 'spaces', 'dots', 'commas', 'utf8']);

        $validation->add("withdrawal.add.bank_address", _("Bank Address"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'numeric', 'dashes', 'spaces', 'commas', 'dots', 'backwardslashes', 'forwardslashes', 'utf8']);
        
        return $validation;
    }
}
