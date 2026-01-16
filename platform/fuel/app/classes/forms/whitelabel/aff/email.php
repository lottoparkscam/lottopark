<?php

use Fuel\Core\Validation;

/**
 * @deprecated
 * Description of Forms_Whitelabel_Aff_Email
 */
class Forms_Whitelabel_Aff_Email extends Forms_Main
{
    /**
     *
     * @var array
     */
    private $whitelabel = [];

    /**
     *
     * @var View
     */
    private $inside = null;
    
    /**
     *
     * @var Model_Whitelabel_Aff
     */
    private $user_aff = null;
    
    /**
     *
     * @param array $whitelabel
     */
    public function __construct(array $whitelabel)
    {
        $this->whitelabel = $whitelabel;
    }

    /**
     *
     * @return array
     */
    public function get_whitelabel(): array
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
     *
     * @return \Model_Whitelabel_Aff
     */
    public function get_user_aff(): Model_Whitelabel_Aff
    {
        return $this->user_aff;
    }
    
    /**
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $val = Validation::forge();
                
        $val->add("input.email", _("E-mail"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_email");
            
        return $val;
    }
    
    /**
     * @param string $token
     * @return int
     */
    public function process_form($token): int
    {
        $whitelabel = $this->get_whitelabel();
        
        $users = Model_Whitelabel_Aff::find_by([
            "whitelabel_id" => $whitelabel['id'],
            "token" => $token
        ]);
        
        if (!($users !== null &&
            count($users) > 0 &&
            (int)$users[0]->whitelabel_id === (int)$whitelabel['id'] &&
            (int)$users[0]->is_deleted === 0 &&
            (int)$users[0]->is_accepted === 1 &&
            (((int)$whitelabel['aff_activation_type'] !== Helpers_General::ACTIVATION_TYPE_REQUIRED &&
                (int)$users[0]->is_active === 1) ||
            ((int)$whitelabel['aff_activation_type'] === Helpers_General::ACTIVATION_TYPE_REQUIRED &&
                (int)$users[0]->is_active === 1 &&
                (int)$users[0]->is_confirmed === 1)))
        ) {
            Session::set_flash("message", ["danger", _("Wrong affiliate.")]);
            return self::RESULT_WRONG_AFF;
        }
        $user = $users[0];

        $this->inside = View::forge("whitelabel/affs/email");

        if (null === Input::post("input.email")) {
            $this->inside->set("user", $user);
            return self::RESULT_WITH_ERRORS;
        }

        $val = $this->validate_form();

        if ($val->run()) {
            $result = Model_Whitelabel_Aff::get_count_for_whitelabel(
                $whitelabel,
                $val->validated("input.email")
            );

            if (is_null($result)) {
                Session::set_flash("message", ["danger", _("There is something wrong with DB!")]);
                return self::RESULT_WITH_ERRORS;
            }

            $aff_count = $result[0]['count'];

            if ((int)$aff_count === 0) {
                $user->set([
                    'email' => $val->validated("input.email")
                ]);
                $user->save();

                $this->user_aff = $user;

                Session::set_flash("message", ["success", _("User e-mail address has been saved!")]);
            } else {
                $errors = ['input.email' => _("This e-mail is already taken!")];
                $this->inside->set("errors", $errors);

                $this->inside->set("user", $user);
                return self::RESULT_WITH_ERRORS;
            }
        } else {
            $errors = Lotto_Helper::generate_errors($val->error());
            $this->inside->set("errors", $errors);

            $this->inside->set("user", $user);
            return self::RESULT_WITH_ERRORS;
        }
        
        return self::RESULT_OK;
    }
}
