<?php

use Fuel\Core\Validation;

/**
 * Class for preparing Forms_Whitelabel_Account_Password form
 */
class Forms_Whitelabel_Account_Password extends Forms_Main
{
    /**
     *
     * @var array
     */
    private $whitelabel;
    
    /**
     *
     * @var View
     */
    private $inside;
    
    /**
     * @param array $whitelabel
     */
    public function __construct($whitelabel)
    {
        $this->whitelabel = $whitelabel;
        
        $this->inside = View::forge("whitelabel/settings/password");
    }
    
    /**
     *
     * @return array
     */
    public function get_whitelabel()
    {
        return $this->whitelabel;
    }
    
    /**
     *
     * @return View
     */
    public function get_inside()
    {
        return $this->inside;
    }
    
    /**
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $val = Validation::forge();
                
        $val->add("input.password", _("New password"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule('min_length', 6);
                
        return $val;
    }
    
    /**
     *
     * @return int
     */
    public function process_form(): int
    {
        if (Input::post("input.password") === null) {
            return self::RESULT_GO_FURTHER;
        }
        
        $whitelabel = $this->get_whitelabel();
        
        $val = $this->validate_form();
        
        if ($val->run()) {
            $newsalt = Lotto_Security::generate_salt();
            $newhash = Lotto_Security::generate_hash(
                $val->validated('input.password'),
                $newsalt
            );
            $dbwhitelabel = Model_Whitelabel::find_by_pk($whitelabel['id']);
            $dbwhitelabel->set([
                'salt' => $newsalt,
                'hash' => $newhash
            ]);
            $dbwhitelabel->save();

            $cache_domain_value = str_replace('.', '-', $whitelabel['domain']);
            Lotto_Helper::clear_cache(["model_whitelabel.bydomain." . $cache_domain_value]);

            Session::set("whitelabel.hash", $newhash);
            Session::set_flash("message", ["success", _("Your password has been changed!")]);
        } else {
            $errors = Lotto_Helper::generate_errors($val->error());
            $this->inside->set("errors", $errors);
            
            return self::RESULT_WITH_ERRORS;
        }
        
        return self::RESULT_OK;
    }
}
