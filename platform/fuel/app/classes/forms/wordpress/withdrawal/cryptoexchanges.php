<?php

use Fuel\Core\Validation;

class Forms_Wordpress_Withdrawal_CryptoExchanges extends Forms_Main
{
    public const OPTIONS = ['Fairox.com USDT&FOMC Wallet(50%/50%)'];
    private $form_name = "";

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

        $validation->add("withdrawal.add.exchange", _("Exchange"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('match_collection', self::OPTIONS);

        $validation->add("withdrawal.add.name", _("Your name"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'singlequotes', 'utf8']);

        $validation->add("withdrawal.add.email", _("Your email on the exchange"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('valid_email');

        return $validation;
    }
}
