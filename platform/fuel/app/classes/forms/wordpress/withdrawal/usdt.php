<?php

use Fuel\Core\Validation;

class Forms_Wordpress_Withdrawal_Usdt extends Forms_Main
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

        $validation->add("withdrawal.add.usdt_wallet_type", _("USDT Wallet Type"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'numeric']);

        $validation->add("withdrawal.add.usdt_wallet_address", _("USDT Wallet Address"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes', 'spaces']);
        
        $validation->add("withdrawal.add.email", _("Email Address"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('valid_email');

        return $validation;
    }
}
